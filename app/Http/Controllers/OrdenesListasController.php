<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use App\Traits\SpaceUtil;

class OrdenesListasController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goOrdenesEntrega()
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'ordenes_listas' => OrdenesListasController::getOrdenesListasEntregar($this->getUsuarioSucursal()),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('facturacion.ordenesEntrega', compact('data'));
    }

    public function goOrdenesPreparacion()
    {
        if (!$this->validarSesion("ordList_prep")) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'ordenes' => OrdenesListasController::getOrdenesPreparacion($this->getUsuarioSucursal()),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('facturacion.ordenesPreparacion', compact('data'));
    }

    public static function getOrdenesListasEntregar($sucursal)
    {
        if ($sucursal < 1 || $sucursal == null) {
            return [];
        }

        $ordenes = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->leftjoin('mesa', 'mesa.id', '=', 'orden.mesa')
            ->select('orden.*', 'sis_estado.nombre as descEstado', 'mesa.numero_mesa as mesaDsc')
            ->whereIn('orden.estado', array(SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION'), SisEstadoController::getIdEstadoByCodGeneral('ORD_PARA_ENTREGA')))
            ->where('orden.sucursal', '=', $sucursal)
            ->where('orden.cierre_caja', '=', CajaController::getIdCaja(session('usuario')['id'], $sucursal))

            ->orderBy('orden.fecha_inicio', 'ASC')->get();

        foreach ($ordenes as $o) {
            $phpdate = strtotime($o->fecha_inicio);
            $date = date("d-m-Y", strtotime($o->fecha_inicio));

            $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
            $fechaAux .= ' - ' . date("g:i a", $phpdate);
            $o->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
            $o->fecha_inicio_texto =  $fechaAux;
            $o->detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $o->id)
                ->get();

            foreach ($o->detalles as $d) {

                $d->extras = DB::table('extra_detalle_orden')->select('extra_detalle_orden.*')
                    ->where('extra_detalle_orden.orden', '=', $o->id)
                    ->where('extra_detalle_orden.detalle', '=', $d->id)

                    ->get() ?? [];
                $d->tieneExtras = count($d->extras) > 0;
            }
            $o->idOrdenEnc = encrypt($o->id);
        }

        return $ordenes;
    }

    public static function getOrdenesPreparacion($sucursal)
    {
        if ($sucursal < 1 || $sucursal == null) {
            return [];
        }

        $ordenes = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.nombre as descEstado')
            ->whereIn('orden.estado', array(SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION')))
            ->where('orden.sucursal', '=', $sucursal)
            ->orderBy('orden.fecha_inicio', 'ASC')->get();

        foreach ($ordenes as $o) {
            $phpdate = strtotime($o->fecha_inicio);
            $date = date("d-m-Y", strtotime($o->fecha_inicio));

            $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
            $fechaAux .= ' - ' . date("g:i a", $phpdate);
            $o->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
            $o->fecha_inicio_texto =  $fechaAux;
            $o->detalles = DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $o->id)
                ->get();

            foreach ($o->detalles as $d) {
                if ($d->tipo_producto == 'R') {
                    $d->receta =  DB::table('producto_menu')
                        ->select('producto_menu.receta')
                        ->where('producto_menu.codigo', '=', $d->codigo_producto)
                        ->get()->first()->receta ?? "";

                    $d->materia_prima = DB::table('producto_menu')
                        ->leftjoin('mt_x_producto', 'mt_x_producto.producto', '=', 'producto_menu.id')
                        ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto.materia_prima')
                        ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'mt_x_producto.cantidad', 'producto_menu.nombre as prodNom')
                        ->where('producto_menu.codigo', '=', $d->codigo_producto)
                        ->get() ?? [];

                    $d->productos_promo = [];
                } else if ($d->tipo_producto == 'PROMO') {
                    $recpAux = "";
                    $d->productos_promo = DB::table('det_grupo_promocion')
                        ->leftjoin('producto_menu', 'producto_menu.id', '=', 'det_grupo_promocion.producto')
                        ->select('producto_menu.*')
                        ->where('det_grupo_promocion.tipo', '=', "R")
                        ->get();

                    $d->productosE_promo = DB::table('det_grupo_promocion')
                        ->leftjoin('producto_externo', 'producto_externo.id', '=', 'det_grupo_promocion.producto')
                        ->select('producto_externo.*')
                        ->where('det_grupo_promocion.tipo', '=', "E")
                        ->get();
                    $d->materia_prima = [];

                    foreach ($d->productos_promo as $i => $p) {
                        $recpAux = $recpAux . ($i > 0 ? "\n" : "") . "[ Receta " . $p->nombre . " ] ";
                        $recpAux = $recpAux . "\n" . ($p->receta ??  " ") . "\n";
                        $p->materia_prima = DB::table('producto_menu')
                            ->leftjoin('mt_x_producto', 'mt_x_producto.producto', '=', 'producto_menu.id')
                            ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto.materia_prima')
                            ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'mt_x_producto.cantidad',)
                            ->where('producto_menu.id', '=', $p->id)
                            ->get() ?? [];
                    }

                    foreach ($d->productosE_promo as $p) {
                        $p->materia_prima = DB::table('producto_externo')
                            ->leftjoin('mt_x_producto_ext', 'mt_x_producto_ext.producto', '=', 'producto_externo.id')
                            ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto_ext.materia_prima')
                            ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'mt_x_producto_ext.cantidad',)
                            ->where('producto_externo.codigo_barra', '=', $p->id)
                            ->get() ?? [];
                    }
                    $d->receta =  $recpAux;
                } else if ($d->tipo_producto == 'E') {
                    $d->receta = "";

                    $d->materia_prima = DB::table('producto_externo')
                        ->leftjoin('mt_x_producto_ext', 'mt_x_producto_ext.producto', '=', 'producto_externo.id')
                        ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto_ext.materia_prima')
                        ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'mt_x_producto_ext.cantidad', 'producto_externo.nombre as prodNom')
                        ->where('producto_externo.codigo_barra', '=', $d->codigo_producto)
                        ->get() ?? [];
                    $d->productos_promo = [];
                }

                $d->extras = DB::table('extra_detalle_orden')->select('extra_detalle_orden.*')
                    ->where('extra_detalle_orden.orden', '=', $o->id)
                    ->where('extra_detalle_orden.detalle', '=', $d->id)
                    ->get() ?? [];
                $d->tieneExtras = count($d->extras) > 0;
                $composicionTxt = "";

                if ($d->tipo_producto == 'R' || $d->tipo_producto == 'E') {

                    foreach ($d->materia_prima as $i => $mp) {
                        $composicionTxt = $composicionTxt .  ($i > 0 ? "\n" : "") . "[ " . $mp->nombre . ", " . $mp->cantidad . " " . $mp->unidad_medida . " ] ";
                    }

                    if ($d->tipo_producto == 'R') {

                        $mpExtras = DB::table('extra_detalle_orden')->select('extra_detalle_orden.*')
                            ->leftjoin('extra_producto_menu', 'extra_producto_menu.id', '=', 'extra_detalle_orden.extra')
                            ->leftjoin('materia_prima', 'materia_prima.id', '=', 'extra_producto_menu.materia_prima')
                            ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'extra_producto_menu.cant_mp')
                            ->where('extra_detalle_orden.orden', '=', $o->id)
                            ->where('extra_detalle_orden.detalle', '=', $d->id)
                            ->get() ?? [];

                        if (count($mpExtras) > 0) {
                            $composicionTxt = $composicionTxt . " \n ---------- Extras ---------- \n";
                            foreach ($mpExtras as $i => $ex) {
                                if ($ex->nombre != null && $ex->cant_mp != null) {
                                    $composicionTxt = $composicionTxt .  "[ " . $ex->nombre . ", " . $ex->cant_mp . " " . $ex->unidad_medida . " ]\n ";
                                }
                            }
                        }
                    }
                } else if ($d->tipo_producto == 'PROMO') {
                    foreach ($d->productosE_promo as $i =>  $p) {
                        $composicionTxt = $composicionTxt .  ($i > 0 ? "\n" : "") . "--- " . $p->nombre . " --- \n";
                        foreach ($p->materia_prima as $i => $mp) {
                            $composicionTxt = $composicionTxt  . $mp->nombre . ", " . $mp->cantidad . " " . $mp->unidad_medida . " ";
                        }
                    }

                    foreach ($d->productos_promo as  $p) {
                        $composicionTxt = $composicionTxt .  "\n--- " . $p->nombre . " --- \n";
                        foreach ($p->materia_prima as $i => $mp) {
                            $composicionTxt = $composicionTxt  . "[ " . $mp->nombre . ", " . $mp->cantidad . " " . $mp->unidad_medida . " ]\n ";
                        }
                        
                        $mpExtras = DB::table('extra_detalle_orden')
                            ->leftjoin('extra_producto_menu', 'extra_producto_menu.id', '=', 'extra_detalle_orden.extra')
                            ->leftjoin('materia_prima', 'materia_prima.id', '=', 'extra_producto_menu.materia_prima')
                            ->select('materia_prima.nombre', 'materia_prima.unidad_medida', 'extra_producto_menu.cant_mp')
                            ->where('extra_detalle_orden.orden', '=', $o->id)
                            ->where('extra_detalle_orden.id_producto', '=', $p->id)
                            ->where('extra_detalle_orden.detalle', '=', $d->id)
                            ->get() ?? [];

                        if (count($mpExtras) > 0) {
                            $composicionTxt = $composicionTxt . " --- Extras de ".$p->nombre." --- \n";
                            foreach ($mpExtras as $i => $ex) {
                                if ($ex->nombre != null && $ex->cant_mp != null) {
                                    $composicionTxt = $composicionTxt .  "[ " . $ex->nombre . ", " . $ex->cant_mp . " " . $ex->unidad_medida . " ]\n ";
                                }
                            }
                        }
                    }
                }
                $d->composicion = $composicionTxt;
            }
            $o->idOrdenEnc = encrypt($o->id);
        }

        return $ordenes;
    }




    public function entregarOrdenComida(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            $this->setError('Entregar Orden', 'Id de la orden incorrecto...');
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            $this->setError('Entregar Orden', 'No existe la orden.');
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($orden->estado != 'PT' && $orden->estado != 'PTF') {
            $this->setError('Entregar Orden', 'La orden ya fue procesada');
            return $this->responseAjaxServerError('La orden ya fue procesada', []);
        }

        try {
            DB::beginTransaction();
            if ($orden->estado == 'PTF') {
                $estado = 'FC';
            } else {
                $estado = 'LF';
            }
            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'estado' => $estado, 'fecha_preparado' => date("Y-m-d H:i:s")
                ]);

            DB::commit();

            return $this->setAjaxResponse(200, "", [], true);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Entregar Orden', 'Algo salio mal...');
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    public function recargarOrdenesListasEntregar(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return 'Error de seguridad!';
        }

        $data = [
            'ordenes_listas' => OrdenesListasController::getOrdenesListasEntregar($this->getRestauranteUsuario()),
        ];
        return view('cocina.ordenesListas.layout.contenedor_comandas', compact('data'));
    }


    public function terminarPreparacionOrden(Request $request)
    {
        if (!$this->validarSesion("ordList_prep")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            $this->setError('Terminar Preparación Orden', 'Id de la orden incorrecto...');
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            $this->setError('Terminar Preparación Orden', 'No existe la orden.');
            return $this->responseAjaxServerError('No existe la orden.', []);
        }


        if ($orden->estado != SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION')) {
            $this->setError('Terminar Preparación Orden', 'La orden ya fue procesada');
            return $this->responseAjaxServerError('La orden ya fue procesada', []);
        }

        $estadoAnterior = $orden->estado;
        $fechaActual = date("Y-m-d H:i:s");
        try {
            $servEstOrd = new EstOrdenController();
            $fac = new EntregasOrdenController();
            DB::beginTransaction();
            $idEstEntrega = SisEstadoController::getIdEstadoByCodGeneral('ORD_PARA_ENTREGA');
            $detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('orden', '=', $id_orden)->get();
            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'estado' => $idEstEntrega, 'fecha_preparado' => $fechaActual, 'cocina_terminado' => 'S'
                ]);
            if ($orden->ind_requiere_envio == 1) {
                $respuesta = $fac->actualizarEntregaOrden($id_orden, SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_PEND_SALIDA_LOCAL'));

                if (!$respuesta['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($respuesta['mensaje'], []);
                }
            }

            $resCargaEst = $servEstOrd->creaEstOrden($id_orden, $idEstEntrega, $estadoAnterior);

            if (!$resCargaEst['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($resCargaEst['mensaje'], []);
            }

            DB::commit();

            return $this->setAjaxResponse(200, "", [], true);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Terminar Preparación Orden', 'Algo salio mal...');
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }


    public function recargarOrdenesEntrega(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return 'Error de seguridad!';
        }

        $data = [

            'ordenes' => OrdenesListasController::getOrdenesListasEntregar($this->getUsuarioSucursal())
        ];
        return view('facturacion.layout.entregas', compact('data'));
    }

    public function recargarOrdenesPreparacion(Request $request)
    {
        if (!$this->validarSesion("ordList_prep")) {
            return 'Error de seguridad!';
        }

        $data = [

            'ordenes' => OrdenesListasController::getOrdenesPreparacion($this->getUsuarioSucursal())
        ];
        return view('facturacion.layout.preparacion', compact('data'));
    }

    public function terminarEntregaOrden(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            $this->setError('Terminar Preparación Orden', 'Id de la orden incorrecto...');
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            $this->setError('Terminar Preparación Orden', 'No existe la orden.');
            return $this->responseAjaxServerError('No existe la orden.', []);
        }


        if ($orden->estado != SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION') && $orden->estado != SisEstadoController::getIdEstadoByCodGeneral('ORD_PARA_ENTREGA')) {
            $this->setError('Terminar Preparación Orden', 'La orden ya fue procesada');
            return $this->responseAjaxServerError('La orden ya fue procesada', []);
        }
        $estadoAnterior = $orden->estado;
        try {
            $fac = new EntregasOrdenController();
            $servEstOrd = new EstOrdenController();
            DB::beginTransaction();
            $idEstEntrega = SisEstadoController::getIdEstadoByCodGeneral('ORD_ENTREGADA');
            $detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('orden', '=', $id_orden)->get();
            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'estado' => $idEstEntrega, 'fecha_entregado' => date("Y-m-d H:i:s"), 'cocina_terminado' => 'S'
                ]);
            if ($orden->ind_requiere_envio == 1) {
                $respuesta = $fac->actualizarEntregaOrden($id_orden, SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_EN_RUTA'));

                if (!$respuesta['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($respuesta['mensaje'], []);
                }
            }
            $resCargaEst = $servEstOrd->creaEstOrden($id_orden, $idEstEntrega, $estadoAnterior);

            if (!$resCargaEst['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($resCargaEst['mensaje'], []);
            }

            DB::commit();


            return $this->setAjaxResponse(200, "", [], true);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Terminar Preparación Orden', 'Algo salio mal...');
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }
}
