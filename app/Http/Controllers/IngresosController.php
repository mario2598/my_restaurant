<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Illuminate\Support\Facades\Validator;

class IngresosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
        $data = [
            'datos' => [],
            'tipos_ingreso' => $this->getTiposIngreso(),
            'monedas' => $this->listarMonedasActivas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.registrarIngresoAdmin', compact('data'));
    }

    public function goIngreso(Request $request)
    {

        $id = $request->input('idIngreso');

        $ingreso = DB::table('ingreso')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'usuario.usuario as nombreUsuario', 'sucursal.descripcion as nombreSucursal', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('ingreso.id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError("No encontrado", "No se encontro el ingreso..");
            return redirect('ingresos/administracion');
        }

        
        $caja = CajaController::getByIdIngreso($id);

        if ($caja == null) {
            $efectivoReportado = null;
        }else{
            $efectivoReportado = $caja->efectivo_reportado ?? 0;
        }

        $ventas = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('orden.ingreso', '=', $id)->get();

        $tieneVentas = count($ventas) > 0;

        foreach ($ventas as $v) {
            $v->fecha_inicio = $this->fechaFormat($v->fecha_inicio);
            $v->fecha_preparado = $this->fechaFormat($v->fecha_preparado);
            $v->fecha_entregado = $this->fechaFormat($v->fecha_entregado);
            $v->detalles =  DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $v->id)->get();
            $v->incidentes = DB::table('det_incidente_orden')
                ->leftJoin('usuario', 'usuario.id', '=', 'det_incidente_orden.usuario')
                ->where('det_incidente_orden.orden', '=', $v->id)
                ->orderBy('det_incidente_orden.fecha', 'DESC')
                ->select('det_incidente_orden.*', 'usuario.nombre as usuario_nombre', 'usuario.usuario as usuario_login')
                ->get();
            $v->tiene_incidentes = $v->incidentes->count() > 0;
        }
        $this->adjuntarInfoMonedaEnVentas($ventas);

        $ingreso->fecha = $this->fechaFormat($ingreso->fecha);

        $ingreso->monto_tarjeta = preg_replace('/\,/', '.', $ingreso->monto_tarjeta);
        $ingreso->monto_efectivo = preg_replace('/\,/', '.', $ingreso->monto_efectivo);
        $ingreso->monto_sinpe = preg_replace('/\,/', '.', $ingreso->monto_sinpe);

        $mRes = $this->ingresoMontosResumenContable($ingreso->id, $ingreso);
        $ingreso->subtotal = $mRes['total'];
        $ingreso->totalGeneral = $mRes['total'];

        $ingresoPagosDetalle = $this->listarIngresoPagosDetalle($ingreso->id);
        $ingresoPagosJsonPrefill = $ingresoPagosDetalle->isEmpty()
            ? ''
            : json_encode($ingresoPagosDetalle->map(function ($p) {
                return [
                    'medio_pago' => $p->medio_pago,
                    'moneda_id' => (int) $p->moneda_id,
                    'monto_moneda' => (float) $p->monto_moneda,
                    'tipo_cambio_snapshot' => (float) $p->tipo_cambio_snapshot,
                ];
            })->values()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $data = [
            'ingreso' => $ingreso,
            'ventas' => $ventas,
            'tieneVentas' => $tieneVentas,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'efectivoReportado' => $efectivoReportado,
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST"),
            'ingreso_pagos_detalle' => $ingresoPagosDetalle,
            'ingreso_pagos_json_prefill' => $ingresoPagosJsonPrefill,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.ingreso.ingreso', compact('data'));
    }

    public function goIngresoById($id)
    {

        $ingreso = DB::table('ingreso')
            ->join('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'usuario.usuario as nombreUsuario', 'sucursal.descripcion as nombreSucursal', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('ingreso.id', '=', $id)->get()->first();

        if ($ingreso == null) {
            $this->setError("No encontrado", "No se encontro el ingreso..");
            return redirect('ingresos/administracion');
        }

        
        $caja = CajaController::getByIdIngreso($id);

        if ($caja == null) {
            $efectivoReportado = null;
        }else{
            $efectivoReportado = $caja->efectivo_reportado ?? 0;
        }

        $ventas = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general')
            ->where('orden.ingreso', '=', $id)->get();

        $tieneVentas = count($ventas) > 0;

        foreach ($ventas as $v) {
            $v->fecha_inicio = $this->fechaFormat($v->fecha_inicio);
            $v->fecha_preparado = $this->fechaFormat($v->fecha_preparado);
            $v->fecha_entregado = $this->fechaFormat($v->fecha_entregado);
            $v->detalles =  DB::table('detalle_orden')
                ->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $v->id)->get();
            $v->incidentes = DB::table('det_incidente_orden')
                ->leftJoin('usuario', 'usuario.id', '=', 'det_incidente_orden.usuario')
                ->where('det_incidente_orden.orden', '=', $v->id)
                ->orderBy('det_incidente_orden.fecha', 'DESC')
                ->select('det_incidente_orden.*', 'usuario.nombre as usuario_nombre', 'usuario.usuario as usuario_login')
                ->get();
            $v->tiene_incidentes = $v->incidentes->count() > 0;
        }
        $this->adjuntarInfoMonedaEnVentas($ventas);

        $ingreso->fecha = $this->fechaFormat($ingreso->fecha);

        $ingreso->monto_tarjeta = preg_replace('/\,/', '.', $ingreso->monto_tarjeta);
        $ingreso->monto_efectivo = preg_replace('/\,/', '.', $ingreso->monto_efectivo);
        $ingreso->monto_sinpe = preg_replace('/\,/', '.', $ingreso->monto_sinpe);

        $mRes = $this->ingresoMontosResumenContable($ingreso->id, $ingreso);
        $ingreso->subtotal = $mRes['total'];
        $ingreso->totalGeneral = $mRes['total'];

        $ingresoPagosDetalle = $this->listarIngresoPagosDetalle($ingreso->id);
        $ingresoPagosJsonPrefill = $ingresoPagosDetalle->isEmpty()
            ? ''
            : json_encode($ingresoPagosDetalle->map(function ($p) {
                return [
                    'medio_pago' => $p->medio_pago,
                    'moneda_id' => (int) $p->moneda_id,
                    'monto_moneda' => (float) $p->monto_moneda,
                    'tipo_cambio_snapshot' => (float) $p->tipo_cambio_snapshot,
                ];
            })->values()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $data = [
            'ingreso' => $ingreso,
            'ventas' => $ventas,
            'tieneVentas' => $tieneVentas,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'efectivoReportado' => $efectivoReportado,
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST"),
            'ingreso_pagos_detalle' => $ingresoPagosDetalle,
            'ingreso_pagos_json_prefill' => $ingresoPagosJsonPrefill,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('ingresos.ingreso.ingreso', compact('data'));
    }


    public function goIngresosAdmin()
    {

        $filtros = [
            'sucursal' => 'T',
            'aprobado' => 'T',
            'hasta' => "",
            'tipo_ingreso' => "",
            'desde' => "",
        ];

        if (session("filtrosIngresos") == null) {
            session(['filtrosIngresos' =>  $filtros]);
        } else {
            $filtros = session("filtrosIngresos");
            return $this->goIngresosAdminFiltro(new Request());
        }


        $data = [
            'ingresos' => [],
            'filtros' => $filtros,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'tipos_ingreso' => $this->getTiposIngreso(),
            'sucursales' => $this->getSucursales(),
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST"),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.ingresosAdmin', compact('data'));
    }

    public function goIngresosAdminFiltro(Request $request)
    {
        // Verificar si hay parámetros en el request, si no, usar sesión
        if ($request->has('sucursal') || $request->has('aprobado') || $request->has('tipo_ingreso') || $request->has('desde') || $request->has('hasta')) {
            $filtroSucursal = $request->input('sucursal');
            $filtroAprobado = $request->input('aprobado');
            $ingreso = $request->input('tipo_ingreso');
            $hasta = $request->input('hasta');
            $desde = $request->input('desde');
        } else {
            $filtros = session("filtrosIngresos");
            if ($filtros) {
                $filtroSucursal = $filtros['sucursal'] ?? null;
                $filtroAprobado = $filtros['aprobado'] ?? null;
                $ingreso = $filtros['tipo_ingreso'] ?? null;
                $hasta = $filtros['hasta'] ?? null;
                $desde = $filtros['desde'] ?? null;
            } else {
                $filtroSucursal = null;
                $filtroAprobado = null;
                $ingreso = null;
                $hasta = null;
                $desde = null;
            }
        }


        $ingresos =  DB::table('ingreso')
            ->leftjoin('tipo_ingreso', 'tipo_ingreso.id', '=', 'ingreso.tipo')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'ingreso.sucursal')
            ->leftjoin('usuario', 'usuario.id', '=', 'ingreso.usuario')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'sucursal.descripcion as nombreSucursal', 'tipo_ingreso.tipo as nombre_tipo_ingreso', 'usuario.usuario as nombreUsuario', 'sis_estado.nombre as dscEstado', 'sis_estado.cod_general as cod_general');

        if (!$this->isNull($ingreso) && $ingreso != '' && $ingreso != 'T' && is_numeric($ingreso) && $ingreso >= 1) {
            $ingresos = $ingresos->where('ingreso.tipo', '=', $ingreso);
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T' && $filtroSucursal != '') {
            $ingresos = $ingresos->where('ingreso.sucursal', '=', $filtroSucursal);
        }

        if (!$this->isNull($filtroAprobado) && $filtroAprobado != 'T' && $filtroAprobado != '') {
            $ingresos = $ingresos->where('ingreso.estado', '=', $filtroAprobado);
        }

        if (!$this->isNull($desde) && $desde != '') {
            $ingresos = $ingresos->where('ingreso.fecha', '>=', $desde);
        }

        if (!$this->isNull($hasta) && $hasta != '') {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $ingresos = $ingresos->where('ingreso.fecha', '<', $mod_date);
        }

        $ingresos = $ingresos->get();
        $totalIngresos = 0;
        $totalRechazados = 0;
        foreach ($ingresos as $i) {

            $m = $this->ingresoMontosResumenContable($i->id, $i);
            $i->total = $m['total'];
            $totalIngresos = $totalIngresos + $i->total;
            
            // Calcular total de rechazados y eliminados
            if ($i->cod_general == 'ING_EST_RECHAZADO' || $i->cod_general == 'ING_EST_ELIMINADO') {
                $totalRechazados = $totalRechazados + $i->total;
            }
            
            $i->fecha = $this->fechaFormat($i->fecha);
        }

        $filtros1 = [
            'sucursal' => $filtroSucursal,
            'aprobado' => $filtroAprobado,
            'tipo_ingreso' => $ingreso,
            'hasta' => $hasta,
            'desde' => $desde,
        ];

        session(['filtrosIngresos' =>  $filtros1]);
        $data = [
            'totalIngresos' => $totalIngresos,
            'totalRechazados' => $totalRechazados,
            'ingresos' => $ingresos,
            'filtros' => $filtros1,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'sucursales' => $this->getSucursales(),
            'estados_ingreso' => SisEstadoController::getEstadosByCodClase("INGRESOS_EST"),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.ingresosAdmin', compact('data'));
    }

    public function goIngresosPendientes()
    {

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
            ->where('ingreso.estado', '=', SisEstadoController::getIdEstadoByCodGeneral("ING_PEND_APB"))->orderby('ingreso.id', 'desc')->get();

        foreach ($ingresosSinAprobar as $i) {
            $caja = CajaController::getByIdIngreso($i->id);
            $m = $this->ingresoMontosResumenContable($i->id, $i);
            $detallePagos = $this->listarIngresoPagosDetalle($i->id);
            $i->tiene_detalle_multimoneda = ! $detallePagos->isEmpty();
            $i->detalle_pagos = $detallePagos;
            $i->detalle_pagos_resumen = $detallePagos->map(function ($p) {
                $medio = strtoupper((string) ($p->medio_pago ?? ''));
                $medioTxt = $medio === 'TARJETA' ? 'Tarjeta' : ($medio === 'SINPE' ? 'SINPE' : 'Efectivo');
                $mon = trim((string) (($p->moneda_simbolo ?? '') . ' ' . ($p->moneda_cod ?? '')));
                $mMon = number_format((float) ($p->monto_moneda ?? 0), 2, '.', ',');
                $mBase = number_format((float) ($p->monto_base ?? 0), 2, '.', ',');

                return $medioTxt . ': ' . $mon . ' ' . $mMon . ' (CRC ' . $mBase . ')';
            })->values()->all();
            if ($caja != null) {
                $efectivo = $caja->efectivo_reportado ?? 0;
                $sinpe = $m['sinpe'];
                $tarjeta = $m['tarjeta'];
                $i->subTotal = $sinpe + $efectivo + $tarjeta;
            } else {
                $i->subTotal = $m['total'];
            }
            $i->total = $m['total'];
            $i->fecha = $this->fechaFormat($i->fecha);
        }

        $data = [
            'ingresosSinAprobar' => $ingresosSinAprobar,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.ingresosPendientes', compact('data'));
    }



    public function returnNuevoIngresoWithData($datos)
    {

        $data = [
            'datos' => $datos,
            'tipos_ingreso' => $this->getTiposIngreso(),
            'monedas' => $this->listarMonedasActivas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('ingresos.registrarIngresoAdmin', compact('data'));
    }



    /**
     * Guarda o actualiza un ingreso
     */
    public function guardarIngreso(Request $request)
    {

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
            $estado = SisEstadoController::getIdEstadoByCodGeneral("ING_EST_APROBADO");

            try {
                DB::beginTransaction();
                if ($actualizar) {
                    DB::table('ingreso')->where('id', '=', $id)->update([
                        'monto_efectivo' => $monto_efectivo,
                        'monto_tarjeta' => $monto_tarjeta,
                        'monto_sinpe' => $monto_sinpe,
                        'observacion' => $observacion
                    ]);
                    $this->aplicarIngresoPagosJson($id, $request->input('ingreso_pagos_json'));
                    $totalBit = $this->ingresoMontosResumenContable($id, DB::table('ingreso')->where('id', $id)->first())['total'];
                    $this->bitacoraMovimientos('ingreso', 'editar', $id, $totalBit);
                } else {
                    $idIngreso = DB::table('ingreso')->insertGetId([
                        'id' => null,
                        'monto_efectivo' => $monto_efectivo,
                        'monto_tarjeta' => $monto_tarjeta,
                        'monto_sinpe' => $monto_sinpe,
                        'usuario' => $idUsuario,
                        'fecha' => $fecha_actual,
                        'tipo' => $tipo_ingreso,
                        'observacion' => $observacion,
                        'sucursal' => $sucursal,
                        'estado' => $estado,
                        'descripcion' => $descripcion
                    ]);
                    $this->aplicarIngresoPagosJson($idIngreso, $request->input('ingreso_pagos_json'));
                    $totalBit = $this->ingresoMontosResumenContable($idIngreso, DB::table('ingreso')->where('id', $idIngreso)->first())['total'];
                    $this->bitacoraMovimientos('ingreso', 'nuevo', $idIngreso, $totalBit, $fecha_actual);
                }

                DB::commit();
                $this->setSuccess('Guardar Ingreso', 'Se guardo el ingreso correctamente.');
                if ($actualizar) {
                    return $this->goIngresoById($id);
                } else {
                    return $this->goIngresoById($idIngreso);
                }
            } catch (\InvalidArgumentException $ex) {
                DB::rollBack();
                $this->setError('Guardar Ingreso', $ex->getMessage());
                if ($actualizar) {
                    return $this->goIngresoById($id);
                } else {
                    return $this->returnNuevoIngresoWithData($request->all());
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

    public function guardarIngresoArr(
        $monto_efectivo,
        $monto_sinpe,
        $monto_tarjeta,
        $observacion,
        $tipo_ingreso,
        $descripcion,
        $cliente = null,
        $fecha = null,
        $idSucursal = null,
        $doc_referencia = null
    ) {
        // Validación manual (puedes adaptar este código según tus necesidades de validación)
        if ($this->validarIngresoArr(compact('monto_efectivo', 'monto_sinpe', 'monto_tarjeta', 'observacion', 'tipo_ingreso', 'descripcion', 'cliente', 'fecha'))) {

            $total = $monto_efectivo + $monto_sinpe + $monto_tarjeta;
            $idUsuario = $this->getUsuarioAuth()['id'];
            $fecha_actual = $fecha ?? date("Y-m-d H:i:s");
            $estado = SisEstadoController::getIdEstadoByCodGeneral("ING_EST_APROBADO");

            try {

                // Crear el nuevo ingreso
                $idIngreso = DB::table('ingreso')->insertGetId([
                    'monto_efectivo' => $monto_efectivo ?? 0,
                    'monto_tarjeta' => $monto_tarjeta ?? 0,
                    'monto_sinpe' => $monto_sinpe ?? 0,
                    'usuario' => $idUsuario,
                    'fecha' => $fecha_actual,
                    'tipo' => $tipo_ingreso,
                    'observacion' => $observacion,
                    'sucursal' => $idSucursal,
                    'estado' => $estado,
                    'descripcion' => $descripcion,
                    'cliente' => $cliente,
                    'doc_referencia' => $doc_referencia
                ]);

                // Registrar el movimiento en la bitácora
                $this->bitacoraMovimientos('ingreso', 'nuevo', $idIngreso, $total, $fecha_actual);

                return response()->json([
                    'estado' => true,
                    'mensaje' => 'Ingreso guardado correctamente.',
                    'datos' => $idIngreso
                ], 200);
            } catch (QueryException $ex) {
                DB::rollBack();
                DB::table('log')->insertGetId(['id' => null, 'documento' => 'IngresosController', 'descripcion' => $ex]);
                return response()->json([
                    'estado' => false,
                    'mensaje' => 'Algo salió mal, reinténtalo.',
                    'error' => $ex->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Validación fallida.',
                'errores' => $this->validarIngresoArr(compact('monto_efectivo', 'monto_sinpe', 'monto_tarjeta', 'observacion', 'tipo_ingreso', 'descripcion', 'cliente', 'fecha'))
            ], 422);
        }
    }

    public function validarIngresoArr(array $data)
    {
        // Realiza la validación usando el array $data
        $rules = [
            'monto_efectivo' => 'required|numeric|min:0',
            'monto_sinpe' => 'required|numeric|min:0',
            'monto_tarjeta' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
            'tipo_ingreso' => 'required|string',
            'descripcion' => 'nullable|string',
            'cliente' => 'nullable|integer',
            'fecha' => 'nullable|date',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return null; // Null si la validación fue exitosa
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

        $jsonPagos = trim((string) ($r->input('ingreso_pagos_json') ?? ''));
        if ($jsonPagos !== '' && ! $this->ingresoPagosJsonEsValido($jsonPagos)) {
            $this->setError('Pagos multimoneda', 'El JSON de pagos en otras monedas es inválido. Revise medio_pago (EFECTIVO|TARJETA|SINPE), moneda_id, monto_moneda y tipo_cambio_snapshot.');

            return false;
        }
        $totalDesdeJson = $this->totalMontoBaseDesdeIngresoPagosJson($jsonPagos);
        if ($total < 10 && $totalDesdeJson < 10) {
            $this->setError('Número incorrecto', 'El total debe ser mayor que 10.00 (CRC en medios clásicos o equivalente en base vía JSON de pagos).');

            return false;
        }

        return $valido;
    }

    public function aprobarIngreso(Request $request)
    {
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
                ->where('id', '=', $id)->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("ING_EST_APROBADO"),'monto_tarjeta' =>  $tarjeta,'monto_sinpe' => $sinpe,'monto_efectivo' =>  $efectivo]);

            $this->bitacoraMovimientos('ingreso', 'Aprobar', $id, $total);

            DB::commit();
            $this->setSuccess("Aprobando ingreso", "Se aprobó el ingreso correctamente");
            return $this->responseAjaxSuccess("El ingreso se aprobo correctamente.",[]);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal, reintentalo.", []);
        }
    }

    public function rechazarIngreso(Request $request)
    {
        if (!$this->validarSesion("ingNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idIngresoRechazar');
        
        if ($id == null || $id < 1) {
            $this->setError('Rechazar Ingreso', 'Identificador inválido.');
            return redirect('ingresos/administracion');
        }

        $ingreso = DB::table('ingreso')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'sis_estado.cod_general as cod_general')
            ->where('ingreso.id', '=', $id)
            ->get()->first();

        if ($ingreso == null) {
            $this->setError('Rechazar Ingreso', 'No existe el ingreso a rechazar.');
            return redirect('ingresos/administracion');
        }

        // Verificar que el ingreso esté pendiente de aprobación
        if ($ingreso->cod_general != 'ING_PEND_APB') {
            $this->setError('Rechazar Ingreso', 'Solo se pueden rechazar ingresos pendientes de aprobación.');
            return redirect('ingresos/administracion');
        }

        try {
            DB::beginTransaction();

            // Calcular el total del ingreso
            $sinpe = $ingreso->monto_sinpe ?? 0;
            $efectivo = $ingreso->monto_efectivo ?? 0;
            $tarjeta = $ingreso->monto_tarjeta ?? 0;
            $total = $sinpe + $efectivo + $tarjeta;

            // Actualizar el estado del ingreso a rechazado
            DB::table('ingreso')
                ->where('id', '=', $id)
                ->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("ING_EST_RECHAZADO")]);

            // Registrar en bitácora
            $this->bitacoraMovimientos('ingreso', 'Rechazar', $id, $total);

            DB::commit();
            $this->setSuccess('Rechazar Ingreso', 'El ingreso se rechazó correctamente.');
            return redirect('ingresos/administracion');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Rechazar Ingreso', 'Ocurrió un error rechazando el ingreso.');
            return redirect('ingresos/administracion');
        } catch (\Exception $ex) {
            DB::rollBack();
            $this->setError('Rechazar Ingreso', 'Error inesperado al rechazar el ingreso.');
            return redirect('ingresos/administracion');
        }
    }

    public function eliminarIngreso(Request $request)
    {
        if (!$this->validarSesion("ingNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idIngresoEliminar');
        
        if ($id == null || $id < 1) {
            $this->setError('Eliminar Ingreso', 'Identificador inválido.');
            return redirect('ingresos/administracion');
        }

        $ingreso = DB::table('ingreso')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'ingreso.estado')
            ->select('ingreso.*', 'sis_estado.cod_general as cod_general')
            ->where('ingreso.id', '=', $id)
            ->get()->first();

        if ($ingreso == null) {
            $this->setError('Eliminar Ingreso', 'No existe el ingreso a eliminar.');
            return redirect('ingresos/administracion');
        }

        // Verificar que el ingreso esté aprobado
        if ($ingreso->cod_general != 'ING_EST_APROBADO') {
            $this->setError('Eliminar Ingreso', 'Solo se pueden eliminar ingresos aprobados.');
            return redirect('ingresos/administracion');
        }

        try {
            DB::beginTransaction();

            // Calcular el total del ingreso
            $sinpe = $ingreso->monto_sinpe ?? 0;
            $efectivo = $ingreso->monto_efectivo ?? 0;
            $tarjeta = $ingreso->monto_tarjeta ?? 0;
            $total = $sinpe + $efectivo + $tarjeta;

            // Actualizar el estado del ingreso a eliminado
            DB::table('ingreso')
                ->where('id', '=', $id)
                ->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("ING_EST_ELIMINADO")]);

            // Registrar en bitácora
            $this->bitacoraMovimientos('ingreso', 'Eliminar', $id, $total);

            DB::commit();
            $this->setSuccess('Eliminar Ingreso', 'El ingreso se eliminó correctamente.');
            return redirect('ingresos/administracion');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Ingreso', 'Ocurrió un error eliminando el ingreso.');
            return redirect('ingresos/administracion');
        } catch (\Exception $ex) {
            DB::rollBack();
            $this->setError('Eliminar Ingreso', 'Error inesperado al eliminar el ingreso.');
            return redirect('ingresos/administracion');
        }
    }

    private function listarMonedasActivas()
    {
        try {
            return DB::table('sis_moneda')->where('estado', '=', 'A')->orderBy('orden_visual')->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Agrega a cada venta:
     * - es_multimoneda (bool)
     * - moneda_label (string)
     * - monedas_lista (array<string>)
     */
    private function adjuntarInfoMonedaEnVentas($ventas): void
    {
        if (empty($ventas) || count($ventas) === 0) {
            return;
        }
        $baseCod = 'CRC';
        try {
            $mBase = DB::table('sis_moneda')
                ->where('estado', '=', 'A')
                ->where('es_base', '=', 'S')
                ->orderBy('id')
                ->first(['cod_general']);
            if (! empty($mBase->cod_general)) {
                $baseCod = (string) $mBase->cod_general;
            }
        } catch (\Throwable $e) {
        }

        $ids = [];
        foreach ($ventas as $v) {
            $ids[] = (int) ($v->id ?? 0);
        }
        $ids = array_values(array_filter(array_unique($ids), function ($n) {
            return $n > 0;
        }));
        if (count($ids) === 0) {
            return;
        }

        $porOrden = [];
        try {
            $pagos = DB::table('pago_orden')
                ->leftJoin('sis_moneda', 'sis_moneda.id', '=', 'pago_orden.moneda_factura_id')
                ->whereIn('pago_orden.orden', $ids)
                ->select('pago_orden.orden', 'sis_moneda.cod_general as moneda_cod')
                ->get();

            foreach ($pagos as $p) {
                $idOrden = (int) ($p->orden ?? 0);
                if ($idOrden <= 0) {
                    continue;
                }
                $cod = trim((string) ($p->moneda_cod ?? ''));
                if ($cod === '') {
                    $cod = $baseCod;
                }
                if (! isset($porOrden[$idOrden])) {
                    $porOrden[$idOrden] = [];
                }
                $porOrden[$idOrden][$cod] = true;
            }
        } catch (\Throwable $e) {
        }

        foreach ($ventas as $v) {
            $idOrden = (int) ($v->id ?? 0);
            $cods = isset($porOrden[$idOrden]) ? array_keys($porOrden[$idOrden]) : [$baseCod];
            sort($cods);
            $v->monedas_lista = $cods;
            $v->es_multimoneda = count($cods) > 1;
            $v->moneda_label = $v->es_multimoneda ? ('Multimoneda (' . implode('/', $cods) . ')') : ($cods[0] ?? $baseCod);
        }
    }

    private function listarIngresoPagosDetalle($ingresoId)
    {
        try {
            return DB::table('ingreso_pago')
                ->join('sis_moneda', 'sis_moneda.id', '=', 'ingreso_pago.moneda_id')
                ->where('ingreso_pago.ingreso', '=', $ingresoId)
                ->select(
                    'ingreso_pago.*',
                    'sis_moneda.cod_general as moneda_cod',
                    'sis_moneda.simbolo as moneda_simbolo',
                    'sis_moneda.nombre as moneda_nombre'
                )
                ->orderBy('ingreso_pago.id')
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function totalMontoBaseDesdeIngresoPagosJson(?string $json): float
    {
        $json = trim((string) $json);
        if ($json === '') {
            return 0;
        }
        $rows = json_decode($json, true);
        if (! is_array($rows)) {
            return 0;
        }
        $sum = 0;
        foreach ($rows as $r) {
            $mm = (float) ($r['monto_moneda'] ?? 0);
            $tc = (float) ($r['tipo_cambio_snapshot'] ?? 0);
            if ($mm > 0 && $tc > 0) {
                $sum += $mm * $tc;
            }
        }

        return round($sum, 4);
    }

    private function ingresoPagosJsonEsValido(string $json): bool
    {
        $rows = json_decode($json, true);
        if (! is_array($rows) || count($rows) === 0) {
            return false;
        }
        foreach ($rows as $r) {
            $medio = strtoupper((string) ($r['medio_pago'] ?? ''));
            if (! in_array($medio, ['EFECTIVO', 'TARJETA', 'SINPE'], true)) {
                return false;
            }
            $mid = (int) ($r['moneda_id'] ?? 0);
            $mm = (float) ($r['monto_moneda'] ?? 0);
            $tc = (float) ($r['tipo_cambio_snapshot'] ?? 0);
            if ($mid < 1 || $mm <= 0 || $tc <= 0) {
                return false;
            }
            try {
                $ok = DB::table('sis_moneda')->where('id', '=', $mid)->where('estado', '=', 'A')->exists();
                if (! $ok) {
                    return false;
                }
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  int|string  $ingresoId
     */
    private function aplicarIngresoPagosJson($ingresoId, ?string $jsonRaw): void
    {
        $jsonRaw = trim((string) ($jsonRaw ?? ''));
        DB::table('ingreso_pago')->where('ingreso', '=', $ingresoId)->delete();
        if ($jsonRaw === '') {
            return;
        }
        if (! $this->ingresoPagosJsonEsValido($jsonRaw)) {
            throw new \InvalidArgumentException('JSON de pagos multimoneda inválido.');
        }
        $rows = json_decode($jsonRaw, true);
        $ef = 0;
        $tj = 0;
        $sn = 0;
        foreach ($rows as $r) {
            $medio = strtoupper((string) ($r['medio_pago'] ?? ''));
            $mid = (int) ($r['moneda_id'] ?? 0);
            $mm = round((float) ($r['monto_moneda'] ?? 0), 4);
            $tc = round((float) ($r['tipo_cambio_snapshot'] ?? 0), 6);
            $base = round($mm * $tc, 4);
            DB::table('ingreso_pago')->insert([
                'ingreso' => $ingresoId,
                'medio_pago' => $medio,
                'moneda_id' => $mid,
                'monto_moneda' => $mm,
                'tipo_cambio_snapshot' => $tc,
                'monto_base' => $base,
            ]);
            if ($medio === 'EFECTIVO') {
                $ef += $base;
            } elseif ($medio === 'TARJETA') {
                $tj += $base;
            } else {
                $sn += $base;
            }
        }
        DB::table('ingreso')->where('id', '=', $ingresoId)->update([
            'monto_efectivo' => round($ef, 4),
            'monto_tarjeta' => round($tj, 4),
            'monto_sinpe' => round($sn, 4),
        ]);
    }
}

