<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Columnas opcionales de moneda / tipo de cambio en pago_orden.
 * Convención: tipo_cambio_snapshot = unidades de moneda base por 1 unidad de moneda_factura_id.
 */
class PagoOrdenMoneda
{
    private static function keysMontosDoc(): array
    {
        return [
            'total_moneda_doc',
            'subtotal_moneda_doc',
            'iva_moneda_doc',
            'descuento_moneda_doc',
            'impuesto_servicio_moneda_doc',
        ];
    }

    /**
     * @param  array<string,mixed>  $ordenArr  Payload "orden" del POS (opcional)
     * @param  array<string,mixed>  $montosDocOverride  Valores explícitos para columnas *_moneda_doc
     * @return array<string,mixed>
     */
    public static function extrasParaInsert(?Request $request = null, array $ordenArr = [], array $montosDocOverride = []): array
    {
        $src = [];
        $keys = array_merge(['moneda_factura_id', 'tipo_cambio_snapshot'], self::keysMontosDoc());

        if ($request !== null) {
            foreach ($keys as $k) {
                if ($request->has($k)) {
                    $src[$k] = $request->input($k);
                }
            }
            $nested = $request->input('moneda_factura');
            if (is_array($nested)) {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $nested)) {
                        $src[$k] = $nested[$k];
                    }
                }
            }
        }

        foreach ($keys as $k) {
            if (array_key_exists($k, $ordenArr)) {
                $src[$k] = $ordenArr[$k];
            }
        }
        if (isset($ordenArr['moneda_factura']) && is_array($ordenArr['moneda_factura'])) {
            foreach ($ordenArr['moneda_factura'] as $k => $v) {
                $src[$k] = $v;
            }
        }

        foreach ($montosDocOverride as $k => $v) {
            if (in_array($k, self::keysMontosDoc(), true)) {
                $src[$k] = $v;
            }
        }

        return self::normalize($src);
    }

    /**
     * @param  array<string,mixed>  $src
     * @return array<string,mixed>
     */
    public static function normalize(array $src): array
    {
        $defaults = [
            'moneda_factura_id' => null,
            'tipo_cambio_snapshot' => null,
            'total_moneda_doc' => null,
            'subtotal_moneda_doc' => null,
            'iva_moneda_doc' => null,
            'descuento_moneda_doc' => null,
            'impuesto_servicio_moneda_doc' => null,
        ];

        $mid = isset($src['moneda_factura_id']) ? (int) $src['moneda_factura_id'] : 0;
        $tcRaw = $src['tipo_cambio_snapshot'] ?? null;
        $tc = $tcRaw === null || $tcRaw === '' ? null : (float) $tcRaw;

        if ($mid <= 0 || $tc === null || $tc <= 0) {
            return $defaults;
        }

        $out = [
            'moneda_factura_id' => $mid,
            'tipo_cambio_snapshot' => round($tc, 6),
        ];

        foreach (self::keysMontosDoc() as $k) {
            if (! array_key_exists($k, $src)) {
                $out[$k] = null;

                continue;
            }
            $v = $src[$k];
            if ($v === null || $v === '') {
                $out[$k] = null;
            } else {
                $out[$k] = round((float) $v, 4);
            }
        }

        return $out;
    }
}
