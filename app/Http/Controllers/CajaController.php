<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class CajaController extends Controller
{

    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "cajCerrar";
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index() {}

    public static function getById($idCaja)
    {
        return DB::table('cierre_caja')
            ->select('cierre_caja.*')
            ->where('id', '=', $idCaja)
            ->get()->first();
    }

    public static function getByIdIngreso($idIngreso)
    {
        return DB::table('cierre_caja')
            ->select('cierre_caja.*')
            ->where('ingreso', '=', $idIngreso)
            ->get()->first();
    }

    public function goCierre()
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'datos' => [],
            'total_gastos_caja' => [],
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('caja.cierre', compact('data'));
    }

    public function goCierreWithData($datos)
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $total_gastos_caja = DB::table('gasto')
            ->where('usuario', '=', $this->getUsuarioAuth()['id'])
            ->where('caja_cerrada', '=', 'N')
            ->where('aprobado', 'like', 'N')
            ->sum('monto');

        $data = [
            'datos' => $datos,
            'total_gastos_caja' => $total_gastos_caja,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('caja.cierre', compact('data'));
    }

    public static function tieneCajaAbierta($idUsuario, $idSucursal)
    {
        $cierre = DB::table('cierre_caja')
            ->select('cierre_caja.*')
            ->where('cajero', '=', $idUsuario)
            ->where('sucursal', '=', $idSucursal)
            ->where('estado', '=', SisEstadoController::getIdEstadoByCodGeneral('CAJA_ABIERTO'))
            ->get()->first();

        return $cierre != null;
    }

    public static function getIdCaja($idUsuario, $idSucursal)
    {
        $cierre = DB::table('cierre_caja')
            ->select('cierre_caja.id')
            ->where('cajero', '=', $idUsuario)
            ->where('sucursal', '=', $idSucursal)
            ->where('estado', '=', SisEstadoController::getIdEstadoByCodGeneral('CAJA_ABIERTO'))
            ->get()->first()->id ?? null;

        return $cierre;
    }

    public static function getIdsCajas( $idSucursal)
    {
        $cierre = DB::table('cierre_caja')
            ->select('cierre_caja.id')
            ->where('sucursal', '=', $idSucursal)
            ->where('estado', '=', SisEstadoController::getIdEstadoByCodGeneral('CAJA_ABIERTO'))
            ->get()->first()->id ?? null;

        return $cierre;
    }

    public function abrirCaja(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }
        try {
            $tieneCajaAbierta = CajaController::tieneCajaAbierta(session('usuario')['id'], $this->getUsuarioSucursal());

            if ($tieneCajaAbierta) {
                return $this->responseAjaxServerError("Ya tiene un cierre de caja abierto.", []);
            }

            $fondoInicio = SisParametroController::getValorByCodGeneral('MTO_FONDO_INI_CAJA');

            $idCierre = DB::table('cierre_caja')->insertGetId([
                'id' => null,
                'fecha' => date("Y-m-d H:i:s"),
                'fondo' => $fondoInicio,
                'monto_tarjeta' => 0,
                'monto_sinpe' => 0,
                'efectivo_reportado' => 0,
                'monto_efectivo' => 0,
                'cajero' => session('usuario')['id'],
                'ingreso' => null,
                'estado' => SisEstadoController::getIdEstadoByCodGeneral('CAJA_ABIERTO'),
                'sucursal' => $this->getUsuarioSucursal()
            ]);

            return $this->responseAjaxSuccess("Se abrio la caja.", $idCierre);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }
    /**
     * Cierra la caja del usuario
     */
    public function cerrarCaja(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $tieneCajaAbierta = CajaController::tieneCajaAbierta(session('usuario')['id'], $this->getUsuarioSucursal());

        if (!$tieneCajaAbierta) {
            return $this->responseAjaxServerError("No tiene un cierre de caja abierto.", []);
        }

        $idCaja = CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal());
        if ($idCaja == null) {
            return $this->responseAjaxServerError("No tiene un cierre de caja abierto.", []);
        }

        $ordenSinPagar = DB::table('orden')
            ->where('cierre_caja', '=', $idCaja)
            ->where('pagado', '=', 0)
            ->where('estado', '<>', SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA'))
            ->exists();

        if ($ordenSinPagar) {
            return $this->responseAjaxServerError("Tiene ordenes con el proceso de pago pendiente.", []);
        }

        $fecha_actual = date("Y-m-d H:i:s");
        $sucursal = $this->getSucursalUsuario();
        $idUsuario = session('usuario')['id'];
        $descripcion = "Cierre de caja " . "realizado por " . session('usuario')['usuario'] . ". Fecha : " . $fecha_actual;

        $parseEfectivo = $this->parseEfectivoReportadoCierre($request);
        if (! $parseEfectivo['ok']) {
            return $this->responseAjaxServerError($parseEfectivo['mensaje'], []);
        }
        $efectivoReportado = $parseEfectivo['total_base'];

        $caja_calculada = $this->calcularCajaUsuario($idCaja);

        if (!$caja_calculada['estado']) {
            return $this->responseAjaxServerError("Error calculando caja.", []);
        }

        $caja_calculada = $caja_calculada['caja'];

        $total = $caja_calculada['total_sinpe'] + $caja_calculada['total_tarjeta'] + $caja_calculada['total_efectivo'];

       
            DB::beginTransaction();

            $idIngreso = DB::table('ingreso')->insertGetId([
                'id' => null,
                'monto_efectivo' => $caja_calculada['total_efectivo'],
                'monto_tarjeta' => $caja_calculada['total_tarjeta'],
                'monto_sinpe' => $caja_calculada['total_sinpe'],
                'usuario' => $idUsuario,
                'fecha' => $fecha_actual,
                'tipo' => MantenimientoTiposIngresoController::getIdByCodGeneral('ING_CIERRE_CAJA'),
                'observacion' => $descripcion,
                'sucursal' => $sucursal,
                'estado' => SisEstadoController::getIdEstadoByCodGeneral("ING_PEND_APB"),
                'cliente' => null,
                'descripcion' => $descripcion
            ]);
            $this->registrarIngresoPagoCierre(
                $idIngreso,
                (int) $idCaja
            );

            DB::table('cierre_caja')
                ->where('id', '=', $idCaja)
                ->update([
                    'monto_efectivo' => $caja_calculada['total_efectivo'],
                    'monto_tarjeta' => $caja_calculada['total_tarjeta'],
                    'monto_sinpe' => $caja_calculada['total_sinpe'],
                    'ingreso' => $idIngreso,
                    'fecha_cierra' => $fecha_actual,
                    'efectivo_reportado' => $efectivoReportado,
                    'estado' => SisEstadoController::getIdEstadoByCodGeneral('CAJA_FINALIZADO')
                ]);

            DB::table('orden')
                ->where('cierre_caja', '=', $idCaja)
                ->update(['ingreso' => $idIngreso, 'caja_cerrada' => "S"]);


            $this->bitacoraMovimientos('ingreso', 'Nuevo [Cierre Caja]', $idIngreso, $total, $fecha_actual);

            DB::commit();
            return $this->responseAjaxSuccess("Se cerro la caja correctamente.", null);
     
    }

    public function getCajaPrevia(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }
        $idCaja = CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal());
        if ($idCaja == null) {
            return $this->responseAjaxServerError("No tiene un cierre de caja abierto.", []);
        }

        $caja_calculada = $this->calcularCajaUsuario($idCaja);

        if (!$caja_calculada['estado']) {
            return $this->responseAjaxServerError("Error calculando caja.", []);
        }
        return $this->responseAjaxSuccess("", $caja_calculada['caja']);
    }
    public static function calcularCajaUsuario($idCaja)
    {
        try {
            $ordenes = DB::table('orden')->where('cierre_caja', '=', $idCaja)
                ->where('estado', '<>', SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA'))->get();

            $total_sinpe = 0;
            $total_efectivo = 0;
            $total_tarjeta = 0;

            foreach ($ordenes as $o) {
                $total_sinpe = $total_sinpe + $o->monto_sinpe;
                $total_efectivo = $total_efectivo + $o->monto_efectivo;
                $total_tarjeta = $total_tarjeta + $o->monto_tarjeta;
            }

            return [
                'estado' => true,
                'caja' => [
                    'total_sinpe' => $total_sinpe,
                    'total_efectivo' => $total_efectivo,
                    'total_tarjeta' => $total_tarjeta
                ]
            ];
        } catch (QueryException $ex) {
            return [
                'estado' => false,
                'caja' => []
            ];
        }
    }

    /**
     * Efectivo reportado al cierre: JSON multimoneda (monto por moneda × TC = base) o un solo monto en base.
     *
     * @return array{ok: bool, mensaje: string, total_base: float, json_guardar: ?string}
     */
    private function parseEfectivoReportadoCierre(Request $request): array
    {
        $jsonRaw = trim((string) $request->input('efectivo_por_moneda_json', ''));
        if ($jsonRaw !== '') {
            $dec = json_decode($jsonRaw, true);
            if (! is_array($dec) || count($dec) === 0) {
                return ['ok' => false, 'mensaje' => 'Datos de efectivo por moneda inválidos.', 'total_base' => 0.0, 'json_guardar' => null];
            }
            $sumBase = 0.0;
            $normalized = [];
            foreach ($dec as $r) {
                if (! is_array($r)) {
                    return ['ok' => false, 'mensaje' => 'Formato de efectivo por moneda inválido.', 'total_base' => 0.0, 'json_guardar' => null];
                }
                $mid = (int) ($r['moneda_id'] ?? 0);
                $mm = round((float) ($r['monto_moneda'] ?? 0), 4);
                $tc = round((float) ($r['tipo_cambio_snapshot'] ?? 0), 6);
                $medio = strtoupper((string) ($r['medio_pago'] ?? 'EFECTIVO'));
                if ($medio !== 'EFECTIVO' || $mid < 1) {
                    return ['ok' => false, 'mensaje' => 'Cada línea debe ser efectivo con moneda válida.', 'total_base' => 0.0, 'json_guardar' => null];
                }
                if ($mm < 0) {
                    return ['ok' => false, 'mensaje' => 'Los montos deben ser mayores o iguales a 0.', 'total_base' => 0.0, 'json_guardar' => null];
                }
                if ($mm > 0 && $tc <= 0) {
                    return ['ok' => false, 'mensaje' => 'Falta tipo de cambio para una moneda con monto indicado.', 'total_base' => 0.0, 'json_guardar' => null];
                }
                try {
                    if (! DB::table('sis_moneda')->where('id', '=', $mid)->where('estado', '=', 'A')->exists()) {
                        return ['ok' => false, 'mensaje' => 'Moneda no válida o inactiva.', 'total_base' => 0.0, 'json_guardar' => null];
                    }
                } catch (\Throwable $e) {
                    return ['ok' => false, 'mensaje' => 'Error al validar moneda.', 'total_base' => 0.0, 'json_guardar' => null];
                }
                $sumBase += $mm * $tc;
                if ($mm > 0) {
                    $normalized[] = [
                        'medio_pago' => 'EFECTIVO',
                        'moneda_id' => $mid,
                        'monto_moneda' => $mm,
                        'tipo_cambio_snapshot' => $tc,
                    ];
                }
            }
            if (count($normalized) === 0) {
                return ['ok' => false, 'mensaje' => 'Indique al menos un monto de efectivo contado en alguna moneda.', 'total_base' => 0.0, 'json_guardar' => null];
            }

            return [
                'ok' => true,
                'mensaje' => '',
                'total_base' => round($sumBase, 4),
                'json_guardar' => json_encode($normalized, JSON_UNESCAPED_UNICODE),
            ];
        }

        $efectivoReportado = (float) $request->input('efectivoReportado', 0);
        if ($efectivoReportado < 0) {
            return ['ok' => false, 'mensaje' => 'El efectivo reportado debe ser mayor o igual a 0.', 'total_base' => 0.0, 'json_guardar' => null];
        }

        return ['ok' => true, 'mensaje' => '', 'total_base' => $efectivoReportado, 'json_guardar' => null];
    }

    /**
     * Genera líneas de ingreso_pago para un cierre de caja usando pagos reales de la caja.
     * Los montos base salen de pago_orden (monto_efectivo/tarjeta/sinpe) y
     * si hay moneda/TC de documento se guarda también el monto en moneda del pago.
     */
    private function registrarIngresoPagoCierre(int $idIngreso, int $idCaja): void
    {
        try {
            $base = DB::table('sis_moneda')
                ->where('estado', '=', 'A')
                ->where('es_base', '=', 'S')
                ->orderBy('id')
                ->first(['id']);

            if (! $base || empty($base->id)) {
                return;
            }

            $pagos = DB::table('pago_orden')
                ->join('orden', 'orden.id', '=', 'pago_orden.orden')
                ->where('orden.cierre_caja', '=', $idCaja)
                ->where('orden.estado', '<>', SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA'))
                ->select(
                    'pago_orden.moneda_factura_id',
                    'pago_orden.tipo_cambio_snapshot',
                    'pago_orden.monto_efectivo',
                    'pago_orden.monto_tarjeta',
                    'pago_orden.monto_sinpe'
                )
                ->get();

            $lineas = [];
            foreach ($pagos as $p) {
                $midPago = (int) ($p->moneda_factura_id ?? 0);
                $tcRaw = (float) ($p->tipo_cambio_snapshot ?? 0);
                $tc = ($midPago > 0 && $tcRaw > 0) ? round($tcRaw, 6) : 1.000000;
                $mid = ($midPago > 0 && $tcRaw > 0) ? $midPago : (int) $base->id;

                $agregarLinea = function (string $medio, float $montoBase) use (&$lineas, $idIngreso, $mid, $tc): void {
                    if ($montoBase <= 0) {
                        return;
                    }
                    $mBase = round($montoBase, 4);
                    $mMon = $tc > 0 ? round($mBase / $tc, 4) : $mBase;
                    $lineas[] = [
                        'ingreso' => $idIngreso,
                        'medio_pago' => $medio,
                        'moneda_id' => $mid,
                        'monto_moneda' => $mMon,
                        'tipo_cambio_snapshot' => $tc,
                        'monto_base' => $mBase,
                    ];
                };

                $agregarLinea('EFECTIVO', (float) ($p->monto_efectivo ?? 0));
                $agregarLinea('TARJETA', (float) ($p->monto_tarjeta ?? 0));
                $agregarLinea('SINPE', (float) ($p->monto_sinpe ?? 0));
            }

            if (! empty($lineas)) {
                DB::table('ingreso_pago')->where('ingreso', '=', $idIngreso)->delete();

                $agrupadas = [];
                foreach ($lineas as $ln) {
                    $key = $ln['medio_pago'] . '|' . $ln['moneda_id'] . '|' . $ln['tipo_cambio_snapshot'];
                    if (! isset($agrupadas[$key])) {
                        $agrupadas[$key] = [
                            'ingreso' => $idIngreso,
                            'medio_pago' => $ln['medio_pago'],
                            'moneda_id' => $ln['moneda_id'],
                            'tipo_cambio_snapshot' => $ln['tipo_cambio_snapshot'],
                            'monto_moneda' => 0.0,
                            'monto_base' => 0.0,
                        ];
                    }
                    $agrupadas[$key]['monto_moneda'] += (float) $ln['monto_moneda'];
                    $agrupadas[$key]['monto_base'] += (float) $ln['monto_base'];
                }
                $rowsInsert = [];
                foreach ($agrupadas as $g) {
                    $rowsInsert[] = [
                        'ingreso' => $g['ingreso'],
                        'medio_pago' => $g['medio_pago'],
                        'moneda_id' => $g['moneda_id'],
                        'tipo_cambio_snapshot' => round((float) $g['tipo_cambio_snapshot'], 6),
                        'monto_moneda' => round((float) $g['monto_moneda'], 4),
                        'monto_base' => round((float) $g['monto_base'], 4),
                    ];
                }
                if (! empty($rowsInsert)) {
                    DB::table('ingreso_pago')->insert($rowsInsert);
                }
            }
        } catch (\Throwable $e) {
            // Si no existe la tabla o hay error de esquema, no interrumpir el cierre de caja.
        }
    }
}
