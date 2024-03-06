<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;

class EntregasOrdenController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function crearEntregaOrden($precio, $dsc_lugar, $dsc_contacto, $orden)
    {
        try {
            $idEst = SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_PREPARACION_PEND');
            $ext_id = DB::table('entrega_orden')->insertGetId([
                'id' => null, 'orden' => $orden, 'precio' => $precio,
                'descripcion_lugar' => $dsc_lugar, 'contacto' => $dsc_contacto,
                'estado' => $idEst, 'encargado' => null
            ]);

            $actEstado = $this->creaEstEntregaOrden($orden, $idEst, null);
            if (!$actEstado['estado']) {
                return $this->responseAjaxServerError($actEstado['mensaje'], []);
            }
            return $this->responseAjaxSuccess("", $ext_id);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal creando el envío");
        }
    }

    public function actualizarEntregaOrden($orden, $idEstado)
    {
        try {
            $entregaOrden = DB::table('entrega_orden')
                ->select('entrega_orden.*')
                ->where('entrega_orden.orden', '=', $orden)->orderBy('entrega_orden.id', 'DESC')
                ->get()->first();

            if ($entregaOrden == null) {
                return $this->responseAjaxServerError("No se encontró la entrega");
            }

            $estadoAnt = DB::table('sis_estado')
                ->select('sis_estado.*')
                ->where('id', '=', $entregaOrden->estado)
                ->get()->first();

            if ($estadoAnt == null) {
                return $this->responseAjaxServerError("No se encontró el estado");
            }

            $entregaOrden = DB::table('entrega_orden')
                ->where('orden', '=', $orden)
                ->update(['estado' =>  $idEstado]);

            $crearBit = $this->creaEstEntregaOrden($orden, $idEstado, $estadoAnt->id);
            if (!$crearBit['estado']) {
                return $this->responseAjaxServerError($crearBit['mensaje'], []);
            }
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal actualizando el envío");
        }
    }

    public function creaEstEntregaOrden($orden, $idEstado, $idEstadoAnteriorV)
    {
        try {
            $fechaActual = date("Y-m-d H:i:s");

            $entrega = DB::table('entrega_orden')
                ->leftjoin('sis_estado', 'sis_estado.id', '=', 'entrega_orden.estado')
                ->select('entrega_orden.id as idEntrega', 'sis_estado.nombre as dscEstado', 'sis_estado.id as idEstado')
                ->where('entrega_orden.orden', '=', $orden)->orderBy('entrega_orden.id', 'DESC')
                ->get()->first();

            if ($entrega == null) {
                return $this->responseAjaxServerError("No se encontró la entrega");
            }

            $estado = DB::table('sis_estado')
                ->select('sis_estado.*')
                ->where('id', '=', $idEstado)
                ->get()->first();

            if ($estado == null) {
                return $this->responseAjaxServerError("No se encontró el estado");
            }

            if ($idEstadoAnteriorV == null) {
                $texto = "Se crea la entrega en estado [ " . $entrega->dscEstado . " ] " .
                    ". Fecha : " . $fechaActual . ", encargado : " . session('usuario')['usuario'];
            } else {
                $estadoAnt = DB::table('sis_estado')
                    ->select('sis_estado.*')
                    ->where('id', '=',   $idEstadoAnteriorV)->get()->first();

                if ($estadoAnt == null) {
                    return $this->responseAjaxServerError("No se encontró el estado");
                }
                $texto = "La orden cambia del estado [ " . $estadoAnt->nombre . " ] al estado [ " . $estado->nombre . " ] " .
                    ". Fecha : " . $fechaActual . ", encargado : " . session('usuario')['usuario'];
            }

            DB::table('est_entrega_orden')->insert([
                'id' => null, 'usuario' => session('usuario')['id'],
                'entrega_orden' => $entrega->idEntrega, 'estado' => $idEstado,
                'fecha' =>  $fechaActual, 'descripcion' => $texto
            ]);

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal creando el estado del envío");
        }
    }

    public function goOrdenesEntrega()
    {
        if (!$this->validarSesion("entregas_pend")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'estadosOrden' => SisEstadoController::getEstadosByCodClase("EST_ENTREGAS_ORDEN"),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("entregas.ordenesEntrega", compact("data"));
    }

    public function filtrarOrdenesEntrega(Request $request)
    {
        if (!$this->validarSesion("entregas_pend")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $filtro = $request->input('filtro');

        $filtroSucursal =  $this->getUsuarioSucursal();
        $hasta = $filtro['hasta'];
        $desde = $filtro['desde'];
        $estado = $filtro['estadoEntrega'];

        $ordenes = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
            ->leftjoin('entrega_orden', 'entrega_orden.orden', '=', 'orden.id')
            ->select(
                'orden.*',
                'sis_estado.nombre as estadoOrden',
                'sis_estado.cod_general',
                'sucursal.descripcion as nombreSucursal'
            )->where('orden.ind_requiere_envio', '=', 1);


        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $ordenes = $ordenes->where('orden.sucursal', '=',  $filtroSucursal);
        }

        if (!$this->isNull($estado) && $estado != 'T') {
            $ordenes = $ordenes->where('entrega_orden.estado', '=',  $estado);
        }

        if (!$this->isNull($desde)) {
            $ordenes = $ordenes->where('orden.fecha_inicio', '>=', $desde);
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $ordenes = $ordenes->where('orden.fecha_inicio', '<', $mod_date);
        }

        $ordenes = $ordenes->orderBy('orden.fecha_inicio', 'DESC')->get();

        foreach ($ordenes as $o) {
            $o->detalles = DB::table('detalle_orden')->where('orden', '=', $o->id)->get();
            $o->entrega = DB::table('entrega_orden')->leftjoin('sis_estado', 'sis_estado.id', '=', 'entrega_orden.estado')
                ->select(
                    'entrega_orden.*',
                    'sis_estado.nombre as estadoOrden',
                    'sis_estado.cod_general'
                )
                ->where('entrega_orden.orden', '=', $o->id)->get()->first();
                $o->idOrdenEnc =encrypt($o->id);
                $o->fechaFormat = $this->fechaFormat($o->fecha_inicio);
        }

        return  $this->responseAjaxSuccess("", $ordenes);
    }

    public function iniciarRutaEntrega(Request $request)
    {
        if (!$this->validarSesion("entregas_pend")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        $entrega = DB::table('entrega_orden')->select('entrega_orden.*')->where('orden', '=', $id_orden)->get()->first();

        if ($orden == null) {
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($entrega == null) {
            return $this->responseAjaxServerError('No existe la entrega.', []);
        }


        if ($entrega->estado != SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_PEND_SALIDA_LOCAL')) {
            return $this->responseAjaxServerError('La entrega ya fue procesada', []);
        }

        $fechaActual = date("Y-m-d H:i:s");
        try {
            $idEstEntrega = SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_EN_RUTA');

            DB::beginTransaction();
         
            $respuesta = $this->actualizarEntregaOrden($id_orden, $idEstEntrega);

            if (!$respuesta['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($respuesta['mensaje'], []);
            }

            DB::commit();

            return $this->responseAjaxSuccess("", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    public function entregarOrden(Request $request)
    {
        if (!$this->validarSesion("entregas_pend")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        $entrega = DB::table('entrega_orden')->select('entrega_orden.*')->where('orden', '=', $id_orden)->get()->first();

        if ($orden == null) {
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($entrega == null) {
            return $this->responseAjaxServerError('No existe la entrega.', []);
        }


        if ($entrega->estado != SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_EN_RUTA')) {
            return $this->responseAjaxServerError('La entrega ya fue procesada', []);
        }

        $fechaActual = date("Y-m-d H:i:s");
        try {
            $idEstEntrega = SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_TERMINADA');

            DB::beginTransaction();
         
            $respuesta = $this->actualizarEntregaOrden($id_orden, $idEstEntrega);

            if (!$respuesta['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($respuesta['mensaje'], []);
            }

            DB::commit();

            return $this->responseAjaxSuccess("", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }
}
