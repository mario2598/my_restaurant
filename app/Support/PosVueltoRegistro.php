<?php

namespace App\Support;

use App\Http\Controllers\CajaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosVueltoRegistro
{
    /**
     * @return array<string,mixed>|null null si no aplica registro
     */
    public static function datosDesdeRequest(Request $request): ?array
    {
        $raw = $request->input('vuelto_registro');
        if (! is_array($raw) || empty($raw['moneda_cobro_id'])) {
            return null;
        }
        $mid = (int) $raw['moneda_cobro_id'];
        $tc = (float) ($raw['tipo_cambio_snapshot'] ?? 0);
        if ($mid <= 0 || $tc <= 0) {
            return null;
        }

        $entregadoEnBase = filter_var(
            $raw['vuelto_entregado_moneda_base'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
        if (! $entregadoEnBase) {
            return null;
        }

        $recibido = round((float) ($raw['monto_recibido_doc'] ?? 0), 4);
        $totalDoc = round((float) ($raw['total_pagar_doc'] ?? 0), 4);
        $vueltoDoc = round((float) ($raw['vuelto_moneda_doc'] ?? 0), 4);
        $vueltoBase = round((float) ($raw['vuelto_moneda_base'] ?? 0), 4);

        if ($recibido <= 0 || $vueltoBase <= 0) {
            return null;
        }

        $retenido = round(max(0, $recibido - $vueltoDoc), 4);

        return [
            'moneda_cobro_id' => $mid,
            'tipo_cambio_snapshot' => round($tc, 6),
            'total_pagar_doc' => $totalDoc,
            'monto_recibido_doc' => $recibido,
            'vuelto_moneda_doc' => max(0, $vueltoDoc),
            'vuelto_moneda_base' => max(0, $vueltoBase),
            'monto_retenido_doc' => $retenido,
        ];
    }

    public static function insertar(
        Request $request,
        ?int $pagoOrdenId,
        ?int $ordenId,
        ?string $numeroOrden,
        int $usuarioId,
        int $sucursalId
    ): void {
        $datos = self::datosDesdeRequest($request);
        if ($datos === null) {
            return;
        }

        $idCaja = CajaController::getIdCaja($usuarioId, $sucursalId);
        if (! $idCaja) {
            return;
        }

        try {
            if (! DB::getSchemaBuilder()->hasTable('pos_vuelto_registro')) {
                return;
            }
            DB::table('pos_vuelto_registro')->insert([
                'cierre_caja' => $idCaja,
                'pago_orden' => $pagoOrdenId,
                'orden' => $ordenId,
                'numero_orden' => $numeroOrden,
                'moneda_cobro_id' => $datos['moneda_cobro_id'],
                'tipo_cambio_snapshot' => $datos['tipo_cambio_snapshot'],
                'total_pagar_doc' => $datos['total_pagar_doc'],
                'monto_recibido_doc' => $datos['monto_recibido_doc'],
                'vuelto_moneda_doc' => $datos['vuelto_moneda_doc'],
                'vuelto_moneda_base' => $datos['vuelto_moneda_base'],
                'monto_retenido_doc' => $datos['monto_retenido_doc'],
                'usuario' => $usuarioId,
                'sucursal' => $sucursalId,
                'fecha_hora' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // No bloquear el cobro si falla el registro auxiliar
        }
    }

    /**
     * @return array{filas: array<int,object>, totales: array<string,float>, monedas: array<int,object>}
     */
    public static function vacioListado(): array
    {
        return [
            'filas' => [],
            'totales' => [
                'vuelto_moneda_doc' => 0,
                'vuelto_moneda_base' => 0,
                'monto_retenido_doc' => 0,
                'monto_recibido_doc' => 0,
            ],
            'monedas' => [],
        ];
    }

    /**
     * Vueltos en colones registrados en el cierre vinculado a un ingreso (p. ej. cierre de caja).
     *
     * @return array{filas: array<int,object>, totales: array<string,float>, monedas: array<int,object>}
     */
    public static function listarPorIdIngreso(int $idIngreso): array
    {
        if ($idIngreso <= 0) {
            return self::vacioListado();
        }
        $caja = CajaController::getByIdIngreso($idIngreso);

        return $caja ? self::listarPorCierreCaja((int) $caja->id) : self::vacioListado();
    }

    /**
     * @return array{filas: array<int,object>, totales: array<string,float>, monedas: array<int,object>}
     */
    public static function listarPorCierreCaja(int $idCierre): array
    {
        if ($idCierre <= 0) {
            return self::vacioListado();
        }

        try {
            if (! DB::getSchemaBuilder()->hasTable('pos_vuelto_registro')) {
                return self::vacioListado();
            }
            $filas = DB::table('pos_vuelto_registro as v')
                ->leftJoin('sis_moneda as m', 'm.id', '=', 'v.moneda_cobro_id')
                ->where('v.cierre_caja', '=', $idCierre)
                ->where('v.vuelto_moneda_base', '>', 0)
                ->orderBy('v.fecha_hora', 'desc')
                ->select(
                    'v.*',
                    'm.cod_general as moneda_cod',
                    'm.simbolo as moneda_simbolo'
                )
                ->limit(80)
                ->get();

            $totales = DB::table('pos_vuelto_registro')
                ->where('cierre_caja', '=', $idCierre)
                ->where('vuelto_moneda_base', '>', 0)
                ->selectRaw('
                    COALESCE(SUM(vuelto_moneda_doc), 0) as vuelto_moneda_doc,
                    COALESCE(SUM(vuelto_moneda_base), 0) as vuelto_moneda_base,
                    COALESCE(SUM(monto_retenido_doc), 0) as monto_retenido_doc,
                    COALESCE(SUM(monto_recibido_doc), 0) as monto_recibido_doc
                ')
                ->first();

            return [
                'filas' => $filas->all(),
                'totales' => [
                    'vuelto_moneda_doc' => (float) ($totales->vuelto_moneda_doc ?? 0),
                    'vuelto_moneda_base' => (float) ($totales->vuelto_moneda_base ?? 0),
                    'monto_retenido_doc' => (float) ($totales->monto_retenido_doc ?? 0),
                    'monto_recibido_doc' => (float) ($totales->monto_recibido_doc ?? 0),
                ],
                'monedas' => [],
            ];
        } catch (\Throwable $e) {
            return self::vacioListado();
        }
    }

    /**
     * @return array<int,string>
     */
    public static function lineasResumenIngreso(int $idIngreso): array
    {
        $datos = self::listarPorIdIngreso($idIngreso);
        $lineas = [];
        foreach ($datos['filas'] as $r) {
            $cod = trim((string) ($r->moneda_cod ?? ''));
            $orden = trim((string) ($r->numero_orden ?? ''));
            $ordenTxt = $orden !== '' ? ' · ' . $orden : '';
            $lineas[] = '₡' . number_format((float) $r->vuelto_moneda_base, 2, '.', ',')
                . ' — rec. ' . number_format((float) $r->monto_recibido_doc, 2, '.', ',') . ' ' . $cod
                . ', ret. ' . number_format((float) $r->monto_retenido_doc, 2, '.', ',') . ' ' . $cod
                . $ordenTxt;
        }

        return $lineas;
    }

    /**
     * @return array{filas: array<int,object>, totales: array<string,float>, monedas: array<int,object>}
     */
    public static function listarPorCajaAbierta(int $usuarioId, int $sucursalId): array
    {
        $idCaja = CajaController::getIdCaja($usuarioId, $sucursalId);
        if (! $idCaja) {
            return self::vacioListado();
        }

        return self::listarPorCierreCaja((int) $idCaja);
    }
}
