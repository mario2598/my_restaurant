<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Carbon\Carbon;

class ComandasController extends Controller
{
    use SpaceUtil;

    public function goComandasAdmin()
    {
        $data = [
            'sucursales' => MantenimientoSucursalController::getSucursalesActivas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('comandas.administrar', compact('data'));
    }

    public function goComandaPreparacionGen()
    {
        $data = [
            'idComanda' =>  null,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('comandas.preparacion.comandasGen', compact('data'));
    }

    public function goComandaPreparacionId($idComanda)
    {
        $data = [
            'idComanda' =>  $idComanda,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('comandas.preparacion.comandasGen', compact('data'));
    }


    public function recargarComandas(Request $request)
    {
        try {
            $idComanda = $request->input('idComanda');
            $idSucursal = $this->getUsuarioSucursal();
            
            if ($idSucursal == null || $idSucursal < 1) {
                return $this->responseAjaxServerError("No se pudo obtener la sucursal del usuario", []);
            }

            $payload = [
                'comandas' => self::getComandasPreparacion($idSucursal, $idComanda),
                'metricas_tiempo' => self::getMetricasPreparacionPorLinea($idSucursal, $idComanda),
            ];

            return $this->responseAjaxSuccess("", $payload);
        } catch (\Exception $ex) {
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'ComandasController::recargarComandas',
                'descripcion' => $ex->getMessage() . ' - ' . $ex->getTraceAsString()
            ]);
            return $this->responseAjaxServerError("Error al cargar las comandas: " . $ex->getMessage(), []);
        }
    }

    public static function getBySucursal($idSucursal)
    {
        return DB::table('comanda')->where('sucursal', '=', $idSucursal)->get();
    }


    public function cargarComandasAdmin(Request $request)
    {
        try {
            $idSucursal = $request->input('idSucursal');

            return $this->responseAjaxSuccess("", self::getBySucursal($idSucursal));
        } catch (QueryException $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoUsuariosController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Error cargando los usuarios", []);
        }
    }

    public function guardarComanda(Request $request)
    {
        $comanda = $request->input('comanda');

        try {
            $id = $comanda['id'];
            $idSucursal = $comanda['sucursal'];
            $nombre = $comanda['nombre'];

            // Validar que nombre y sucursal no sean nulos
            if (empty($nombre)) {
                return $this->responseAjaxServerError("El nombre es obligatorio", []);
            }

            if (empty($idSucursal)) {
                return $this->responseAjaxServerError("La sucursal es obligatoria", []);
            }

            $actualizar = !(is_null($id) || $id < 1);

            DB::beginTransaction();

            if ($actualizar) {
                // Obtener la comanda actual
                $comandaEntity = DB::table('comanda')->where('id', '=', $id)->first();

                if ($comandaEntity == null) {
                    return $this->responseAjaxServerError("Ocurrió un error cargando la comanda", []);
                }

                // Validar que el nombre no esté en uso, excepto cuando es el mismo que ya tiene la comanda
                $nombreEnUso = DB::table('comanda')
                    ->where('sucursal', '=', $idSucursal)
                    ->where('nombre', '=', $nombre)
                    ->where('id', '!=', $id) // Excluir la comanda actual
                    ->exists();

                if ($nombreEnUso) {
                    return $this->responseAjaxServerError("El nombre de la comanda ya está en uso en esta sucursal", []);
                }

                // Actualizar la comanda
                DB::table('comanda')
                    ->where('id', '=', $id)
                    ->update([
                        'sucursal' => $idSucursal,
                        'nombre' => $nombre
                    ]);
            } else {
                $nombreEnUso = DB::table('comanda')
                    ->where('sucursal', '=', $idSucursal)
                    ->where('nombre', '=', $nombre)
                    ->exists();

                if ($nombreEnUso) {
                    return $this->responseAjaxServerError("El nombre de la comanda ya está en uso en esta sucursal", []);
                }
                // Insertar nueva comanda
                DB::table('comanda')
                    ->insertGetId([
                        'sucursal' => $idSucursal,
                        'nombre' => $nombre
                    ]);
            }

            DB::commit();
            return $this->responseAjaxSuccess("Comanda guardada correctamente", "");
        } catch (\Exception $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'ComandasController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error cargando los usuarios", []);
        }
    }

    public function eliminarComanda(Request $request)
    {
        $id = $request->input('id');

        try {
            // Validar que el ID no sea nulo o menor que 1
            if (is_null($id) || $id < 1) {
                return $this->responseAjaxServerError("El ID de la comanda es obligatorio", []);
            }

            DB::beginTransaction();

            // Obtener la comanda actual para asegurarse de que existe
            $comandaEntity = DB::table('comanda')->where('id', '=', $id)->first();

            if ($comandaEntity == null) {
                return $this->responseAjaxServerError("No se encontró la comanda", []);
            }

            // Eliminar la comanda
            DB::table('comanda')->where('id', '=', $id)->delete();

            DB::commit();
            return $this->responseAjaxSuccess("Comanda eliminada correctamente", "");
        } catch (\Exception $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'ComandasController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al eliminar la comanda", []);
        }
    }

    /**
     * Promedios y SLA por línea (detalle_orden_comanda: fecha_ingreso → fecha_fin).
     * Solo órdenes con fecha_inicio en el día actual (zona horaria de la app).
     * Sin montos: solo minutos, porcentajes y conteos.
     *
     * @param  int|null  $idComanda  Si viene definido, solo líneas de esa comanda (estación); si no, todas de la sucursal.
     */
    public static function getMetricasPreparacionPorLinea($idSucursal, $idComanda = null, $slaMinutos = 15)
    {
        if ($idSucursal === null || (int) $idSucursal < 1) {
            return [
                'sla_minutos' => (int) $slaMinutos,
                'es_solo_hoy' => true,
                'fecha_dia' => Carbon::now()->toDateString(),
                'es_vista_general' => true,
                'comanda_filtro_id' => null,
                'comanda_filtro_nombre' => null,
                'total_lineas_terminadas' => 0,
                'promedio_min_por_linea' => null,
                'lineas_dentro_sla' => 0,
                'pct_dentro_sla' => null,
                'max_minutos_una_linea' => null,
                'peores_lineas_detalle' => [],
            ];
        }

        $desde = Carbon::now()->startOfDay();
        $hastaFin = Carbon::now()->endOfDay();

        $idComandaFiltro = null;
        if ($idComanda !== null && $idComanda !== '' && (int) $idComanda > 0) {
            $idComandaFiltro = (int) $idComanda;
        }

        $nombreComandaFiltro = null;
        if ($idComandaFiltro !== null) {
            $nombreComandaFiltro = DB::table('comanda')
                ->where('id', '=', $idComandaFiltro)
                ->where('sucursal', '=', (int) $idSucursal)
                ->value('nombre');
        }

        $sla = (int) $slaMinutos;

        $q = DB::table('detalle_orden_comanda')
            ->join('orden_comanda', 'orden_comanda.id', '=', 'detalle_orden_comanda.orden_comanda')
            ->join('orden', 'orden.id', '=', 'orden_comanda.orden')
            ->leftJoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->where('orden.sucursal', '=', (int) $idSucursal)
            ->whereNotNull('detalle_orden_comanda.fecha_fin')
            ->whereNotNull('detalle_orden_comanda.fecha_ingreso')
            ->where('sis_estado.cod_general', '!=', 'ORD_ANULADA')
            ->where('orden.fecha_inicio', '>=', $desde)
            ->where('orden.fecha_inicio', '<=', $hastaFin);

        if ($idComandaFiltro !== null) {
            $q->where('detalle_orden_comanda.comanda', '=', $idComandaFiltro);
        }

        $row = $q->select(
            DB::raw('COUNT(detalle_orden_comanda.id) as total_lineas'),
            DB::raw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, detalle_orden_comanda.fecha_ingreso, detalle_orden_comanda.fecha_fin)), 1) as promedio_min'),
            DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, detalle_orden_comanda.fecha_ingreso, detalle_orden_comanda.fecha_fin) <= ' . $sla . ' THEN 1 ELSE 0 END) as dentro_sla'),
            DB::raw('ROUND(MAX(TIMESTAMPDIFF(MINUTE, detalle_orden_comanda.fecha_ingreso, detalle_orden_comanda.fecha_fin)), 0) as max_min')
        )->first();

        $total = (int) ($row->total_lineas ?? 0);
        $dentro = (int) ($row->dentro_sla ?? 0);
        $pct = $total > 0 ? round(100 * $dentro / $total, 1) : null;
        $prom = $row->promedio_min !== null ? round((float) $row->promedio_min, 1) : null;
        $maxMin = $row->max_min !== null ? (int) $row->max_min : null;

        $qDetalle = DB::table('detalle_orden_comanda')
            ->join('orden_comanda', 'orden_comanda.id', '=', 'detalle_orden_comanda.orden_comanda')
            ->join('orden', 'orden.id', '=', 'orden_comanda.orden')
            ->join('detalle_orden', 'detalle_orden.id', '=', 'detalle_orden_comanda.detalle_orden')
            ->leftJoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->leftJoin('comanda as comanda_linea', 'comanda_linea.id', '=', 'detalle_orden_comanda.comanda')
            ->where('orden.sucursal', '=', (int) $idSucursal)
            ->whereNotNull('detalle_orden_comanda.fecha_fin')
            ->whereNotNull('detalle_orden_comanda.fecha_ingreso')
            ->where('sis_estado.cod_general', '!=', 'ORD_ANULADA')
            ->where('orden.fecha_inicio', '>=', $desde)
            ->where('orden.fecha_inicio', '<=', $hastaFin);

        if ($idComandaFiltro !== null) {
            $qDetalle->where('detalle_orden_comanda.comanda', '=', $idComandaFiltro);
        }

        $filasPeores = $qDetalle
            ->select(
                'orden.numero_orden',
                'orden_comanda.num_comanda',
                'detalle_orden.nombre_producto',
                'detalle_orden_comanda.cantidad',
                'detalle_orden.observacion',
                'detalle_orden_comanda.fecha_ingreso',
                'detalle_orden_comanda.fecha_fin',
                DB::raw('TIMESTAMPDIFF(MINUTE, detalle_orden_comanda.fecha_ingreso, detalle_orden_comanda.fecha_fin) as minutos_prep'),
                DB::raw('COALESCE(comanda_linea.nombre, \'-\') as estacion_nombre')
            )
            ->orderByRaw('TIMESTAMPDIFF(MINUTE, detalle_orden_comanda.fecha_ingreso, detalle_orden_comanda.fecha_fin) DESC')
            ->limit(25)
            ->get();

        $peoresLineasDetalle = [];
        foreach ($filasPeores as $r) {
            $min = (int) ($r->minutos_prep ?? 0);
            $peoresLineasDetalle[] = [
                'numero_orden' => $r->numero_orden,
                'num_comanda' => $r->num_comanda,
                'producto' => $r->nombre_producto,
                'cantidad' => (int) ($r->cantidad ?? 0),
                'observacion' => $r->observacion,
                'minutos' => $min,
                'fecha_ingreso' => $r->fecha_ingreso,
                'fecha_fin' => $r->fecha_fin,
                'estacion' => $r->estacion_nombre,
                'excede_sla' => $min > $sla,
            ];
        }

        return [
            'sla_minutos' => $sla,
            'es_solo_hoy' => true,
            'fecha_dia' => Carbon::now()->toDateString(),
            'es_vista_general' => $idComandaFiltro === null,
            'comanda_filtro_id' => $idComandaFiltro,
            'comanda_filtro_nombre' => $nombreComandaFiltro,
            'total_lineas_terminadas' => $total,
            'promedio_min_por_linea' => $prom,
            'lineas_dentro_sla' => $dentro,
            'pct_dentro_sla' => $pct,
            'max_minutos_una_linea' => $maxMin,
            'peores_lineas_detalle' => $peoresLineasDetalle,
        ];
    }

    public static function getComandasPreparacion($sucursal, $idComanda)
    {
        if ($sucursal < 1 || $sucursal == null) {
            return [];
        }

        $ordenes = DB::table('orden')
            ->join('orden_comanda', 'orden_comanda.orden', '=', 'orden.id')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->leftjoin('mesa', 'mesa.id', '=', 'orden.mesa')
            ->select('orden.*', 'orden_comanda.id as id_orden_comanda', 'sis_estado.nombre as descEstado', 'mesa.numero_mesa as numero_mesa', 'orden_comanda.fecha_inicio as fecha_inicio_cmd')
            ->whereIn('orden.estado', array(SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION')))
            ->where('orden.sucursal', '=', $sucursal)
            ->orderBy('orden_comanda.id', 'ASC')->get();

        $result = [];
        foreach ($ordenes as $o) {
            $o->fecha_inicio = $o->fecha_inicio_cmd;
            $phpdate = strtotime($o->fecha_inicio_cmd);
            
            // Usar Carbon para formatear la fecha en español (compatible con PHP 8.1+)
            try {
                $carbonDate = Carbon::parse($o->fecha_inicio_cmd);
                $carbonDate->setLocale('es');
                $fechaAux = ucfirst($carbonDate->isoFormat('dddd, D [de] MMMM'));
                $fechaAux .= ' - ' . date("g:i a", $phpdate);
            } catch (\Exception $e) {
                // Fallback si Carbon falla
                $fechaAux = date("d-m-Y", $phpdate) . ' - ' . date("g:i a", $phpdate);
            }
            
            $o->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
            $o->fecha_inicio_texto = $fechaAux;

            $detalles = DB::table('detalle_orden')
                ->join('detalle_orden_comanda', 'detalle_orden_comanda.detalle_orden', '=', 'detalle_orden.id')
                ->select('detalle_orden.*',
                 'detalle_orden_comanda.id as id_detalle_orden_comanda',
                 'detalle_orden_comanda.cantidad as cantidad_comanda', 
                 'detalle_orden_comanda.fecha_fin as fecha_fin_comanda')
                ->where('detalle_orden_comanda.orden_comanda', '=', $o->id_orden_comanda)
                ->where('detalle_orden_comanda.preparado', '=', 0)
                ->get();

            $o->detalles = [];

            foreach ($detalles as $d) {
                $idComandaAux = null;
                if ($d->tipo_producto == 'R') {
                    $productoMenu = ProductosMenuController::getIdByCodigo($d->codigo_producto);
                    if ($productoMenu != null) {
                        $d->idProd = $productoMenu->id;
                        $idComandaAux = ProductosMenuController::getIdComandaByCodigoSucursal($d->codigo_producto, $sucursal);
                    } else {
                        // Si no se encuentra el producto, saltar este detalle
                        continue;
                    }
                } else if ($d->tipo_producto == 'E') {
                    $productoExterno = ProductosExternosController::getIdByCodigo($d->codigo_producto);
                    if ($productoExterno != null) {
                        $d->idProd = $productoExterno->id;
                        $idComandaAux = ProductosExternosController::getIdComandaByCodigoSucursal($d->codigo_producto, $sucursal);
                    } else {
                        // Si no se encuentra el producto, saltar este detalle
                        continue;
                    }
                }

                // Filtrar los productos de tipo `PROMO` con sus productos internos
                if ($d->tipo_producto == 'PROMO') {
                    $productosPromo = DB::table('det_grupo_promocion')
                        ->leftjoin('producto_menu', 'producto_menu.id', '=', 'det_grupo_promocion.producto')
                        ->select('producto_menu.*', 'det_grupo_promocion.cantidad as cantProd')
                        ->where('det_grupo_promocion.tipo', '=', "R")
                        ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)
                        ->get();

                    foreach ($productosPromo as $p) {
                        if ($p->codigo == null) {
                            continue;
                        }
                        $idComandaAux  = ProductosMenuController::getIdComandaByCodigoSucursal($p->codigo, $sucursal);
                        $nuevoDetalle = clone $d;
                        $nuevoDetalle->codigo_producto = $p->codigo;
                        $nuevoDetalle->nombre_producto = $p->nombre;
                        $nuevoDetalle->cantidad = $p->cantProd;
                        $nuevoDetalle->idProd = $p->id;
                        $nuevoDetalle->extras = [];
                        $nuevoDetalle->tipo_producto = 'R';
                        $nuevoDetalle->comanda = $idComandaAux;
                        if ($idComanda == null || $nuevoDetalle->comanda == $idComanda) {
                            $o->detalles[] = $nuevoDetalle;
                        }
                    }

                    $productosPromoE = DB::table('det_grupo_promocion')
                        ->leftjoin('producto_externo', 'producto_externo.id', '=', 'det_grupo_promocion.producto')
                        ->select('producto_externo.*', 'det_grupo_promocion.cantidad as cantProd')
                        ->where('det_grupo_promocion.tipo', '=', "E")
                        ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)
                        ->get();

                    foreach ($productosPromoE as $p) {
                        if ($p->codigo_barra == null) {
                            continue;
                        }
                        $idComandaAux  = ProductosExternosController::getIdComandaByCodigoSucursal($p->codigo_barra, $sucursal);
                        $nuevoDetalle = clone $d;
                        $nuevoDetalle->codigo_producto = $p->codigo_barra;
                        $nuevoDetalle->nombre_producto = $p->nombre;
                        $nuevoDetalle->cantidad = $p->cantProd;
                        $nuevoDetalle->tipo_producto = 'E';
                        $nuevoDetalle->extras = [];
                        $nuevoDetalle->idProd = $p->id;
                        $nuevoDetalle->comanda = $idComandaAux;
                        if ($idComanda == null || $nuevoDetalle->comanda == $idComanda) {
                            $o->detalles[] = $nuevoDetalle;
                        }
                    }
                } else {
                    // Solo asignar comanda si $idComandaAux fue definido
                    if ($idComandaAux !== null) {
                        $d->comanda = $idComandaAux;
                        if ($idComanda == null || $d->comanda == $idComanda) {
                            $o->detalles[] = $d;
                        }
                    }
                }
            }

            // Asignación de composición y extras
            foreach ($o->detalles as $d) {
                if ($d->tipo_producto == 'R') {
                    $d->receta = DB::table('producto_menu')
                        ->select('producto_menu.receta')
                        ->where('producto_menu.codigo', '=', $d->codigo_producto)
                        ->get()->first()->receta ?? "";

                    $d->materia_prima = DB::table('producto_menu')
                        ->leftjoin('mt_x_producto', 'mt_x_producto.producto', '=', 'producto_menu.id')
                        ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto.materia_prima')
                        ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'mt_x_producto.cantidad', 'producto_menu.nombre as prodNom')
                        ->where('producto_menu.codigo', '=', $d->codigo_producto)
                        ->get() ?? [];
                } else if ($d->tipo_producto == 'E') {
                    $d->receta = "";
                    $d->materia_prima = DB::table('producto_externo')
                        ->leftjoin('mt_x_producto_ext', 'mt_x_producto_ext.producto', '=', 'producto_externo.id')
                        ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto_ext.materia_prima')
                        ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'mt_x_producto_ext.cantidad', 'producto_externo.nombre as prodNom')
                        ->where('producto_externo.codigo_barra', '=', $d->codigo_producto)
                        ->get() ?? [];
                }

                $d->extras = DB::table('extra_detalle_orden')
                    ->select('extra_detalle_orden.*')
                    ->where('extra_detalle_orden.orden', '=', $o->id)
                    ->where('extra_detalle_orden.detalle', '=', $d->id)
                    ->where('extra_detalle_orden.id_producto', '=', $d->idProd)
                    ->get() ?? [];
                $d->tieneExtras = count($d->extras) > 0;

                $composicionTxt = "";
                foreach ($d->materia_prima as $i => $mp) {
                    $composicionTxt .= ($i > 0 ? "\n" : "") . "[ " . $mp->nombre . ", " . $mp->cantidad . " " . $mp->unidad_medida . " ] ";
                }

                if ($d->tipo_producto == 'R') {
                    $mpExtras = DB::table('extra_detalle_orden')
                        ->leftjoin('extra_producto_menu', 'extra_producto_menu.id', '=', 'extra_detalle_orden.extra')
                        ->leftjoin('materia_prima', 'materia_prima.id', '=', 'extra_producto_menu.materia_prima')
                        ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'extra_producto_menu.cant_mp')
                        ->where('extra_detalle_orden.orden', '=', $o->id)
                        ->where('extra_detalle_orden.detalle', '=', $d->id)
                        ->get() ?? [];

                    if (count($mpExtras) > 0) {
                        $composicionTxt .= " \n ---------- Extras ---------- \n";
                        foreach ($mpExtras as $ex) {
                            if ($ex->nombre != null && $ex->cant_mp != null) {
                                $composicionTxt .= "[ " . $ex->nombre . ", " . $ex->cant_mp . " " . $ex->unidad_medida . " ]\n ";
                            }
                        }
                    }
                }
                $d->composicion = $composicionTxt;
            }

            if (!empty($o->detalles)) {
                $result[] = $o;
            }
        }

        return $result;
    }

    public function terminarPreparacionComanda(Request $request)
    {

        $id_orden_comanda = $request->input('id_orden_comanda');
        $id_comanda = $request->input('id_comanda');

        if ($id_orden_comanda < 1 || $this->isNull($id_orden_comanda)) {
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden_comanda = DB::table('orden_comanda')->select('orden_comanda.*')->where('orden_comanda.id', '=', $id_orden_comanda)->get()->first();

        if ($orden_comanda == null) {
            return $this->responseAjaxServerError('No existe la comanda.', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $orden_comanda->orden)->get()->first();

        if ($orden == null) {
            $this->setError('Terminar Preparación Orden', 'No existe la orden.');
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($orden->estado != SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION')) {
            return $this->responseAjaxServerError('La orden ya fue procesada', []);
        }

        $estadoAnterior = $orden->estado;
        $fechaActual = date("Y-m-d H:i:s");
        try {
            $servEstOrd = new EstOrdenController();
            $fac = new EntregasOrdenController();
            DB::beginTransaction();
            $idEstEntrega = SisEstadoController::getIdEstadoByCodGeneral('ORD_PARA_ENTREGA');
            if ($id_comanda  != null) {
                $detalles = DB::table('detalle_orden')->select('detalle_orden.*',
                 'detalle_orden_comanda.cantidad as cantidad_prep', 'detalle_orden_comanda.fecha_fin as fecha_fin_comanda')
                    ->join('detalle_orden_comanda', 'detalle_orden_comanda.detalle_orden', '=', 'detalle_orden.id')
                    ->where('detalle_orden_comanda.orden_comanda', '=', $id_orden_comanda)
                    ->where('detalle_orden.comanda', '=', $id_comanda)->get();
            } else {
                $detalles = DB::table('detalle_orden')->select('detalle_orden.*',
                 'detalle_orden_comanda.cantidad as cantidad_prep', 'detalle_orden_comanda.fecha_fin as fecha_fin_comanda')
                    ->join('detalle_orden_comanda', 'detalle_orden_comanda.detalle_orden', '=', 'detalle_orden.id')
                    ->where('detalle_orden_comanda.orden_comanda', '=', $id_orden_comanda)->get();
            }

            // Determinar si la orden está completamente preparada


            foreach ($detalles as $detalle) {
                // Actualizar la cantidad_preparada en detalle_orden
                DB::table('detalle_orden')
                    ->where('id', '=', $detalle->id)
                    ->update(['cantidad_preparada' => $detalle->cantidad_preparada + $detalle->cantidad_prep]);

                if ($detalle->fecha_fin_comanda == null) {
                    DB::table('detalle_orden_comanda')
                    ->where('detalle_orden', '=', $detalle->id)
                    ->update(['fecha_fin' => $fechaActual]); 
                }
                // Actualizar el detalle_orden_comanda si es necesario
                DB::table('detalle_orden_comanda')
                    ->where('detalle_orden', '=', $detalle->id)
                    ->update([ 'preparado' => 1]); // o el estado correspondiente

                $facturacion = new FacturacionController();
                $res =  $facturacion->restarProductoMenuMatPrima($detalle->id, $detalle->cantidad_prep);
                if (!$res['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            }


            $detalleSinPreparar = DB::table('detalle_orden')
                ->join('detalle_orden_comanda', 'detalle_orden_comanda.detalle_orden', '=', 'detalle_orden.id')
                ->select('detalle_orden.id')
                ->where('detalle_orden.orden', '=', $orden->id)
                ->where('detalle_orden_comanda.preparado', '=', 0)
                ->exists(); // Retorna `true` si al menos uno tiene `preparado = 0`
            $ordenCompleta = false;

            if ($detalleSinPreparar) {
                $ordenCompleta = false;
            } else {
                $ordenCompleta = true;
            }

            if ($ordenCompleta) {
                DB::table('orden')
                    ->where('id', '=', $orden->id)
                    ->update([
                        'estado' => $idEstEntrega,
                        'fecha_preparado' => $fechaActual
                    ]);

                if ($orden->ind_requiere_envio == 1) {
                    $respuesta = $fac->actualizarEntregaOrden($orden->id, SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_PEND_SALIDA_LOCAL'));

                    if (!$respuesta['estado']) {
                        DB::rollBack();
                        return $this->responseAjaxServerError($respuesta['mensaje'], []);
                    }
                }

                $resCargaEst = $servEstOrd->creaEstOrden($orden->id, $idEstEntrega, $estadoAnterior);

                if (!$resCargaEst['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($resCargaEst['mensaje'], []);
                }
            }

            DB::commit();

            return $this->setAjaxResponse(200, "", [], true);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

     /**
     * Marca una línea (detalle_orden_comanda) como preparada individualmente.
     * Solo se puede ejecutar una vez por línea (cuando fecha_fin es null).
     */
    public function marcarLineaPreparada(Request $request)
    {
        $id_detalle_orden_comanda = $request->input('id_detalle_orden_comanda');

        if ($id_detalle_orden_comanda < 1 || $this->isNull($id_detalle_orden_comanda)) {
            return $this->responseAjaxServerError('Id de la línea incorrecto.', []);
        }

        $doc = DB::table('detalle_orden_comanda')
            ->where('id', '=', $id_detalle_orden_comanda)
            ->first();

        if ($doc == null) {
            return $this->responseAjaxServerError('No existe la línea.', []);
        }

        if ($doc->fecha_fin !== null) {
            return $this->responseAjaxServerError('Esta línea ya fue marcada como preparada.', []);
        }

        $orden_comanda = DB::table('orden_comanda')->where('id', '=', $doc->orden_comanda)->first();
        if ($orden_comanda == null) {
            return $this->responseAjaxServerError('No existe la comanda.', []);
        }

        $orden = DB::table('orden')->where('id', '=', $orden_comanda->orden)->first();
        if ($orden == null) {
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($orden->estado != SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION')) {
            return $this->responseAjaxServerError('La orden no está en preparación.', []);
        }

        $fechaActual = date("Y-m-d H:i:s");

        try {
            DB::beginTransaction();

            $updated = DB::table('detalle_orden_comanda')
                ->where('id', '=', $id_detalle_orden_comanda)
                ->update(['fecha_fin' => $fechaActual]);

            $detalleSinPreparar = DB::table('detalle_orden')
                ->join('detalle_orden_comanda', 'detalle_orden_comanda.detalle_orden', '=', 'detalle_orden.id')
                ->where('detalle_orden.orden', '=', $orden->id)
                ->where('detalle_orden_comanda.fecha_fin', '=', null)
                ->exists();

            DB::commit();

            $datos = [];
            if (!$detalleSinPreparar) {
                $datos['orden_completa'] = true;
                $datos['id_orden_comanda'] = $orden_comanda->id;
            }
            return $this->setAjaxResponse(200, "", $datos, true);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salió mal.', []);
        }
    }
}
