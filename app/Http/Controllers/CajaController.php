<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function index()
    {
    }

    public function goCierre()
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $total_gastos_caja = DB::table('gasto')
            ->where('usuario', '=', $this->getUsuarioAuth()['id'])
            ->where('caja_cerrada', '=', 'N')
            ->where('aprobado', 'like', 'N')
            ->sum('monto');

        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => [],
            'total_gastos_caja' => $total_gastos_caja,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('caja.cierre', compact('data'));
    }

    public function goCierreWithData($datos)
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $total_gastos_caja = DB::table('gasto')
            ->where('usuario', '=', $this->getUsuarioAuth()['id'])
            ->where('caja_cerrada', '=', 'N')
            ->where('aprobado', 'like', 'N')
            ->sum('monto');

        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'total_gastos_caja' => $total_gastos_caja,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('caja.cierre', compact('data'));
    }

    /**
     * Cierra la caja del usuario
     */
    public function cerrarCaja(Request $request)
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            return redirect('/');
        }

        $monto_efectivo = $request->input('monto_efectivo');
        $monto_tarjeta = $request->input('monto_tarjeta');
        $monto_sinpe = $request->input('monto_sinpe');
        $fondo = 0;
        $turno = $request->input('turno');
        $observacion = $request->input('observacion');
        $total = $request->input('total');
        $fecha_actual = date("Y-m-d H:i:s");
        $fecha = $request->input('fecha');
        $tipo_ingreso = $this->getTipoIngresoRolUsuario();
        $sucursal = $this->getSucursalUsuario();
        $idUsuario = session('usuario')['id'];
        $descripcion = "Cierre de caja " . "realizado por " . session('usuario')['usuario'] . ". Fecha : " . $fecha_actual;

        if ($monto_efectivo < 0 || $this->isNull($monto_efectivo)) {
            $monto_efectivo = 0;
        }
        if ($monto_tarjeta < 0 || $this->isNull($monto_tarjeta)) {
            $monto_tarjeta = 0;
        }
        if ($monto_sinpe < 0 || $this->isNull($monto_sinpe)) {
            $monto_sinpe = 0;
        }

        $usuAdmin = $this->usuarioAdministrador();
        if ($usuAdmin) {
            if ($fecha != null && $fecha != '') {
                $fecha_actual = $fecha;
            }
        }

        $caja_calculada = $this->calcularCajaUsuario($idUsuario);

        if (!$caja_calculada['estado']) {
            $this->setError("Validación de datos", "Error calculando caja.");
            return $this->goCierreWithData($request->all());
        }
        $caja_calculada = $caja_calculada['caja'];
        if ($tipo_ingreso == 2) { // Se supone que los ingresos 2 son de cafeteria -- Caso provicional para separar ingresos
            $tipo_cierre = 'D'; //D : Despacho
        } else {
            $tipo_cierre = 'O';  //O : Otro
        }
        $total = $caja_calculada['total_sinpe'] + $caja_calculada['total_tarjeta'] + $caja_calculada['total_efectivo'] + $caja_calculada['total_otros'];
        if (!$this->isIn($turno, array("M", "T"))) {
            $this->setError("Validación de datos", "Debe definir un turno válido.");
            return $this->goCierreWithData($request->all());
        }

        if (!$this->isLengthMinor($observacion, 150)) {
            $this->setError('Tamaño exedido', "La observación es de máximo 150 cáracteres.");
            return $this->goCierreWithData($request->all());
        }
        try {
            DB::beginTransaction();

            $idIngreso = DB::table('ingreso')->insertGetId([
                'id' => null, 'monto_efectivo' => $caja_calculada['total_efectivo'], 'monto_tarjeta' => $caja_calculada['total_tarjeta'], 'monto_sinpe' => $caja_calculada['total_sinpe'],
                'usuario' => $idUsuario, 'fecha' => $fecha_actual,
                'turno' => $turno, 'tipo' => $tipo_ingreso, 'observacion' => $observacion,
                'sucursal' => $sucursal, 'aprobado' => "N", 'cliente' => null, 'descripcion' => $descripcion, 'tipo_cierre' => $tipo_cierre
            ]);

            $idCierre = DB::table('cierre_caja')->insertGetId([
                'id' => null, 'fecha' => date("Y-m-d H:i:s"), 'fondo' => $fondo, 'monto_tarjeta' => $monto_tarjeta, 'monto_sinpe' => $monto_sinpe,
                'monto_efectivo' => $monto_efectivo, 'cajero' => $idUsuario,
                'ingreso' => $idIngreso
            ]);

            $pagosParciales = DB::table('pago_parcial_h')->where('usuario', '=', $idUsuario)
                ->where('estado', '=', 'PENDIENTE')->get();

            foreach ($pagosParciales as $pago) {
                DB::table('pago_parcial_h')
                    ->where('id', '=', $pago->id)
                    ->update(['estado' => "PROCESADO",'ingreso' => $idIngreso]);
            }

            DB::table('orden')
                ->where('cajero', '=', $idUsuario)
                ->where('caja_cerrada', '=', 'N')
                ->where('estado', '=', 'FC')
                ->update(['ingreso' => $idIngreso, 'caja_cerrada' => "S"]);

            DB::table('gasto')
                ->where('usuario', '=', $idUsuario)
                ->where('caja_cerrada', '=', "N")
                ->where('aprobado', '=', "N")
                ->update(['ingreso' => $idIngreso, 'caja_cerrada' => "S"]);



            $this->bitacoraMovimientos('ingreso', 'nuevo [Cierre Caja]', $idIngreso, $total, $fecha_actual);

            DB::commit();
            $this->setSuccess('Cierre de Caja', 'Se cerro la caja correctamente.');
            return redirect('/');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Cierre de Caja', 'Algo salío mal, reintentalo!');
            return $this->goCierreWithData($request->all());
        }
    }

    public static function calcularCajaUsuario($usuario_id)
    {
        $ordenes = DB::table('orden')->where('cajero', '=', $usuario_id)
            ->where('caja_cerrada', '=', 'N')
            ->where('estado', '=', 'FC')->get();

        $total_sinpe = 0;
        $total_efectivo = 0;
        $total_tarjeta = 0;
        $total_otros = 0;

        foreach ($ordenes as $o) {
            $total_sinpe = $total_sinpe + $o->monto_sinpe;
            $total_efectivo = $total_efectivo + $o->monto_efectivo;
            $total_tarjeta = $total_tarjeta + $o->monto_tarjeta;
            $total_otros  = $total_otros + $o->monto_otros;
        }

        $pagosParciales = DB::table('pago_parcial_h')->where('usuario', '=', $usuario_id)
            ->where('estado', '=', 'PENDIENTE')->get();

        foreach ($pagosParciales as $pago) {
            $total_sinpe = $total_sinpe + $pago->monto_sinpe;
            $total_efectivo = $total_efectivo + $pago->monto_efectivo;
            $total_tarjeta = $total_tarjeta + $pago->monto_tarjeta;
        }

        return [
            'estado' => true,
            'caja' => [
                'total_sinpe' => $total_sinpe,
                'total_efectivo' => $total_efectivo,
                'total_tarjeta' => $total_tarjeta,
                'total_otros' => $total_otros
            ]
        ];
    }
}
