<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tiquete #{{ $orden->numero_orden ?? '' }}</title>
<style>
/* ── Screen layout ── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
    margin: 0;
    padding: 0;
}
.ticket {
    background: #fff;
    width: 100%;
    padding: 12px 10px;
    overflow: hidden;
    word-break: break-word;
    overflow-wrap: break-word;
}
.ticket-logo { text-align: center; margin-bottom: 0; }
.ticket-logo img {
    max-width: 130px;
    max-height: 90px;
    display: block;
    margin: 0 auto;
    object-fit: contain;
}
.ticket-header {
    text-align: center;
    margin-bottom: 10px;
    line-height: 1.65;
    padding: 9px 4px 6px;
    border-top: 2px solid #222;
}
.ticket-header .empresa {
    font-weight: 700;
    font-size: 16px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    display: block;
    padding-bottom: 5px;
    border-bottom: 1px dashed #888;
    margin-bottom: 4px;
}
.ticket-header div:not(.empresa) { font-size: 10px; color: #333; word-break: normal; overflow-wrap: break-word; }
hr.dashed { border: none; border-top: 1px dashed #999; margin: 6px 0; }
.ticket-info { margin-bottom: 6px; line-height: 1.6; }
.ticket-info .lbl { font-weight: 700; }
table.detalle { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 11px; table-layout: fixed; }
table.detalle th { font-weight: 700; border-bottom: 1px solid #333; padding: 2px 0; white-space: nowrap; overflow: hidden; word-break: normal; }
table.detalle td { padding: 2px 0; vertical-align: top; word-break: normal; overflow-wrap: break-word; }
table.detalle .td-total { white-space: nowrap; word-break: normal; font-size: 10.5px; }
table.detalle .td-qty { white-space: nowrap; word-break: normal; }
table.detalle .extra-row td { font-size: 11px; color: #555; padding-left: 8px; }
.totales { margin-top: 6px; }
.totales tr td:first-child { font-weight: 700; }
.totales tr td:last-child { text-align: right; }
.totales tr.grand td { font-size: 14px; font-weight: 700; border-top: 1px dashed #999; padding-top: 4px; }
.ticket-footer { text-align: center; margin-top: 10px; font-size: 11px; color: #666; }
.btn-print {
    display: block; width: 100%; margin-top: 14px;
    padding: 8px; background: #4e73df; color: #fff;
    border: none; border-radius: 4px; cursor: pointer; font-size: 14px;
}
.btn-print:hover { background: #2e59d9; }


@if(!($qz_mode ?? false))
/* ── Screen only ── */
@media screen {
    body {
        background: #f0f0f0;
        display: flex;
        justify-content: center;
        padding: 20px;
    }
    .ticket {
        width: {{ $ancho_mm == 58 ? '200px' : '300px' }};
        box-shadow: 0 2px 12px rgba(0,0,0,.18);
        border-radius: 4px;
        max-width: 100%;
    }
}
@endif

