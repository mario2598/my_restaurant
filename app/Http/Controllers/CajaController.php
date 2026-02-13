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

    public static function getIdsCajas($idSucursal)
    {
        $cierres = DB::table('cierre_caja')
            ->select('cierre_caja.id')
            ->where('sucursal', '=', $idSucursal)
            ->where('estado', '=', SisEstadoController::getIdEstadoByCodGeneral('CAJA_ABIERTO'))
            ->pluck('id')
            ->toArray();

        return $cierres;
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
        $efectivoReportado = $request->input('efectivoReportado');

        if ( $efectivoReportado < 0) {
            return $this->responseAjaxServerError("El campo efectivo reportado debe ser un número mayor o igual a 0.", []);
        }

        $caja_calculada = $this->calcularCajaUsuario($idCaja);

        if (!$caja_calculada['estado']) {
            return $this->responseAjaxServerError("Error calculando caja.", []);
        }

        $caja_calculada = $caja_calculada['caja'];

        $total = $caja_calculada['total_sinpe'] + $caja_calculada['total_tarjeta'] + $caja_calculada['total_efectivo'];

        try {
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
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error cerrando caja.", []);
        }
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
}
