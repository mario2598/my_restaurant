<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
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
            ->select('orden.*', 'sis_estado.nombre as descEstado')
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
            ->where('orden.cierre_caja', '=', CajaController::getIdCaja(session('usuario')['id'], $sucursal))

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
                if($d->tipo_producto == 'R'){
                    $d->receta =  DB::table('producto_menu')
                    ->select('producto_menu.receta')
                        ->where('producto_menu.codigo', '=', $d->codigo_producto)
                        ->get()->first()->receta ?? "";
                }else{
                    $d->receta = "";  
                }
                $d->extras = DB::table('extra_detalle_orden')->select('extra_detalle_orden.*')
                    ->where('extra_detalle_orden.orden', '=', $o->id)
                    ->where('extra_detalle_orden.detalle', '=', $d->id)

                    ->get() ?? [];
                $d->tieneExtras = count($d->extras) > 0;
            }}

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

        try {
            DB::beginTransaction();
            $detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('orden', '=', $id_orden)->get();
            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'estado' => SisEstadoController::getIdEstadoByCodGeneral('ORD_PARA_ENTREGA'), 'fecha_preparado' => date("Y-m-d H:i:s"), 'cocina_terminado' => 'S'
                ]);

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

        try {
            DB::beginTransaction();
            $detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('orden', '=', $id_orden)->get();
            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'estado' => SisEstadoController::getIdEstadoByCodGeneral('ORD_ENTREGADA'), 'fecha_entregado' => date("Y-m-d H:i:s"), 'cocina_terminado' => 'S'
                ]);

            DB::commit();


            return $this->setAjaxResponse(200, "", [], true);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Terminar Preparación Orden', 'Algo salio mal...');
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }}
}
