<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;

class FeController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goFacturasFe()
    {
        if (!$this->validarSesion("fe_fes")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("fe.facturas", compact("data"));
    }

    public function filtrarFacturas(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $filtro = $request->input('filtro');

        $filtroSucursal =  $filtro['sucursal'];
        $hasta = $filtro['hasta'];
        $desde = $filtro['desde'];

        $ordenes = DB::table('orden')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
            ->join('fe_info', 'fe_info.orden', '=', 'orden.id')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'fe_info.estado')
            ->select(
                'orden.*',
                'sis_estado.nombre as estadoFe',
                'sis_estado.cod_general',
                'sucursal.descripcion as nombreSucursal',
                'fe_info.cedula as cedFe',
                'fe_info.id as idFe',
                'fe_info.num_comprobante as comprobanteFe',
                'fe_info.nombre as nombreFe',
                'fe_info.num_comprobante as num_comprobanteFe',
                'fe_info.correo as correoFe'
            );

        $ordenes = $ordenes->where('orden.factura_electronica', '=',  'S');
        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $ordenes = $ordenes->where('orden.sucursal', '=',  $filtroSucursal);
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
            $phpdate = strtotime($o->fecha_inicio);
            $date = date("d-m-Y", strtotime($o->fecha_inicio));
    
            $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
            $fechaAux .= ' - ' . date("g:i a", $phpdate);
            $o->fechaFormat =  $fechaAux;
            $o->idOrdenEnc = encrypt($o->id);
        }
        return  $this->responseAjaxSuccess("", $ordenes);
    }

    public function enviarFe(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idOrden = $request->input("idOrden");
        $idInfoFe = $request->input("idInfoFe");
        $numComprobante =  $request->input("numComprobante");
      
        if ($idOrden == null || $idOrden == 0) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        if ($idInfoFe == null || $idInfoFe == 0) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        $orden = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.cod_general')
            ->where('orden.id', '=', $idOrden)->get()->first();


        if ($orden == null) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        if ($numComprobante == null || $numComprobante == '') {
            return $this->responseAjaxServerError("Debe ingresar el número de comprobante de hacienda", []);
        }

        if ($orden->cod_general == 'ORD_ANULADA') {
            return $this->responseAjaxServerError("La orden se encuentra anulada", []);
        }

        try {
            DB::beginTransaction();

            DB::table('fe_info')
                ->where('id', '=', $idInfoFe)
                ->update(['estado' =>  SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_ENVIADA'),
                'num_comprobante' => $numComprobante]);

         
            DB::commit();

            return $this->responseAjaxSuccess("Se marcó como enviada la factura electrónica.", $idOrden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

}