/* ── Print ── */
@media print {
    @page {
        size: {{ $ancho_mm }}mm auto;
        margin: 0;
    }
    body { background: none; padding: 0; }
    .ticket {
        width: 100%; box-shadow: none; border-radius: 0;
        padding: 4px 6px;
    }
    .btn-print { display: none !important; }
}
</style>
</head>
<body>
<div class="ticket">

    {{-- Logo --}}
    @if(!empty($logo_url))
    <div class="ticket-logo">
        <img src="{{ $logo_url }}" alt="Logo">
    </div>
    @endif

    {{-- Encabezado empresa --}}
    <div class="ticket-header">
        <div class="empresa">{{ $sucursal->nombre_factura ?? config('app.name') }}</div>
        @if(!empty($sucursal->cedula_factura))
        <div>Cédula: {{ $sucursal->cedula_factura }}</div>
        @endif
        @if(!empty($sucursal->correo_factura))
        <div>{{ $sucursal->correo_factura }}</div>
        @endif
        <div>Sucursal: {{ $orden->nombre_sucursal ?? $sucursal->descripcion ?? '' }}</div>
    </div>

    <hr class="dashed">

    {{-- Info de la orden --}}
    <div class="ticket-info">
        <div><span class="lbl">No. Orden:</span> {{ $orden->numero_orden ?? '' }}</div>
        <div>
            <span class="lbl">{{ $orden->mesa != null ? 'Mesa' : 'Tipo' }}:</span>
            {{ $orden->mesa != null ? ($orden->numero_mesa ?? '') : 'PARA LLEVAR' }}
        </div>
        @if(!empty($orden->nombre_cliente))
        <div><span class="lbl">Cliente:</span> {{ $orden->nombre_cliente }}</div>
        @endif
        <div><span class="lbl">Fecha:</span> {{ \Carbon\Carbon::parse($orden->fecha_fin)->format('d/m/Y H:i') }}</div>
    </div>

    <hr class="dashed">

    {{-- Detalle productos --}}
    <table class="detalle">
        <thead>
            <tr>
                <th style="width:9%;text-align:center;">Q</th>
                <th style="width:57%;">Producto</th>
                <th style="width:34%;text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($detalles as $d)
            <tr>
                <td class="td-qty" style="text-align:center;">{{ $d->cantidad }}</td>
                <td>
                    {{ $d->nombre_producto ?? $d->nombre ?? '' }}
                    <div style="font-size:10px;color:#888;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">P.U. {{ $simbolo }}{{ number_format($d->precio_unitario ?? 0, 2, '.', ',') }}</div>
                </td>
                <td class="td-total" style="text-align:right;">{{ $simbolo }}{{ number_format(($d->subtotal ?? ($d->precio_unitario * $d->cantidad)), 2, '.', ',') }}</td>
            </tr>
            @if(!empty($d->extras))
                @foreach($d->extras as $e)
                <tr class="extra-row">
                    <td></td>
                    <td colspan="3">+ {{ $e->nombre ?? '' }}</td>
                </tr>
                @endforeach
            @endif
        @endforeach
        </tbody>
    </table>

    <hr class="dashed">

    {{-- Totales --}}
    <table class="totales" style="width:100%;border-collapse:collapse;">
        @if(($orden->descuento ?? 0) > 0)
        <tr>
            <td>Descuento:</td>
            <td style="text-align:right;">-{{ $simbolo }}{{ number_format($orden->descuento, 2, '.', ',') }}</td>
        </tr>
        @endif
        @if(($orden->mto_impuesto_servicio ?? 0) > 0)
        <tr>
            <td>Serv. (10%):</td>
            <td style="text-align:right;">{{ $simbolo }}{{ number_format($orden->mto_impuesto_servicio, 2, '.', ',') }}</td>
        </tr>
        @endif
        @if(($orden->monto_envio ?? 0) > 0)
        <tr>
            <td>Envío:</td>
            <td style="text-align:right;">{{ $simbolo }}{{ number_format($orden->monto_envio, 2, '.', ',') }}</td>
        </tr>
        @endif
        @if(($sucursal->factura_iva ?? 0) == 1 && ($orden->impuesto ?? 0) > 0)
        <tr>
            <td>IVA:</td>
            <td style="text-align:right;">{{ $simbolo }}{{ number_format($orden->impuesto, 2, '.', ',') }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td>TOTAL:</td>
            <td style="text-align:right;">{{ $simbolo }}{{ number_format($orden->total_con_descuento, 2, '.', ',') }}</td>
        </tr>
    </table>

    {{-- Pagos --}}
    @if(!empty($pagos) && count($pagos) > 0)
    <hr class="dashed">
    <div style="font-size:12px;">
        @foreach($pagos as $p)
        <div style="display:flex;justify-content:space-between;">
            <span>{{ $p->tipo_pago ?? 'Pago' }}:</span>
            <span>{{ $simbolo }}{{ number_format($p->monto ?? 0, 2, '.', ',') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Footer --}}
    @if(!empty($nota_pie))
    <hr class="dashed">
    <div class="ticket-footer">{{ $nota_pie }}</div>
    @endif

    @if(!($qz_mode ?? false))
    <button class="btn-print" onclick="window.print()">
        🖨 Imprimir
    </button>
    @endif
</div>

@if($auto_imprimir)
<script>
window.onload = function() {
    setTimeout(function() {
        window.print();
        // close after print: onfocus fires when print dialog closes; timeout covers kiosk mode
        window.onfocus = function() { window.close(); };
        setTimeout(function() { window.close(); }, 1200);
    }, 400);
};
</script>
@endif
</body>
</html>
