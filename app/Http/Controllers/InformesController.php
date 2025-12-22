<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;
use Codedge\Fpdf\Fpdf\Fpdf;

class InformesController extends Controller
{
    use SpaceUtil;
    private $admin;
    private $pdf;
    
    public function __construct()
    {
        $this->pdf = new Fpdf();
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goResumenContable()
    {
        if (!$this->validarSesion("informes")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
        ];

        // Inicializar resumen vacío para evitar errores en la vista
        $resumen = [
            'totalIngresosSinpeGeneral' => 0,
            'totalIngresosTarjetaGeneral' => 0,
            'totalPagoTarjetaGeneral' => 0,
            'totalIngresosEfectivoGeneral' => 0,
            'subTotalFondosGeneral' => 0,
            'gastosGeneral' => 0,
            'totalFondosGeneral' => 0,
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'resumen' => $resumen,
            'filtros' => $filtros,
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.resumenContable', compact('data'));
    }

    public function goResumenContableFiltro(Request $request)
    {
        if (!$this->validarSesion("informes")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        // Validar y normalizar valores
        if ($this->isNull($filtroSucursal) || $filtroSucursal == '') {
            $filtroSucursal = 'T';
        }

        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta ?? '',
            'desde' => $desde ?? '',
        ];

        // Obtener resumen contable
        $resumen = $this->resumenContable($desde, $hasta, $filtroSucursal);

        $data = [
            'menus' => $this->cargarMenus(),
            'resumen' => $resumen,
            'sucursales' => $this->getSucursales(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('informes.resumenContable', compact('data'));
    }

    public function goVentaGenProductos()
    {
        if (!$this->validarSesion("ventaGenProductos")) {
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
            'menus' => $this->cargarMenus(),
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.ventasGenProductos', compact('data'));
    }

    public function goMovInvProductoExterno()
    {
        if (!$this->validarSesion("movInvProductoExterno")) {
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
            'menus' => $this->cargarMenus(),
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.movInvProductoExterno', compact('data'));
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
            "usu.usuario,inv.fecha,pe.nombre as nombreProducto,inv.detalle,inv.cantidad_anterior,inv.cantidad_ajustada,inv.cantidad_nueva " .
            "FROM bit_inv_producto_externo inv join  usuario usu on usu.id = inv.usuario " .
            "join producto_externo pe on pe.id = inv.producto " .
            "join sucursal suc on suc.id = inv.sucursal ";
        $where = " where 1 = 1 ";

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if (!$this->isNull($desde)) {
            $where .= " and inv.fecha > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and inv.fecha < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(pe.nombre) like UPPER('%" . $filtroDescProd . "%')";
        }

        if ($filtroDescUsuario != ''  && !$this->isNull($filtroDescUsuario)) {
            $where .= " and  ( UPPER(usu.usuario) like UPPER('%" . $filtroDescUsuario . "%') or UPPER(usu.nombre) like UPPER('%" . $filtroDescUsuario . "%'))";
        }

        $query .= $where . " order by inv.fecha DESC";
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'descUsuario' => $filtroDescUsuario
        ];
        $datos = DB::select($query);
        foreach ($datos as $d) {
            $d->fecha = $this->fechaFormat($d->fecha);
        }
        $data = [
            'menus' => $this->cargarMenus(),
            'datosReporte' =>  $datos,
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.movInvProductoExterno', compact('data'));
    }

    public function goMovConMateriaPrima()
    {
        if (!$this->validarSesion("movConMateriaPrima")) {
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
            'menus' => $this->cargarMenus(),
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.movConMateriaPrima', compact('data'));
    }

    public function goMovConMateriaPrimaFiltro(Request $request)
    {
        if (!$this->validarSesion("movConMateriaPrima")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroDescProd = $request->input('descProd');
        $filtroDescUsuario = $request->input('descUsuario');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $query = "SELECT usu.nombre as nombreUsuario,suc.descripcion as nombreSucursal, " .
            "usu.usuario,inv.fecha,pe.nombre as nombreProducto,inv.detalle,inv.cantidad_anterior,inv.cantidad_ajuste,inv.cantidad_nueva,pe.unidad_medida " .
            "FROM bit_materia_prima inv join  usuario usu on usu.id = inv.usuario " .
            "join materia_prima pe on pe.id = inv.materia_prima join sucursal suc on suc.id = inv.sucursal ";
        $where = " where 1 = 1 ";

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if (!$this->isNull($desde)) {
            $where .= " and inv.fecha > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and inv.fecha < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(pe.nombre) like UPPER('%" . $filtroDescProd . "%')";
        }

        if ($filtroDescUsuario != ''  && !$this->isNull($filtroDescUsuario)) {
            $where .= " and  ( UPPER(usu.usuario) like UPPER('%" . $filtroDescUsuario . "%') or UPPER(usu.nombre) like UPPER('%" . $filtroDescUsuario . "%'))";
        }

        $query .= $where . " order by inv.fecha DESC";
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd,
            'descUsuario' => $filtroDescUsuario
        ];
        $datos = DB::select($query);
        foreach ($datos as $d) {
            $d->fecha = $this->fechaFormat($d->fecha);
        }
        $data = [
            'menus' => $this->cargarMenus(),
            'datosReporte' =>  $datos,
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.movConMateriaPrima', compact('data'));
    }

    public function goConMateriaPrima()
    {
        if (!$this->validarSesion("conMateriaPrima")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
            'descProd' => "",
        ];


        $data = [
            'menus' => $this->cargarMenus(),
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.conMateriaPrima', compact('data'));
    }

    public function goConMateriaPrimaFiltro(Request $request)
    {
        if (!$this->validarSesion("conMateriaPrima")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $filtroDescProd = $request->input('descProd');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $query = "SELECT suc.descripcion as nombreSucursal,pe.nombre as nombreProducto,pe.unidad_medida,sum(inv.cantidad_ajuste) as suma,pe.precio as precio_unidad, (sum(inv.cantidad_ajuste) * pe.precio) as costo " .
            "FROM bit_materia_prima inv join  usuario usu on usu.id = inv.usuario " .
            "join materia_prima pe on pe.id = inv.materia_prima join sucursal suc on suc.id = inv.sucursal ";
        $where = " where inv.cantidad_anterior > inv.cantidad_nueva ";

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if (!$this->isNull($desde)) {
            $where .= " and inv.fecha > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and inv.fecha < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(pe.nombre) like UPPER('%" . $filtroDescProd . "%')";
        }


        $query .= $where . " group by suc.descripcion,pe.nombre,pe.unidad_medida,pe.precio";
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
            'descProd' => $filtroDescProd
        ];
        $datos = DB::select($query);

        $data = [
            'menus' => $this->cargarMenus(),
            'datosReporte' =>  $datos,
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.conMateriaPrima', compact('data'));
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
            ",suc.descripcion as SUCURSAL, " .
            "sum(do.cantidad) " .
            "CANTIDAD, do.precio_unidad, Sum(do.cantidad * do.precio_unidad) as total_venta," .
            " case do.tipo_producto when 'E' then 'Externo' else  'Cafetería'  end as tipo_producto FROM detalle_orden " .
            " do join orden o on do.orden = o.id " .
            " join usuario usu on usu.id = o.cajero " .
            " left join sucursal suc on suc.id = o.sucursal " .
            " left join cliente cli on cli.id = o.cliente ";
        $where = " where o.estado <> " . SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA');

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if ($filtroTipoProd != '' && $filtroTipoProd != 'T') {
            $where .= " and do.tipo_producto ='" . $filtroTipoProd . "' ";
        }

        if (!$this->isNull($desde)) {
            $where .= " and o.fecha_inicio > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and o.fecha_inicio < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(do.nombre_producto) like UPPER('%" . $filtroDescProd . "%')";
        }


        if (!$this->isNull($horaHasta) && $horaHasta < 24  &&  $horaHasta >= 0) {
            $where .= " and HOUR(o.fecha_inicio) <= " . $horaHasta;
        }


        if (!$this->isNull($horaDesde) && $horaDesde < 24  &&  $horaDesde >= 0) {
            $where .= " and HOUR(o.fecha_inicio) >= " . $horaDesde;
        }

        $query .= $where . " group by do.nombre_producto,suc.descripcion,do.precio_unidad,do.tipo_producto order by 3 DESC";

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
            'menus' => $this->cargarMenus(),
            'datosReporte' => DB::select($query),
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.ventasGenProductos', compact('data'));
    }

    public function goVentaXhora()
    {
        if (!$this->validarSesion("ventaXhora")) {
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
            'menus' => $this->cargarMenus(),
            'clientes' => $this->getClientes(),
            'datosReporte' => [],
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.ventasXhora', compact('data'));
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
            "USUARIO,NVL(cli.nombre,'') as CLIENTE,suc.descripcion as SUCURSAL,HOUR(o.fecha_inicio) as HORAFILTRO, " .
            "sum(do.cantidad) " .
            "CANTIDAD, do.precio_unidad, Sum(do.cantidad * do.precio_unidad) as total_venta," .
            " case do.tipo_producto when 'E' then 'Externo' else  'Propio'  end as tipo_producto FROM detalle_orden " .
            " do join orden o on do.orden = o.id " .
            " join usuario usu on usu.id = o.cajero " .
            " left join sucursal suc on suc.id = o.sucursal " .
            " left join cliente cli on cli.id = o.cliente ";
        $where = " where o.estado <> " . SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA');

        if ($filtroCliente >= 1  && !$this->isNull($filtroCliente)) {
            $where .= " and cli.id =" . $filtroCliente;
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $where .= " and suc.id =" . $filtroSucursal;
        }

        if ($filtroTipoProd != '' && $filtroTipoProd != 'T') {
            $where .= " and do.tipo_producto ='" . $filtroTipoProd . "' ";
        }

        if (!$this->isNull($desde)) {
            $where .= " and o.fecha_inicio > '" . $desde . "'";
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $where .= " and o.fecha_inicio < '" . $mod_date . "'";
        }


        if ($filtroDescProd != ''  && !$this->isNull($filtroDescProd)) {
            $where .= " and  UPPER(do.nombre_producto) like UPPER('%" . $filtroDescProd . "%')";
        }

        if ($filtronombreUsu != ''  && !$this->isNull($filtronombreUsu)) {
            $where .= " and  UPPER(usu.usuario) like UPPER('%" . $filtronombreUsu . "%')";
        }

        if (!$this->isNull($horaHasta) && $horaHasta < 24  &&  $horaHasta >= 0) {
            $where .= " and HOUR(o.fecha_inicio) <= " . $horaHasta;
        }


        if (!$this->isNull($horaDesde) && $horaDesde < 24  &&  $horaDesde >= 0) {
            $where .= " and HOUR(o.fecha_inicio) >= " . $horaDesde;
        }

        $query .= $where . " group by do.nombre_producto,DATE_FORMAT(o.fecha_inicio, '%Y-%m-%d'),DATE_FORMAT(o.fecha_inicio, '%h %p'),usu.usuario,NVL(cli.nombre,''),suc.descripcion,HOUR(o.fecha_inicio),do.precio_unidad,do.tipo_producto order by 1 DESC,2 ASC,7 ASC";

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
            'menus' => $this->cargarMenus(),
            'clientes' => $this->getClientes(),
            'datosReporte' => DB::select($query),
            'filtros' => $filtros,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('informes.ventasXhora', compact('data'));
    }

    public function generarReporteResumenContablePDF(Request $request)
    {
        if (!$this->validarSesion("informes")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        // Validar y normalizar valores
        if ($this->isNull($filtroSucursal) || $filtroSucursal == '') {
            $filtroSucursal = 'T';
        }

        // Obtener resumen contable
        $resumen = $this->resumenContable($desde, $hasta, $filtroSucursal);

        // Obtener ingresos individuales
        $ingresosQuery = DB::table('ingreso')
            ->leftjoin('tipo_ingreso', 'tipo_ingreso.id', '=', 'ingreso.tipo')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select(
                'ingreso.*',
                'sucursal.descripcion as nombreSucursal',
                'tipo_ingreso.tipo as nombre_tipo_ingreso',
                'usuario.usuario as nombreUsuario',
                'sis_estado.nombre as dscEstado'
            )
            ->where('sis_estado.cod_general', '=', 'ING_EST_APROBADO');

        if ($filtroSucursal != null && $filtroSucursal != '' && $filtroSucursal != 'T') {
            $ingresosQuery = $ingresosQuery->where('ingreso.sucursal', '=', $filtroSucursal);
        }

        if ($desde != null && $desde != '') {
            $desdeDate = date('Y-m-d 00:00:00', strtotime($desde));
            $ingresosQuery = $ingresosQuery->where('ingreso.fecha', '>=', $desdeDate);
        }

        if ($hasta != null && $hasta != '') {
            $hastaDate = date('Y-m-d 23:59:59', strtotime($hasta));
            $ingresosQuery = $ingresosQuery->where('ingreso.fecha', '<=', $hastaDate);
        }

        $ingresos = $ingresosQuery->orderBy('ingreso.fecha', 'DESC')->get();

        // Calcular totales por ingreso
        foreach ($ingresos as $i) {
            $sinpe = $i->monto_sinpe ?? 0;
            $efectivo = $i->monto_efectivo ?? 0;
            $tarjeta = $i->monto_tarjeta ?? 0;
            $i->total = $sinpe + $efectivo + $tarjeta;
            $i->fecha_formateada = $this->fechaFormat($i->fecha);
        }

        // Obtener gastos individuales
        $gastosQuery = DB::table('gasto')
            ->leftjoin('tipo_gasto', 'tipo_gasto.id', '=', 'gasto.tipo_gasto')
            ->leftjoin('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'gasto.sucursal')
            ->leftjoin('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'gasto.estado')
            ->select(
                'gasto.*',
                'tipo_gasto.tipo as nombre_tipo_gasto',
                'proveedor.nombre as nombreProveedor',
                'sucursal.descripcion as nombreSucursal',
                'usuario.usuario as nombreUsuario',
                'sis_estado.nombre as dscEstado'
            )
            ->where('sis_estado.cod_general', '!=', 'EST_GASTO_ELIMINADO');

        if ($filtroSucursal != null && $filtroSucursal != '' && $filtroSucursal != 'T') {
            $nombreSucursal = DB::table('sucursal')->where('id', '=', $filtroSucursal)->first();
            if ($nombreSucursal) {
                $gastosQuery = $gastosQuery->where('gasto.sucursal', 'like', '%' . $nombreSucursal->descripcion . '%');
            }
        }

        if ($desde != null && $desde != '') {
            $desdeDate = date('Y-m-d 00:00:00', strtotime($desde));
            $gastosQuery = $gastosQuery->where('gasto.fecha', '>=', $desdeDate);
        }

        if ($hasta != null && $hasta != '') {
            $hastaDate = date('Y-m-d 23:59:59', strtotime($hasta));
            $gastosQuery = $gastosQuery->where('gasto.fecha', '<=', $hastaDate);
        }

        $gastos = $gastosQuery->orderBy('gasto.fecha', 'DESC')->get();

        foreach ($gastos as $g) {
            $g->fecha_formateada = $this->fechaFormat($g->fecha);
        }

        // Obtener facturas electrónicas enviadas/aceptadas
        $facturasQuery = DB::table('fe_info')
            ->leftjoin('orden', 'orden.id', '=', 'fe_info.orden')
            ->leftjoin('pago_orden', 'pago_orden.id', '=', 'fe_info.id_pago')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
            ->leftjoin('sis_estado as estado_hacienda', 'estado_hacienda.id', '=', 'fe_info.estado_hacienda')
            ->select(
                'fe_info.*',
                'orden.numero_orden',
                'orden.fecha_inicio',
                'pago_orden.total as monto_factura',
                'pago_orden.fecha_pago',
                'sucursal.descripcion as nombreSucursal',
                'estado_hacienda.nombre as estadoHaciendaNombre'
            )
            ->where('estado_hacienda.cod_general', '=', 'HACIENDA_ACEPTADO')
            ->whereNotNull('fe_info.num_comprobante');

        if ($filtroSucursal != null && $filtroSucursal != '' && $filtroSucursal != 'T') {
            $facturasQuery = $facturasQuery->where('orden.sucursal', '=', $filtroSucursal);
        }

        if ($desde != null && $desde != '') {
            $desdeDate = date('Y-m-d 00:00:00', strtotime($desde));
            $facturasQuery = $facturasQuery->where('pago_orden.fecha_pago', '>=', $desdeDate);
        }

        if ($hasta != null && $hasta != '') {
            $hastaDate = date('Y-m-d 23:59:59', strtotime($hasta));
            $facturasQuery = $facturasQuery->where('pago_orden.fecha_pago', '<=', $hastaDate);
        }

        $facturas = $facturasQuery->orderBy('pago_orden.fecha_pago', 'DESC')->get();

        foreach ($facturas as $f) {
            $f->fecha_formateada = $this->fechaFormat($f->fecha_pago ?? $f->fecha_inicio ?? now());
        }

        // Obtener información de sucursal
        $nombreSucursal = 'Todas';
        if ($filtroSucursal != null && $filtroSucursal != '' && $filtroSucursal != 'T') {
            $sucursal = DB::table('sucursal')->where('id', '=', $filtroSucursal)->first();
            if ($sucursal) {
                $nombreSucursal = $sucursal->descripcion;
            }
        }

        // Generar PDF
        $this->generarPDFResumenContable($resumen, $ingresos, $gastos, $facturas, $desde, $hasta, $nombreSucursal);
    }

    private function generarPDFResumenContable($resumen, $ingresos, $gastos, $facturas, $desde, $hasta, $nombreSucursal)
    {
        $this->pdf->__construct('P', 'mm', 'A4');
        $this->pdf->SetAutoPageBreak(true, 20);
        $this->pdf->SetMargins(10, 15, 10);
        $this->pdf->AddPage();

        // Encabezado con borde
        $this->pdf->SetFillColor(41, 128, 185);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Arial', 'B', 18);
        $this->pdf->Cell(0, 12, iconv('UTF-8', 'ISO-8859-1', 'REPORTE RESUMEN CONTABLE GENERAL'), 0, 1, 'C', true);
        
        // Información del período en caja
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetFillColor(245, 245, 245);
        $this->pdf->Ln(3);
        
        $periodo = '';
        if ($desde && $desde != '') {
            $periodo .= 'Desde ' . date('d/m/Y', strtotime($desde));
        }
        if ($hasta && $hasta != '') {
            if ($periodo != '') $periodo .= ' ';
            $periodo .= 'Hasta ' . date('d/m/Y', strtotime($hasta));
        }
        if (!$desde && !$hasta) {
            $periodo = 'Todos los registros';
        }
        
        $this->pdf->Cell(95, 7, iconv('UTF-8', 'ISO-8859-1', 'Período: ' . $periodo), 1, 0, 'L', true);
        $this->pdf->Cell(95, 7, iconv('UTF-8', 'ISO-8859-1', 'Sucursal: ' . $nombreSucursal), 1, 1, 'L', true);
        $this->pdf->Cell(95, 7, iconv('UTF-8', 'ISO-8859-1', 'Generado: ' . date('d/m/Y H:i:s')), 1, 0, 'L', true);
        $this->pdf->Cell(95, 7, iconv('UTF-8', 'ISO-8859-1', 'Usuario: ' . (session('usuario')['usuario'] ?? 'Sistema')), 1, 1, 'L', true);
        $this->pdf->Ln(3);

        // Resumen General con mejor formato
        $this->pdf->SetFont('Arial', 'B', 13);
        $this->pdf->SetFillColor(52, 73, 94);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Cell(0, 9, iconv('UTF-8', 'ISO-8859-1', 'RESUMEN GENERAL'), 1, 1, 'C', true);
        
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetFillColor(255, 255, 255);
        
        // Tabla de resumen
        $anchoEtiqueta = 80;
        $anchoValor = 110;
        
        $this->pdf->Cell($anchoEtiqueta, 7, iconv('UTF-8', 'ISO-8859-1', 'Ingresos SINPE:'), 'LTR', 0, 'L', true);
        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Cell($anchoValor, 7, 'CRC ' . number_format($resumen['totalIngresosSinpeGeneral'] ?? 0, 2, '.', ','), 'LTR', 1, 'R', true);
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell($anchoEtiqueta, 7, iconv('UTF-8', 'ISO-8859-1', 'Ingresos Tarjeta:'), 'LR', 0, 'L', true);
        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Cell($anchoValor, 7, 'CRC ' . number_format($resumen['totalIngresosTarjetaGeneral'] ?? 0, 2, '.', ','), 'LR', 1, 'R', true);
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell($anchoEtiqueta, 7, iconv('UTF-8', 'ISO-8859-1', 'Ingresos Efectivo:'), 'LR', 0, 'L', true);
        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Cell($anchoValor, 7, 'CRC ' . number_format($resumen['totalIngresosEfectivoGeneral'] ?? 0, 2, '.', ','), 'LR', 1, 'R', true);
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell($anchoEtiqueta, 7, iconv('UTF-8', 'ISO-8859-1', 'SubTotal Fondos:'), 'LR', 0, 'L', true);
        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Cell($anchoValor, 7, 'CRC ' . number_format($resumen['subTotalFondosGeneral'] ?? 0, 2, '.', ','), 'LR', 1, 'R', true);
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->Cell($anchoEtiqueta, 7, iconv('UTF-8', 'ISO-8859-1', 'Total Gastos:'), 'LR', 0, 'L', true);
        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->SetTextColor(200, 0, 0);
        $this->pdf->Cell($anchoValor, 7, 'CRC ' . number_format($resumen['gastosGeneral'] ?? 0, 2, '.', ','), 'LR', 1, 'R', true);
        
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->SetFillColor(52, 152, 219);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Cell($anchoEtiqueta, 9, iconv('UTF-8', 'ISO-8859-1', 'TOTAL GENERAL FONDOS:'), 'LBR', 0, 'L', true);
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell($anchoValor, 9, 'CRC ' . number_format($resumen['totalFondosGeneral'] ?? 0, 2, '.', ','), 'LBR', 1, 'R', true);
        
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(2);

        // Desglose de Ingresos
        if (count($ingresos) > 0) {
            // Solo agregar nueva página si no hay espacio suficiente
            if ($this->pdf->GetY() > 240) {
                $this->pdf->AddPage();
            } else {
                $this->pdf->Ln(3);
            }
            $this->pdf->SetFont('Arial', 'B', 13);
            $this->pdf->SetFillColor(46, 125, 50);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->Cell(0, 9, iconv('UTF-8', 'ISO-8859-1', 'DESGLOSE DE INGRESOS (' . count($ingresos) . ' registros)'), 1, 1, 'C', true);
            $this->pdf->Ln(1);

            // Encabezado de tabla - Ajustado para A4 (190mm disponible)
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->SetFillColor(230, 230, 230);
            
            // Anchos ajustados: 190mm total
            $wFecha = 25;
            $wSucursal = 35;
            $wTipo = 30;
            $wEfectivo = 25;
            $wTarjeta = 25;
            $wSinpe = 25;
            $wTotal = 25;
            
            $this->pdf->Cell($wFecha, 7, iconv('UTF-8', 'ISO-8859-1', 'Fecha'), 1, 0, 'C', true);
            $this->pdf->Cell($wSucursal, 7, iconv('UTF-8', 'ISO-8859-1', 'Sucursal'), 1, 0, 'C', true);
            $this->pdf->Cell($wTipo, 7, iconv('UTF-8', 'ISO-8859-1', 'Tipo'), 1, 0, 'C', true);
            $this->pdf->Cell($wEfectivo, 7, iconv('UTF-8', 'ISO-8859-1', 'Efectivo'), 1, 0, 'C', true);
            $this->pdf->Cell($wTarjeta, 7, iconv('UTF-8', 'ISO-8859-1', 'Tarjeta'), 1, 0, 'C', true);
            $this->pdf->Cell($wSinpe, 7, iconv('UTF-8', 'ISO-8859-1', 'SINPE'), 1, 0, 'C', true);
            $this->pdf->Cell($wTotal, 7, iconv('UTF-8', 'ISO-8859-1', 'Total'), 1, 1, 'C', true);

            $this->pdf->SetFont('Arial', '', 8);
            $fill = false;
            foreach ($ingresos as $ingreso) {
                if ($this->pdf->GetY() > 270) {
                    $this->pdf->AddPage();
                    // Reimprimir encabezado
                    $this->pdf->SetFont('Arial', 'B', 9);
                    $this->pdf->SetFillColor(230, 230, 230);
                    $this->pdf->Cell($wFecha, 7, iconv('UTF-8', 'ISO-8859-1', 'Fecha'), 1, 0, 'C', true);
                    $this->pdf->Cell($wSucursal, 7, iconv('UTF-8', 'ISO-8859-1', 'Sucursal'), 1, 0, 'C', true);
                    $this->pdf->Cell($wTipo, 7, iconv('UTF-8', 'ISO-8859-1', 'Tipo'), 1, 0, 'C', true);
                    $this->pdf->Cell($wEfectivo, 7, iconv('UTF-8', 'ISO-8859-1', 'Efectivo'), 1, 0, 'C', true);
                    $this->pdf->Cell($wTarjeta, 7, iconv('UTF-8', 'ISO-8859-1', 'Tarjeta'), 1, 0, 'C', true);
                    $this->pdf->Cell($wSinpe, 7, iconv('UTF-8', 'ISO-8859-1', 'SINPE'), 1, 0, 'C', true);
                    $this->pdf->Cell($wTotal, 7, iconv('UTF-8', 'ISO-8859-1', 'Total'), 1, 1, 'C', true);
                    $this->pdf->SetFont('Arial', '', 8);
                    $fill = false;
                }

                // Formatear fecha correctamente (solo fecha, no hora completa)
                $fecha = date('d/m/Y', strtotime($ingreso->fecha));
                $sucursal = mb_substr($ingreso->nombreSucursal ?? 'N/A', 0, 18, 'UTF-8');
                $tipo = mb_substr($ingreso->nombre_tipo_ingreso ?? 'N/A', 0, 15, 'UTF-8');

                $fillColor = $fill ? 245 : 255;
                $this->pdf->SetFillColor($fillColor, $fillColor, $fillColor);
                
                $this->pdf->Cell($wFecha, 6, iconv('UTF-8', 'ISO-8859-1', $fecha), 1, 0, 'C', true);
                $this->pdf->Cell($wSucursal, 6, iconv('UTF-8', 'ISO-8859-1', $sucursal), 1, 0, 'L', true);
                $this->pdf->Cell($wTipo, 6, iconv('UTF-8', 'ISO-8859-1', $tipo), 1, 0, 'L', true);
                $this->pdf->Cell($wEfectivo, 6, number_format($ingreso->monto_efectivo ?? 0, 2, '.', ','), 1, 0, 'R', true);
                $this->pdf->Cell($wTarjeta, 6, number_format($ingreso->monto_tarjeta ?? 0, 2, '.', ','), 1, 0, 'R', true);
                $this->pdf->Cell($wSinpe, 6, number_format($ingreso->monto_sinpe ?? 0, 2, '.', ','), 1, 0, 'R', true);
                $this->pdf->Cell($wTotal, 6, number_format($ingreso->total ?? 0, 2, '.', ','), 1, 1, 'R', true);
                
                $fill = !$fill;
            }
            $this->pdf->Ln(2);
        }

        // Desglose de Gastos
        if (count($gastos) > 0) {
            // Solo agregar nueva página si no hay espacio suficiente
            if ($this->pdf->GetY() > 240) {
                $this->pdf->AddPage();
            } else {
                $this->pdf->Ln(3);
            }
            $this->pdf->SetFont('Arial', 'B', 13);
            $this->pdf->SetFillColor(192, 57, 43);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->Cell(0, 9, iconv('UTF-8', 'ISO-8859-1', 'DESGLOSE DE GASTOS (' . count($gastos) . ' registros)'), 1, 1, 'C', true);
            $this->pdf->Ln(1);

            // Encabezado de tabla - Ajustado para A4
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->SetFillColor(230, 230, 230);
            
            // Anchos ajustados: 190mm total
            $wFecha = 25;
            $wDescripcion = 60;
            $wTipoGasto = 35;
            $wProveedor = 40;
            $wMonto = 30;
            
            $this->pdf->Cell($wFecha, 7, iconv('UTF-8', 'ISO-8859-1', 'Fecha'), 1, 0, 'C', true);
            $this->pdf->Cell($wDescripcion, 7, iconv('UTF-8', 'ISO-8859-1', 'Descripción'), 1, 0, 'C', true);
            $this->pdf->Cell($wTipoGasto, 7, iconv('UTF-8', 'ISO-8859-1', 'Tipo Gasto'), 1, 0, 'C', true);
            $this->pdf->Cell($wProveedor, 7, iconv('UTF-8', 'ISO-8859-1', 'Proveedor'), 1, 0, 'C', true);
            $this->pdf->Cell($wMonto, 7, iconv('UTF-8', 'ISO-8859-1', 'Monto'), 1, 1, 'C', true);

            $this->pdf->SetFont('Arial', '', 8);
            $fill = false;
            foreach ($gastos as $gasto) {
                if ($this->pdf->GetY() > 270) {
                    $this->pdf->AddPage();
                    // Reimprimir encabezado
                    $this->pdf->SetFont('Arial', 'B', 9);
                    $this->pdf->SetFillColor(230, 230, 230);
                    $this->pdf->Cell($wFecha, 7, iconv('UTF-8', 'ISO-8859-1', 'Fecha'), 1, 0, 'C', true);
                    $this->pdf->Cell($wDescripcion, 7, iconv('UTF-8', 'ISO-8859-1', 'Descripción'), 1, 0, 'C', true);
                    $this->pdf->Cell($wTipoGasto, 7, iconv('UTF-8', 'ISO-8859-1', 'Tipo Gasto'), 1, 0, 'C', true);
                    $this->pdf->Cell($wProveedor, 7, iconv('UTF-8', 'ISO-8859-1', 'Proveedor'), 1, 0, 'C', true);
                    $this->pdf->Cell($wMonto, 7, iconv('UTF-8', 'ISO-8859-1', 'Monto'), 1, 1, 'C', true);
                    $this->pdf->SetFont('Arial', '', 8);
                    $fill = false;
                }

                // Formatear fecha correctamente
                $fecha = date('d/m/Y', strtotime($gasto->fecha));
                $descripcion = mb_substr($gasto->descripcion ?? 'N/A', 0, 35, 'UTF-8');
                $tipoGasto = mb_substr($gasto->nombre_tipo_gasto ?? 'N/A', 0, 20, 'UTF-8');
                $proveedor = mb_substr($gasto->nombreProveedor ?? 'N/A', 0, 22, 'UTF-8');

                $fillColor = $fill ? 245 : 255;
                $this->pdf->SetFillColor($fillColor, $fillColor, $fillColor);

                $this->pdf->Cell($wFecha, 6, iconv('UTF-8', 'ISO-8859-1', $fecha), 1, 0, 'C', true);
                $this->pdf->Cell($wDescripcion, 6, iconv('UTF-8', 'ISO-8859-1', $descripcion), 1, 0, 'L', true);
                $this->pdf->Cell($wTipoGasto, 6, iconv('UTF-8', 'ISO-8859-1', $tipoGasto), 1, 0, 'L', true);
                $this->pdf->Cell($wProveedor, 6, iconv('UTF-8', 'ISO-8859-1', $proveedor), 1, 0, 'L', true);
                $this->pdf->Cell($wMonto, 6, number_format($gasto->monto ?? 0, 2, '.', ','), 1, 1, 'R', true);
                
                $fill = !$fill;
            }
            $this->pdf->Ln(2);
        }

        // Facturas Electrónicas
        if (count($facturas) > 0) {
            // Solo agregar nueva página si no hay espacio suficiente
            if ($this->pdf->GetY() > 240) {
                $this->pdf->AddPage();
            } else {
                $this->pdf->Ln(3);
            }
            $this->pdf->SetFont('Arial', 'B', 13);
            $this->pdf->SetFillColor(142, 68, 173);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->Cell(0, 9, iconv('UTF-8', 'ISO-8859-1', 'FACTURAS ELECTRÓNICAS ENVIADAS (' . count($facturas) . ' registros)'), 1, 1, 'C', true);
            $this->pdf->Ln(1);

            // Encabezado de tabla - Ajustado para A4
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->SetFillColor(230, 230, 230);
            
            // Anchos ajustados: 190mm total
            $wFecha = 25;
            $wOrden = 35;
            $wCliente = 50;
            $wComprobante = 50;
            $wMonto = 30;
            
            $this->pdf->Cell($wFecha, 7, iconv('UTF-8', 'ISO-8859-1', 'Fecha'), 1, 0, 'C', true);
            $this->pdf->Cell($wOrden, 7, iconv('UTF-8', 'ISO-8859-1', 'No. Orden'), 1, 0, 'C', true);
            $this->pdf->Cell($wCliente, 7, iconv('UTF-8', 'ISO-8859-1', 'Cliente'), 1, 0, 'C', true);
            $this->pdf->Cell($wComprobante, 7, iconv('UTF-8', 'ISO-8859-1', 'Comprobante'), 1, 0, 'C', true);
            $this->pdf->Cell($wMonto, 7, iconv('UTF-8', 'ISO-8859-1', 'Monto'), 1, 1, 'C', true);

            $this->pdf->SetFont('Arial', '', 8);
            $totalFacturas = 0;
            $fill = false;
            foreach ($facturas as $factura) {
                if ($this->pdf->GetY() > 270) {
                    $this->pdf->AddPage();
                    // Reimprimir encabezado
                    $this->pdf->SetFont('Arial', 'B', 9);
                    $this->pdf->SetFillColor(230, 230, 230);
                    $this->pdf->Cell($wFecha, 7, iconv('UTF-8', 'ISO-8859-1', 'Fecha'), 1, 0, 'C', true);
                    $this->pdf->Cell($wOrden, 7, iconv('UTF-8', 'ISO-8859-1', 'No. Orden'), 1, 0, 'C', true);
                    $this->pdf->Cell($wCliente, 7, iconv('UTF-8', 'ISO-8859-1', 'Cliente'), 1, 0, 'C', true);
                    $this->pdf->Cell($wComprobante, 7, iconv('UTF-8', 'ISO-8859-1', 'Comprobante'), 1, 0, 'C', true);
                    $this->pdf->Cell($wMonto, 7, iconv('UTF-8', 'ISO-8859-1', 'Monto'), 1, 1, 'C', true);
                    $this->pdf->SetFont('Arial', '', 8);
                    $fill = false;
                }

                // Formatear fecha correctamente
                $fechaPago = $factura->fecha_pago ?? $factura->fecha_inicio ?? date('Y-m-d H:i:s');
                $fecha = date('d/m/Y', strtotime($fechaPago));
                $numeroOrden = mb_substr($factura->numero_orden ?? 'N/A', 0, 18, 'UTF-8');
                $cliente = mb_substr($factura->nombre ?? 'Sin cliente', 0, 28, 'UTF-8');
                $comprobante = mb_substr($factura->num_comprobante ?? 'N/A', 0, 28, 'UTF-8');
                $monto = $factura->monto_factura ?? 0;
                $totalFacturas += $monto;

                $fillColor = $fill ? 245 : 255;
                $this->pdf->SetFillColor($fillColor, $fillColor, $fillColor);

                $this->pdf->Cell($wFecha, 6, iconv('UTF-8', 'ISO-8859-1', $fecha), 1, 0, 'C', true);
                $this->pdf->Cell($wOrden, 6, iconv('UTF-8', 'ISO-8859-1', $numeroOrden), 1, 0, 'L', true);
                $this->pdf->Cell($wCliente, 6, iconv('UTF-8', 'ISO-8859-1', $cliente), 1, 0, 'L', true);
                $this->pdf->Cell($wComprobante, 6, iconv('UTF-8', 'ISO-8859-1', $comprobante), 1, 0, 'L', true);
                $this->pdf->Cell($wMonto, 6, number_format($monto, 2, '.', ','), 1, 1, 'R', true);
                
                $fill = !$fill;
            }

            // Total de facturas
            $this->pdf->SetFont('Arial', 'B', 10);
            $this->pdf->SetFillColor(142, 68, 173);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->Cell($wFecha + $wOrden + $wCliente + $wComprobante, 8, iconv('UTF-8', 'ISO-8859-1', 'Total Facturas Electrónicas:'), 1, 0, 'R', true);
            $this->pdf->SetFont('Arial', 'B', 11);
            $this->pdf->Cell($wMonto, 8, number_format($totalFacturas, 2, '.', ','), 1, 1, 'R', true);
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Ln(2);
        }

        // Pie de página en la última página
        $this->pdf->SetY(-12);
        $this->pdf->SetFont('Arial', 'I', 8);
        $this->pdf->SetTextColor(128, 128, 128);
        $this->pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1', 'Página ' . $this->pdf->PageNo() . ' - Generado por Space Software CR'), 0, 0, 'C');

        $nombreArchivo = 'resumen_contable_' . date('Y-m-d_His') . '.pdf';
        $this->pdf->Output($nombreArchivo, 'I');
        exit;
    }
}
