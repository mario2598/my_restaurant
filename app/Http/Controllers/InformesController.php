<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;
class InformesController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {
       
        setlocale(LC_ALL, "es_ES");
    }
    
    public function index(){

    }

    public function goResumenContable(){
        if(!$this->validarSesion("informes")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
     
        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
        ];

        
        $data = [
             'menus'=> $this->cargarMenus(),
            'filtros' =>$filtros,
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('informes.resumenContable',compact('data'));
    }

    public function goResumenContableFiltro(Request $request){
        if(!$this->validarSesion("informes")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
      

        $filtroSucursal = $request->input('sucursal');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
        ];
        
        $data = [
             'menus'=> $this->cargarMenus(),
            'resumen' =>$this->resumenContable($desde,$hasta,$filtroSucursal),
            'sucursales' => $this->getSucursales(),
            'filtros' =>$filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('informes.resumenContable',compact('data'));
    }

    public function goVentaGenProductos(){
        if(!$this->validarSesion("ventaGenProductos")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
       
        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
            'horaDesdeFiltro' => "",
            'filtroTipoProd' => "",
            'horaHastaFiltro' => ""
        ];

        
        $data = [
             'menus'=> $this->cargarMenus(),
             'datosReporte'=> [],
            'filtros' =>$filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('informes.ventasGenProductos',compact('data'));
    }

    public function goMovInvProductoExterno(){
        if(!$this->validarSesion("movInvProductoExterno")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
       
        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
            'descUsuario' => ""
        ];

        
        $data = [
            'menus'=> $this->cargarMenus(),
            'datosReporte'=> [],
            'filtros' =>$filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('informes.movInvProductoExterno',compact('data'));
    }

    public function goMovInvProductoExternoFiltro(Request $request)
    {
        if (!$this->validarSesion("movInvProductoExterno")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroDescProd = $request->input('descProd');
        $filtroDescUsuario = $request->input('descUsuario');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $query = "SELECT usu.nombre as nombreUsuario,suc.descripcion as nombreSucursal, " .
        "usu.usuario,inv.fecha,pe.nombre as nombreProducto,inv.detalle,inv.cantidad_anterior,inv.cantidad_ajustada,inv.cantidad_nueva ".
        "FROM coffee_to_go.bit_inv_producto_externo inv join  coffee_to_go.usuario usu on usu.id = inv.usuario ".
        "join coffee_to_go.producto_externo pe on pe.id = inv.producto ".
        "join coffee_to_go.sucursal suc on suc.id = inv.sucursal ";
       $where = " where 1 = 1 ";

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =".$filtroSucursal;
        }

        if (!$this->isNull($desde)) {
            $where .= " and inv.fecha > '".$desde."'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and inv.fecha < '".$mod_date."'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(pe.nombre) like UPPER('%".$filtroDescProd."%')";
        }

        if ($filtroDescUsuario != ''  && !$this->isNull($filtroDescUsuario)) {
            $where .= " and  ( UPPER(usu.usuario) like UPPER('%".$filtroDescUsuario."%') or UPPER(usu.nombre) like UPPER('%".$filtroDescUsuario."%'))";
        }

        $query .= $where . " order by inv.fecha DESC";
        dd($query);
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'descUsuario' => $filtroDescUsuario
        ];
        dd(DB::select($query));
        $data = [
            'menus'=> $this->cargarMenus(),
            'datosReporte'=> DB::select($query),
           'filtros' =>$filtros,
           'sucursales' => $this->getSucursalesAndBodegas(),
           'panel_configuraciones' => $this->getPanelConfiguraciones()
       ];
       
       return view('informes.movInvProductoExterno',compact('data'));

    }

    
    public function goVentaGenProductosFiltro(Request $request)
    {
        if (!$this->validarSesion("ventaGenProductos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroTipoProd = $request->input('filtroTipoProd');
        $filtroDescProd = $request->input('descProd');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        $horaHasta = $request->input('horaHastaFiltro');
        $horaDesde = $request->input('horaDesdeFiltro');

        $query = "SELECT do.nombre_producto PRODUCTO" .
        ",suc.descripcion as SUCURSAL, ".
        "sum(do.cantidad) ".
        "CANTIDAD, do.precio_unidad, Sum(do.cantidad * do.precio_unidad) as total_venta,".
        " case do.tipo_producto when 'E' then 'Externo' else  'Cafetería'  end as tipo_producto FROM coffee_to_go.detalle_orden ".
        " do join coffee_to_go.orden o on do.orden = o.id ".
        " join coffee_to_go.usuario usu on usu.id = o.cajero ".
        " left join coffee_to_go.sucursal suc on suc.id = o.sucursal ".
        " left join coffee_to_go.cliente cli on cli.id = o.cliente ";
       $where = " where o.estado <> " . SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA');

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =".$filtroSucursal;
        }

        if ($filtroTipoProd != '' && $filtroTipoProd != 'T') {
            $where .= " and do.tipo_producto ='".$filtroTipoProd."' ";
        }

        if (!$this->isNull($desde)) {
            $where .= " and o.fecha_inicio > '".$desde."'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and o.fecha_inicio < '".$mod_date."'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(do.nombre_producto) like UPPER('%".$filtroDescProd."%')";
        }


        if (!$this->isNull($horaHasta) && $horaHasta < 24  &&  $horaHasta >= 0) {
            $where .= " and HOUR(o.fecha_inicio) <= ".$horaHasta;
        }

        
        if (!$this->isNull($horaDesde) && $horaDesde < 24  &&  $horaDesde >= 0) {
            $where .= " and HOUR(o.fecha_inicio) >= ".$horaDesde;
        }
       
        $query .= $where . " group by do.nombre_producto,suc.descripcion,do.precio_unidad,coffee_to_go.do.tipo_producto order by 3 DESC";

        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'horaDesdeFiltro' => $horaDesde,
            'filtroTipoProd' => $filtroTipoProd,
            'horaHastaFiltro' => $horaHasta
        ];
 
        $data = [
            'menus'=> $this->cargarMenus(),
            'datosReporte'=> DB::select($query),
           'filtros' =>$filtros,
           'sucursales' => $this->getSucursalesAndBodegas(),
           'panel_configuraciones' => $this->getPanelConfiguraciones()
       ];
       
       return view('informes.ventasGenProductos',compact('data'));

    }
    
    public function goVentaXhora(){
        if(!$this->validarSesion("ventaXhora")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
       
        $filtros = [
            'cliente' => 0,
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
            'nombreUsu' => "",
            'horaDesdeFiltro' => "",
            'filtroTipoProd' => "",
            'horaHastaFiltro' => ""
        ];

        
        $data = [
             'menus'=> $this->cargarMenus(),
             'clientes'=> $this->getClientes(),
             'datosReporte'=> [],
            'filtros' =>$filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('informes.ventasXhora',compact('data'));
    }

    public function goVentaXhoraFiltro(Request $request)
    {
        if (!$this->validarSesion("ventaXhora")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroCliente = $request->input('cliente');
        $filtroSucursal = $request->input('sucursal');
        $filtroTipoProd = $request->input('filtroTipoProd');
        $filtroDescProd = $request->input('descProd');
        $filtronombreUsu = $request->input('nombreUsu');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        $horaHasta = $request->input('horaHastaFiltro');
        $horaDesde = $request->input('horaDesdeFiltro');

        $query = "SELECT DATE_FORMAT(o.fecha_inicio, '%Y-%m-%d') FECHA, DATE_FORMAT(o.fecha_inicio, '%h %p') HORA ,do.nombre_producto PRODUCTO, usu.usuario AS " .
        "USUARIO,NVL(cli.nombre,'') as CLIENTE,suc.descripcion as SUCURSAL,HOUR(o.fecha_inicio) as HORAFILTRO, ".
        "sum(do.cantidad) ".
        "CANTIDAD, do.precio_unidad, Sum(do.cantidad * do.precio_unidad) as total_venta,".
        " case do.tipo_producto when 'E' then 'Externo' else  'Propio'  end as tipo_producto FROM coffee_to_go.detalle_orden ".
        " do join coffee_to_go.orden o on do.orden = o.id ".
        " join coffee_to_go.usuario usu on usu.id = o.cajero ".
        " left join coffee_to_go.sucursal suc on suc.id = o.sucursal ".
        " left join coffee_to_go.cliente cli on cli.id = o.cliente ";
       $where = " where o.estado <> " . SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA');

        if ($filtroCliente >= 1  && !$this->isNull($filtroCliente)) {
            $where .= " and cli.id =".$filtroCliente;
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =".$filtroSucursal;
        }

        if ($filtroTipoProd != '' && $filtroTipoProd != 'T') {
            $where .= " and do.tipo_producto ='".$filtroTipoProd."' ";
        }

        if (!$this->isNull($desde)) {
            $where .= " and o.fecha_inicio > '".$desde."'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and o.fecha_inicio < '".$mod_date."'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(do.nombre_producto) like UPPER('%".$filtroDescProd."%')";
        }

        if ($filtronombreUsu != ''  && !$this->isNull($filtronombreUsu)) {
            $where .= " and  UPPER(usu.usuario) like UPPER('%".$filtronombreUsu."%')";
        }

        if (!$this->isNull($horaHasta) && $horaHasta < 24  &&  $horaHasta >= 0) {
            $where .= " and HOUR(o.fecha_inicio) <= ".$horaHasta;
        }

        
        if (!$this->isNull($horaDesde) && $horaDesde < 24  &&  $horaDesde >= 0) {
            $where .= " and HOUR(o.fecha_inicio) >= ".$horaDesde;
        }
       
        $query .= $where . " group by do.nombre_producto,DATE_FORMAT(o.fecha_inicio, '%Y-%m-%d'),DATE_FORMAT(o.fecha_inicio, '%h %p'),usu.usuario,NVL(cli.nombre,''),suc.descripcion,HOUR(o.fecha_inicio),do.precio_unidad,coffee_to_go.do.tipo_producto order by 1 DESC,2 ASC,7 ASC";

        $filtros = [
            'cliente' => $filtroCliente,
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'nombreUsu' => $filtronombreUsu,
            'horaDesdeFiltro' => $horaDesde,
            'filtroTipoProd' => $filtroTipoProd,
            'horaHastaFiltro' => $horaHasta
        ];
 
        $data = [
            'menus'=> $this->cargarMenus(),
            'clientes'=> $this->getClientes(),
            'datosReporte'=> DB::select($query),
           'filtros' =>$filtros,
           'sucursales' => $this->getSucursalesAndBodegas(),
           'panel_configuraciones' => $this->getPanelConfiguraciones()
       ];
       
       return view('informes.ventasXhora',compact('data'));

    }
  
}
