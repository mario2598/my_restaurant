<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\SpaceUtil;

class PosBarraController extends Controller
{
    use SpaceUtil;

    public function goPosBarra()
    {
        if (!$this->validarPermisos(['posBarra'])) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $sucursalFactura = MantenimientoSucursalController::getSucursalById($this->getUsuarioSucursal());
        $monedasFacturaPos = collect();
        try {
            $monedasFacturaPos = DB::table('sis_moneda')
                ->where('estado', '=', 'A')
                ->orderByRaw("CASE WHEN es_base = 'S' THEN 0 ELSE 1 END")
                ->orderBy('orden_visual')
                ->get(['id', 'cod_general', 'nombre', 'simbolo', 'decimales', 'es_base']);

            $ultimoTcPorMoneda = DB::table('sis_tipo_cambio')
                ->select('moneda_id', 'tipo_cambio')
                ->whereIn('id', function ($q) {
                    $q->select(DB::raw('MAX(id)'))->from('sis_tipo_cambio')->groupBy('moneda_id');
                })
                ->pluck('tipo_cambio', 'moneda_id');

            foreach ($monedasFacturaPos as $mf) {
                if (($mf->es_base ?? '') === 'S') {
                    $mf->tipo_cambio_vigente = 1.0;
                } else {
                    $raw = $ultimoTcPorMoneda[$mf->id] ?? null;
                    $mf->tipo_cambio_vigente = $raw !== null ? (float) $raw : null;
                }
            }
        } catch (\Throwable $e) {
            $monedasFacturaPos = collect();
        }

        $data = [
            'tipos' => (new FacturacionController())->getPosProductos(),
            'sucursalFacturaIva' => $sucursalFactura->factura_iva == 1,
            'mesas' => MesasController::getBySucursal($this->getUsuarioSucursal()),
            'cajaAbierta' => CajaController::tieneCajaAbierta(session('usuario')['id'], $this->getUsuarioSucursal()),
            'panel_configuraciones' => $this->getPanelConfiguraciones(),
            'monedasFacturaPos' => $monedasFacturaPos,
            'modo_barra' => true,
        ];

        return view('facturacion.posBarra', compact('data'));
    }

    public function listarCuentasAbiertas(Request $request)
    {
        if (!$this->validarPermisos(['posBarra', 'facFac'])) {
            return $this->responseAjaxServerError('No tienes permisos.', []);
        }

        try {
            $idCaja = CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal());
            if (!$idCaja) {
                return $this->responseAjaxSuccess('', []);
            }

            $cuentas = DB::table('cuenta_barra')
                ->leftJoin('mesa', 'mesa.id', '=', 'cuenta_barra.mesa')
                ->leftJoin('orden', 'orden.id', '=', 'cuenta_barra.orden_activa')
                ->where('cuenta_barra.cierre_caja', '=', $idCaja)
                ->where('cuenta_barra.estado', '=', 'A')
                ->select(
                    'cuenta_barra.*',
                    'mesa.numero_mesa',
                    'orden.numero_orden',
                    'orden.total',
                    'orden.pagado'
                )
                ->orderBy('cuenta_barra.fecha_apertura', 'DESC')
                ->get();

            return $this->responseAjaxSuccess('', $cuentas);
        } catch (\Exception $ex) {
            return $this->responseAjaxServerError('Error al listar cuentas: ' . $ex->getMessage(), []);
        }
    }

    public function abrirCuenta(Request $request)
    {
        if (!$this->validarPermisos(['posBarra', 'facFac'])) {
            return $this->responseAjaxServerError('No tienes permisos.', []);
        }

        $etiqueta = trim((string) $request->input('etiqueta', ''));
        $mesaId = $request->input('mesa');
        $mesaId = ($mesaId === null || $mesaId === '' || $mesaId == -1) ? null : (int) $mesaId;

        if ($etiqueta === '') {
            return $this->responseAjaxServerError('La etiqueta de la cuenta es obligatoria.', []);
        }

        $idCaja = CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal());
        if (!$idCaja) {
            return $this->responseAjaxServerError('Debe abrir caja antes de crear cuentas.', []);
        }

        try {
            $id = DB::table('cuenta_barra')->insertGetId([
                'sucursal' => $this->getUsuarioSucursal(),
                'cierre_caja' => $idCaja,
                'etiqueta' => $etiqueta,
                'mesa' => $mesaId,
                'orden_activa' => null,
                'estado' => 'A',
                'usuario' => session('usuario')['id'],
            ]);

            $cuenta = DB::table('cuenta_barra')
                ->leftJoin('mesa', 'mesa.id', '=', 'cuenta_barra.mesa')
                ->where('cuenta_barra.id', '=', $id)
                ->select('cuenta_barra.*', 'mesa.numero_mesa')
                ->first();

            return $this->responseAjaxSuccess('Cuenta abierta', $cuenta);
        } catch (\Exception $ex) {
            return $this->responseAjaxServerError('Error al abrir cuenta: ' . $ex->getMessage(), []);
        }
    }

    public function seleccionarCuenta(Request $request)
    {
        if (!$this->validarPermisos(['posBarra', 'facFac'])) {
            return $this->responseAjaxServerError('No tienes permisos.', []);
        }

        $id = (int) $request->input('id');
        if ($id < 1) {
            return $this->responseAjaxServerError('Cuenta inválida.', []);
        }

        $cuenta = DB::table('cuenta_barra')->where('id', '=', $id)->where('estado', '=', 'A')->first();
        if ($cuenta == null) {
            return $this->responseAjaxServerError('Cuenta no encontrada o cerrada.', []);
        }

        return $this->responseAjaxSuccess('', $cuenta);
    }

    public function cerrarCuenta(Request $request)
    {
        if (!$this->validarPermisos(['posBarra', 'facFac'])) {
            return $this->responseAjaxServerError('No tienes permisos.', []);
        }

        $id = (int) $request->input('id');
        if ($id < 1) {
            return $this->responseAjaxServerError('Cuenta inválida.', []);
        }

        try {
            DB::table('cuenta_barra')->where('id', '=', $id)->update([
                'estado' => 'C',
                'fecha_cierre' => date('Y-m-d H:i:s'),
            ]);
            return $this->responseAjaxSuccess('Cuenta cerrada', []);
        } catch (\Exception $ex) {
            return $this->responseAjaxServerError('Error al cerrar cuenta: ' . $ex->getMessage(), []);
        }
    }

    public static function cerrarCuentaPorOrden(int $idOrden): void
    {
        $orden = DB::table('orden')->where('id', '=', $idOrden)->first();
        if ($orden == null || empty($orden->cuenta_barra_id)) {
            return;
        }
        DB::table('cuenta_barra')
            ->where('id', '=', $orden->cuenta_barra_id)
            ->where('estado', '=', 'A')
            ->update([
                'estado' => 'C',
                'fecha_cierre' => date('Y-m-d H:i:s'),
            ]);
    }

    public static function vincularOrdenACuenta(int $idOrden, ?int $cuentaBarraId): void
    {
        if ($cuentaBarraId === null || $cuentaBarraId < 1) {
            return;
        }
        DB::table('cuenta_barra')
            ->where('id', '=', $cuentaBarraId)
            ->where('estado', '=', 'A')
            ->update(['orden_activa' => $idOrden]);
    }
}
