<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\FacturacionController;

class UsuarioExternoController extends Controller
{
    use SpaceUtil;


    public function __construct()
    {
    }

    public function goMenu()
    {
        if (!$this->validarSesion("usuExtMnu")) {
            return redirect('/');
        }

        $contro = new FacturacionController();
        $categorias =  $contro->getCategoriasTodosProductos($this->getUsuarioSucursal());

        $data = [
            'categorias' => $categorias,
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('usuarioExterno.menu', compact('data'));
    }

    public function goMenuMobile()
    {
        $contro = new FacturacionController();
        $categorias =  $contro->getCategoriasTodosProductos(1);

        $data = [
            'categorias' => $categorias
        ];
        return view('usuarioExterno.menuMobile', compact('data'));
    }

    public function cargarTiposGeneral(Request $request)
    {
        if (!$this->validarSesion("usuExtMnu")) {
            return redirect('/');
            return $this->responseAjaxServerError("No tienes permisos", "");
        }
        $contro = new FacturacionController();
        $categorias =  $contro->getCategoriasTodosProductos($this->getUsuarioSucursal());


        return $this->responseAjaxSuccess("", $categorias);
    }

    public function cargarTiposGeneralMobile(Request $request)
    {
        $contro = new FacturacionController();
        $categorias =  $contro->getCategoriasTodosProductos(1);

        return $this->responseAjaxSuccess("", $categorias);
    }

    public function goTrackingOrden($encryptedOrderId)
    {
        $id_orden = Crypt::decrypt($encryptedOrderId);
        if ($id_orden < 1 || $this->isNull($id_orden)) {
            $this->setError('Campos Requeridos', "No se encontró la orden");
            return redirect('/');
        }
        $orden = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select(
                'orden.*',
                'sis_estado.nombre as estadoOrden',
                'sis_estado.cod_general'
            )->where('orden.id', '=', $id_orden)->get()->first();
        if ($this->isNull($orden)) {
            $this->setError('Campos Requeridos', "No se encontró la orden");
            return redirect('/');
        }

        $orden->detalles = DB::table('detalle_orden')->where('orden', '=', $id_orden)->get();
        $orden->entrega = DB::table('entrega_orden')->leftjoin('sis_estado', 'sis_estado.id', '=', 'entrega_orden.estado')
            ->select(
                'entrega_orden.*',
                'sis_estado.nombre as estadoOrden',
                'sis_estado.cod_general'
            )
            ->where('entrega_orden.orden', '=', $id_orden)->get()->first();
        $orden->fechaFormat = $this->fechaFormat($orden->fecha_inicio);

        if($orden->entrega != null){
            $orden->entrega->estados = DB::table('est_entrega_orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'est_entrega_orden.estado')
            ->select(
                'est_entrega_orden.*',
                'sis_estado.nombre as estadoOrden',
                'sis_estado.cod_general'
            )->where('est_entrega_orden.entrega_orden', '=', $orden->entrega->id)
            ->orderBy('fecha','ASC')->get();
            foreach($orden->entrega->estados as $e){
                $phpdate = strtotime($e->fecha);
                $e->hora = date("g:i a", $phpdate);
            }
        }
       
        $data = [
            'orden' => $orden
        ];
        return view('usuarioExterno.trackingOrden', compact('data'));
    }
}
