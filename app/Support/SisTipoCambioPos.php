<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SisTipoCambioPos
{
    private const EPSILON = 0.0001;

    /**
     * Inserta un nuevo tipo de cambio si difiere del último vigente en BD.
     *
     * @return array{guardado: bool, tipo_cambio: float, era_igual: bool, mensaje?: string}
     */
    public static function registrarSiDifiere(int $monedaId, float $tipoCambio, ?int $usuarioId = null, ?int $sucursalId = null): array
    {
        if ($monedaId <= 0 || $tipoCambio <= 0) {
            return ['guardado' => false, 'tipo_cambio' => $tipoCambio, 'era_igual' => false, 'mensaje' => 'Datos inválidos'];
        }

        try {
            $moneda = DB::table('sis_moneda')->where('id', '=', $monedaId)->where('estado', '=', 'A')->first();
            if ($moneda === null) {
                return ['guardado' => false, 'tipo_cambio' => $tipoCambio, 'era_igual' => false, 'mensaje' => 'Moneda no encontrada'];
            }
            if (($moneda->es_base ?? 'N') === 'S') {
                return ['guardado' => false, 'tipo_cambio' => 1.0, 'era_igual' => true];
            }

            $ultimo = DB::table('sis_tipo_cambio')
                ->where('moneda_id', '=', $monedaId)
                ->orderBy('id', 'desc')
                ->value('tipo_cambio');

            $tcNuevo = round($tipoCambio, 6);
            if ($ultimo !== null && abs((float) $ultimo - $tcNuevo) < self::EPSILON) {
                return ['guardado' => false, 'tipo_cambio' => $tcNuevo, 'era_igual' => true];
            }

            DB::table('sis_tipo_cambio')->insert([
                'moneda_id' => $monedaId,
                'fecha_hora' => date('Y-m-d H:i:s'),
                'tipo_cambio' => $tcNuevo,
                'usuario' => $usuarioId,
                'sucursal' => $sucursalId,
            ]);

            return ['guardado' => true, 'tipo_cambio' => $tcNuevo, 'era_igual' => false];
        } catch (\Throwable $e) {
            return [
                'guardado' => false,
                'tipo_cambio' => $tipoCambio,
                'era_igual' => false,
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    public static function ultimoTipoCambio(int $monedaId): ?float
    {
        if ($monedaId <= 0) {
            return null;
        }
        $raw = DB::table('sis_tipo_cambio')
            ->where('moneda_id', '=', $monedaId)
            ->orderBy('id', 'desc')
            ->value('tipo_cambio');

        return $raw !== null ? (float) $raw : null;
    }
}
