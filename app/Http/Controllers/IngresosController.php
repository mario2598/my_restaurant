<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class IngresosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
        if (!$this->validarSesion("ingNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }


        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => [],
            'tipos_ingreso' => $this->getTiposIngreso(),
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.registrarIngresoAdmin', compact('data'));
    }

    public function goIngreso(Request $request)
    {
        if (!$this->validarSesion("ingTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idIngreso');

        $ingreso = DB::table('ingreso')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->select('ingreso.*', 'usuario.usuario as nombreUsuario', 'sucursal.descripcion as nombreSucursal')
            ->where('ingreso.id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError("No encontrado", "No se encontro el ingreso..");
            return redirect('ingresos/administracion');
        }

        $ventas = DB::table('orden')
            ->select('orden.*')
            ->where('orden.ingreso', '=', $id)->get();

        $tieneVentas = count($ventas) > 0;

        foreach ($ventas as $v) {
            $v->fecha_inicio = $this->fechaFormat($v->fecha_inicio);
            $v->fecha_preparado = $this->fechaFormat($v->fecha_preparado);
            $v->fecha_entregado = $this->fechaFormat($v->fecha_entregado);
            $v->detalles =  DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $v->id)->get();
        }

        $reporte_cajero = DB::table('cierre_caja')
            ->select('cierre_caja.*')
            ->where('cierre_caja.ingreso', '=', $id)->get()->first();

        $tieneReporteCajero = $reporte_cajero != null;
       
        $ingreso->fecha = $this->fechaFormat($ingreso->fecha);
    
        $sinpe = $ingreso->monto_sinpe ?? 0;
        $efectivo = $ingreso->monto_efectivo ?? 0;
        $tarjeta = $ingreso->monto_tarjeta ?? 0;
        $ingreso->subtotal = $sinpe + $efectivo + $tarjeta;
        $ingreso->totalGeneral = $ingreso->subtotal;
        $ingreso->monto_tarjeta  = preg_replace('/\,/', '.', $ingreso->monto_tarjeta);
        $ingreso->monto_efectivo  = preg_replace('/\,/', '.', $ingreso->monto_efectivo);
        $ingreso->monto_sinpe  = preg_replace('/\,/', '.', $ingreso->monto_sinpe);

        $data = [
            'menus' => $this->cargarMenus(),
            'ingreso' => $ingreso,
            'ventas' => $ventas,
            'tieneReporteCajero' => $tieneReporteCajero,
            'tieneVentas' => $tieneVentas,
            'reporte_cajero' => $reporte_cajero,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        //dd( $data );
        return view('ingresos.ingreso.ingreso', compact('data'));
    }

    public function goIngresoById($id)
    {
        if (!$this->validarSesion(array("ingTodos", "ingNue"))) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $ingreso = DB::table('ingreso')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->select('ingreso.*', 'usuario.usuario as nombreUsuario', 'sucursal.descripcion as nombreSucursal')
            ->where('ingreso.id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError("No encontrado", "No se encontro el ingreso..");
            return redirect('ingresos/administracion');
        }

        $ventasParciales = DB::table('pago_parcial_h')
        ->select('pago_parcial_h.*')
        ->where('pago_parcial_h.ingreso', '=',  $id )
        ->where('pago_parcial_h.estado', '=',  "PROCESADO" )->get();

        foreach ($ventasParciales as $v) {
            $v->ordenObj = DB::table('orden')
            ->select('orden.*')
            ->where('orden.id', '=', $v->orden)->get()->first();

            $v->cancelado = $v->monto_tarjeta + $v->monto_sinpe +$v->monto_efectivo ;
        }

        foreach ($ventasParciales as $v) {
            $v->ordenObj->fecha_inicio = $this->fechaFormat($v->ordenObj->fecha_inicio);
            $v->ordenObj->fecha_preparado = $this->fechaFormat($v->ordenObj->fecha_preparado);
            $v->ordenObj->fecha_entregado = $this->fechaFormat($v->ordenObj->fecha_entregado);
            $v->ordenObj->detalles =  DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $v->ordenObj->id)->get();
        }

        $gastosCaja = DB::table('gasto')
            ->leftJoin('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->select('gasto.*', 'proveedor.nombre as nombreProveedor')
            ->where('gasto.aprobado', '<>', "E")
            ->where('gasto.aprobado', '<>', "R")
            ->where('gasto.ingreso', '=', $id)->get();

        $ventas = DB::table('orden')
            ->select('orden.*')
            ->where('orden.caja_cerrada', '=', "S")
            ->where('orden.ingreso', '=', $id)->get();
        $tieneVentas = $ventas == null;
        $tieneVentas = count($ventas) > 0;

        if(!$tieneVentas){
            $tieneVentas = $ventasParciales == null;
            $tieneVentas = count($ventasParciales) > 0;
        }

        foreach ($ventas as $v) {
            $v->fecha_inicio = $this->fechaFormat($v->fecha_inicio);
            $v->fecha_preparado = $this->fechaFormat($v->fecha_preparado);
            $v->fecha_entregado = $this->fechaFormat($v->fecha_entregado);
            $v->detalles =  DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $v->id)->get();
        }

        $reporte_cajero = DB::table('cierre_caja')
            ->select('cierre_caja.*')
            ->where('cierre_caja.ingreso', '=', $id)->get()->first();

        $tieneReporteCajero = $reporte_cajero != null;
        $estadisticas = $this->totalIngresosMes($ingreso->fecha, $ingreso->tipo);

        $ingreso->fecha = $this->fechaFormat($ingreso->fecha);
        $totalGastos = 0;
        foreach ($gastosCaja as $i) {
            $i->fecha = $this->fechaFormat($i->fecha);
            $totalGastos = $totalGastos + $i->monto;
        }

        $ingreso->totalGastos = $totalGastos;
        $sinpe = $ingreso->monto_sinpe ?? 0;
        $efectivo = $ingreso->monto_efectivo ?? 0;
        $tarjeta = $ingreso->monto_tarjeta ?? 0;
        $ingreso->subtotal = $sinpe + $efectivo + $tarjeta;
        $ingreso->totalGeneral = $ingreso->subtotal - $ingreso->totalGastos;

        $data = [
            'menus' => $this->cargarMenus(),
            'ingreso' => $ingreso,
            'ventas' => $ventas,
            'tieneReporteCajero' => $tieneReporteCajero,
            'tieneVentas' => $tieneVentas,
            'ventasParciales' => $ventasParciales,
            'reporte_cajero' => $reporte_cajero,
            'estadisticas' => $estadisticas,
            'gastosCaja' => $gastosCaja,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        if (count($gastosCaja) > 0) {
            return view('ingresos.ingreso.ingresoConGastos', compact('data'));
        } else {
            return view('ingresos.ingreso.ingresoSinGastos', compact('data'));
        }
    }


    public function goIngresosAdmin()
    {
        if (!$this->validarSesion("ingTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'cliente' => 0,
            'sucursal' => 'T',
            'aprobado' => 'T',
            'hasta' => "",
            'tipo_ingreso' => "",
            'desde' => "",
        ];
        //  dd($filtros);
        $data = [
            'menus' => $this->cargarMenus(),
            'ingresos' => [],
            'filtros' => $filtros,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'clientes' => $this->getClientes(),
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.ingresosAdmin', compact('data'));
    }

    public function goIngresosAdminFiltro(Request $request)
    {
        if (!$this->validarSesion("ingTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroCliente = $request->input('cliente');
        $filtroSucursal = $request->input('sucursal');
        $filtroAprobado = $request->input('aprobado');
        $ingreso = $request->input('tipo_ingreso');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $ingresos =  DB::table('ingreso')
            ->leftjoin('cliente', 'cliente.id', '=', 'ingreso.cliente')
            ->leftjoin('tipo_ingreso', 'tipo_ingreso.id', '=', 'ingreso.tipo')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->select('ingreso.*', 'sucursal.descripcion as nombreSucursal', 'tipo_ingreso.tipo as nombre_tipo_ingreso', 'cliente.nombre', 'usuario.usuario as nombreUsuario')
            ->where('ingreso.aprobado', '<>', 'E');


        if ($filtroCliente >= 1  && !$this->isNull($filtroCliente)) {
            $ingresos = $ingresos->where('ingreso.cliente', '=', $filtroCliente);
        }

        if ($ingreso >= 1  && !$this->isNull($ingreso)) {
            $ingresos = $ingresos->where('ingreso.tipo', '=', $ingreso);
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $ingresos = $ingresos->where('ingreso.sucursal', 'like', '%' . $filtroSucursal . '%');
        }

        if ($this->isIn($filtroAprobado, array('S', 'N', 'R'))) {

            $ingresos = $ingresos->where('ingreso.aprobado', 'like', $filtroAprobado);
        }

        if (!$this->isNull($desde)) {
            $ingresos = $ingresos->where('ingreso.fecha', '>=', $desde);
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $ingresos = $ingresos->where('ingreso.fecha', '<', $mod_date);
        }

        $ingresos = $ingresos->get();
        $totalIngresos = 0;
        foreach ($ingresos as $i) {
            /*$gastos =  DB::table('gasto')->where('gasto.ingreso','=',$i->id)->where('gasto.aprobado','<>','E')->get();
            $totalEnGastos = 0;
            foreach($gastos as $g){
                $totalEnGastos = $totalEnGastos + $g->monto;
                
            }*/
            $sinpe = $i->monto_sinpe ?? 0;
            $efectivo = $i->monto_efectivo ?? 0;
            $tarjeta = $i->monto_tarjeta ?? 0;
            $i->total = $sinpe + $efectivo + $tarjeta;
            $totalIngresos = $totalIngresos + $i->total;
            $i->fecha = $this->fechaFormat($i->fecha);
        }
        // dd($ingresos);
        $filtros = [
            'cliente' => $filtroCliente,
            'sucursal' => $filtroSucursal,
            'aprobado' => $filtroAprobado,
            'tipo_ingreso' => $ingreso,
            'hasta' => $hasta,
            'desde' => $desde,
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'totalIngresos' => $totalIngresos,
            'ingresos' => $ingresos,
            'filtros' => $filtros,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'clientes' => $this->getClientes(),
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.ingresosAdmin', compact('data'));
    }

    public function goIngresosPendientes()
    {
        if (!$this->validarSesion("ingPendApr")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $ingresosSinAprobar =  DB::table('ingreso')
            ->join('tipo_ingreso', 'tipo_ingreso.id', '=', 'ingreso.tipo')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->select(
                'ingreso.id',
                'ingreso.fecha',
                'ingreso.monto_sinpe',
                'ingreso.monto_efectivo',
                'ingreso.monto_tarjeta',
                'ingreso.descripcion',
                'usuario.usuario as nombreUsuario',
                'tipo_ingreso.tipo as tipoIngreso'
            )
            ->where('aprobado', 'like', 'N')->orderby('ingreso.id', 'desc')->get();

        foreach ($ingresosSinAprobar as $i) {
            $sinpe = $i->monto_sinpe ?? 0;
            $efectivo = $i->monto_efectivo ?? 0;
            $tarjeta = $i->monto_tarjeta ?? 0;
            $i->subTotal = $sinpe + $efectivo + $tarjeta;
            $i->total = $i->subTotal ;
            $i->fecha = $this->fechaFormat($i->fecha);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'ingresosSinAprobar' => $ingresosSinAprobar,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.ingresosPendientes', compact('data'));
    }



    public function returnNuevoIngresoWithData($datos)
    {
        if (!$this->validarSesion("ingNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.registrarIngresoAdmin', compact('data'));
    }

    /*
     * 
     * Rechza un gasto de un ingreso
     */
    public function rechazarIngresoGasto(Request $request)
    {
        if (!$this->validarSesion("ingTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }


        $id = $request->input('idIngresoGastoRechazar');
        $ingreso = $request->input('idIngreso');

        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();

        if ($gasto == null) {
            $this->setError('Rechazar gasto', "El gasto no existe.");
            return $this->goIngresoById($ingreso);
        }

        if ($gasto->aprobado == 'R' && $gasto->aprobado == 'E') {
            $this->setError('Rechazar gasto', "El gasto ya fue rechazado.");
            return $this->goIngresoById($ingreso);
        }


        try {
            DB::beginTransaction();

            DB::table('gasto')
                ->where('id', '=', $id)->update(['aprobado' => 'R']); // Rechazado
            $this->bitacoraMovimientos('gasto', 'rechazar', $id, $gasto->monto);

            DB::commit();
            $this->setSuccess('Rechazar gasto', "El gasto se rechazo correctamente.");
            return $this->goIngresoById($ingreso);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Rechazar gasto', "Algo salío mal, reintentalo.");
            return $this->goIngresoById($ingreso);
        }
    }

    /**
     * Guarda o actualiza un ingreso
     */
    public function guardarIngreso(Request $request)
    {
        if (!$this->validarSesion("ingNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('id');

        if ($id < 1 || $this->isNull($id)) { // Nuevo ingreso
            $actualizar = false;
        } else { // editar ingreso
            $actualizar = true;
            $ingreso = DB::table('ingreso')->where('id', '=', $id)->get()->first();

            if ($ingreso == null) {
                $this->setError('Guardar Ingreso', 'El ingreso a editar no existe!');
                return redirect('/');
            }
        }
        if ($this->validarIngreso($request)) {

            $monto_efectivo = $request->input('monto_efectivo') ?? 0;
            $monto_sinpe = $request->input('monto_sinpe') ?? 0;
            $monto_tarjeta = $request->input('monto_tarjeta') ?? 0;
            $total = $monto_efectivo + $monto_sinpe + $monto_tarjeta;
            $idUsuario = $this->getUsuarioAuth()['id'];
            $observacion = $request->input('observacion');
            $tipo_ingreso = $request->input('tipo_ingreso');
            $sucursal = $this->getSucursalUsuario();
            $descripcion = $request->input('descripcion');
            $cliente = $request->input('cliente');
            $fecha = $request->input('fecha');
            $cliente = ($cliente == "null") ? null : $cliente;
            $fecha_actual = date("Y-m-d H:i:s");
            $aprobado = 'S';

            try {
                DB::beginTransaction();
                if ($actualizar) {
                    DB::table('ingreso')->where('id', '=', $id)->update([
                        'monto_efectivo' => $monto_efectivo,
                        'monto_tarjeta' => $monto_tarjeta, 'monto_sinpe' => $monto_sinpe, 'observacion' => $observacion,
                        'cliente' => $cliente
                    ]);
                    $this->bitacoraMovimientos('ingreso', 'editar', $id, $total);
                } else {
                    $idIngreso = DB::table('ingreso')->insertGetId([
                        'id' => null, 'monto_efectivo' => $monto_efectivo, 'monto_tarjeta' => $monto_tarjeta, 'monto_sinpe' => $monto_sinpe,
                        'usuario' => $idUsuario, 'fecha' => $fecha_actual,
                         'tipo' => $tipo_ingreso, 'observacion' => $observacion,
                        'sucursal' => $sucursal, 'aprobado' => $aprobado, 'cliente' => $cliente, 'descripcion' => $descripcion
                    ]);
                    $this->bitacoraMovimientos('ingreso', 'nuevo', $idIngreso, $total, $fecha_actual);
                }

                DB::commit();
                $this->setSuccess('Guardar Ingreso', 'Se guardo el ingreso correctamente.');
                if ($actualizar) {
                    return $this->goIngresoById($id);
                } else {
                    return $this->goIngresoById($idIngreso);
                }
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Ingreso', 'Algo salío mal, reintentalo!');
                if ($actualizar) {
                    return $this->goIngresoById($id);
                } else {
                    return $this->returnNuevoIngresoWithData($request->all());
                }
            }
        } else {
            if ($actualizar) {
                return $this->goIngresoById($id);
            } else {
                return $this->returnNuevoIngresoWithData($request->all());
            }
        }
    }

    /**
     * Elimina un gasto
     */
    public function eliminarIngreso(Request $request)
    {
        if (!$this->validarSesion("ingTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idIngresoEliminar');
        $ingreso = DB::table('ingreso')->where('id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError('Eliminar ingreso', "El ingreso no existe.");
            return redirect('/');
        }

        $sinpe = $ingreso->monto_sinpe ?? 0;
        $efectivo = $ingreso->monto_efectivo ?? 0;
        $tarjeta = $ingreso->monto_tarjeta ?? 0;
        $total = $sinpe + $efectivo + $tarjeta;


        try {
            DB::beginTransaction();

            DB::table('gasto')
                ->where('ingreso', '=', $id)->update(['aprobado' => 'E']);

            DB::table('ingreso')
                ->where('id', '=', $id)->update(['aprobado' => 'E']);

            $this->bitacoraMovimientos('ingreso', 'eliminar', $id, $total);

            DB::commit();
            $this->setSuccess('Eliminar ingreso', "El ingreso se elimino correctamente.");
            return redirect('ingresos/administracion');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar ingreso', "Algo salío mal, reintentalo.");
            return redirect('ingresos/administracion');
        }
    }

    /**
     * Rechazar un gasto
     */
    public function rechazarIngreso(Request $request)
    {
        if (!$this->validarSesion("ingTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idIngresoRechazar');
        $ingreso = DB::table('ingreso')->where('id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError('Rechazar ingreso', "El ingreso no existe.");
            return redirect('/');
        }

        $sinpe = $ingreso->monto_sinpe ?? 0;
        $efectivo = $ingreso->monto_efectivo ?? 0;
        $tarjeta = $ingreso->monto_tarjeta ?? 0;
        $total = $sinpe + $efectivo + $tarjeta;


        try {
            DB::beginTransaction();

            DB::table('gasto')
                ->where('ingreso', '=', $id)->update(['aprobado' => 'R']);

            DB::table('ingreso')
                ->where('id', '=', $id)->update(['aprobado' => 'R']);

            $this->bitacoraMovimientos('ingreso', 'rechazar', $id, $total);

            DB::commit();
            $this->setSuccess('Rechazar ingreso', "El ingreso se rechazo correctamente.");
            return redirect('ingresos/administracion');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Rechazar ingreso', "Algo salío mal, reintentalo.");
            return redirect('ingresos/administracion');
        }
    }


    /**
     * aprobar un gasto
     */
    public function aprobarIngreso(Request $request)
    {
        if (!$this->validarSesion("ingTodos")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $id = $request->input('idIngreso');
        $ingreso = DB::table('ingreso')->where('id', '=', $id)->get()->first();

        if ($ingreso == null) {
            return $this->responseAjaxServerError("El ingreso no existe.", []);
        }

        $sinpe = $request->input('pago_sinpe');
        $efectivo = $request->input('pago_efectivo');
        $tarjeta = $request->input('pago_tarjeta');
        $total = $sinpe + $efectivo + $tarjeta;


        try {
            DB::beginTransaction();

          

            DB::table('ingreso')
                ->where('id', '=', $id)->update(['aprobado' => 'S','monto_tarjeta' =>  $tarjeta,'monto_sinpe' => $sinpe,'monto_efectivo' =>  $efectivo]);

            $this->bitacoraMovimientos('ingreso', 'Aprobar', $id, $total);

            DB::commit();
            return $this->responseAjaxSuccess("El ingreso se aprobo correctamente.",[]);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal, reintentalo.", []);
        }
    }


    public function validarIngreso(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
        $monto_efectivo = $r->input('monto_efectivo') ?? 0;
        $monto_sinpe = $r->input('monto_sinpe') ?? 0;
        $monto_tarjeta = $r->input('monto_tarjeta') ?? 0;
        $total = $monto_efectivo + $monto_sinpe + $monto_tarjeta;

        if ($this->isNull($r->input('descripcion')) || $this->isEmpty($r->input('descripcion'))) {
            $requeridos .= " Descripción del ingreso ";
            $valido = false;
            $esPrimero = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        if ($this->isNull($r->input('tipo_ingreso'))) {
            $this->setError('Error de integridad', "Tipo de ingreso invalido.");
            return false;
        }

        if (!$this->isLengthMinor($r->input('descripcion'), 300)) {
            $this->setError('Tamaño exedido', "La descripción del gasto debe ser de máximo 150 caracteres.");
            return false;
        }
        if (!$this->isLengthMinor($r->input('observacion'), 150)) {
            $this->setError('Tamaño exedido', "La observación debe ser de máximo 150 caracteres.");
            return false;
        }
        if ($total < 10) {
            $this->setError('Número incorrecto', "El total debe ser mayor que 10.00 CRC.");
            return false;
        }

        return $valido;
    }
}
