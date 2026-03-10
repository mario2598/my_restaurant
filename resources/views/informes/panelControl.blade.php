@extends('layout.master')

@section('style')
<style>
    .panel-control .card-statistic-1 .card-body { font-size: clamp(0.75rem, 2.5vw, 1rem); }
    .panel-control .card-header h4 { font-size: clamp(0.85rem, 2.2vw, 1rem); }
    .panel-control h5.section-title { font-size: clamp(0.9rem, 2vw, 1.1rem); }
    .panel-control .table { font-size: clamp(0.7rem, 1.8vw, 0.875rem); }
    .panel-control .table th, .panel-control .table td { padding: 0.35rem 0.5rem; }
    .panel-control .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    @media (max-width: 575.98px) {
        .panel-control .card-statistic-1 .card-wrap .card-header h4 { font-size: 0.8rem; }
        .panel-control .section-header h1 { font-size: 1.25rem; }
        .panel-control .table th, .panel-control .table td { white-space: nowrap; }
    }
</style>
@endsection

@section('content')
@include('layout.sidebar')

<!-- Main Content -->
<div class="main-content panel-control">
    <section class="section">
        <div class="section-header">
            <h1 class="mb-0">Panel de control</h1>
        </div>

        <div class="section-body px-0 px-sm-2">
            @php
                $dashboard = $data['dashboard'] ?? null;
                $kpisSucursales = $dashboard['kpis_sucursales'] ?? [];
                $resumenGlobal = $dashboard['resumen_global'] ?? null;
                $ordenesAbiertas = $dashboard['ordenes_abiertas'] ?? [];
                $tiemposPrep = $dashboard['tiempos_prep'] ?? [];
                $tiemposPrepPorItem = $dashboard['tiempos_prep_por_item'] ?? [];
                $tiemposEntrega = $dashboard['tiempos_entrega'] ?? [];
                $resumenTiempos = $dashboard['resumen_tiempos'] ?? null;
                $incidentesSucursal = $dashboard['incidentes_sucursal'] ?? [];
                $totalIncidentes = $dashboard['total_incidentes'] ?? 0;
                $totalMontoRebajado = $dashboard['total_monto_rebajado'] ?? 0;
                $promediosPorComanda = $dashboard['promedios_por_comanda'] ?? [];
                $comandasMayorDuracion = $dashboard['comandas_mayor_duracion'] ?? [];
            @endphp

            <!-- Filtros de fecha -->
            <div class="row mb-3">
                <div class="col-12">
                    <form method="GET" action="{{ url('informes/panelControl') }}" class="w-100">
                        <div class="form-row">
                            <div class="form-group col-12 col-sm-6 col-md-3 mb-2">
                                <label for="desde" class="mr-2 d-block">Desde</label>
                                <input type="date" id="desde" name="desde" class="form-control form-control-sm"
                                       value="{{ $dashboard['fecha_desde'] ?? date('Y-m-d') }}">
                            </div>
                            <div class="form-group col-12 col-sm-6 col-md-3 mb-2">
                                <label for="hasta" class="mr-2 d-block">Hasta</label>
                                <input type="date" id="hasta" name="hasta" class="form-control form-control-sm"
                                       value="{{ $dashboard['fecha_hasta'] ?? date('Y-m-d') }}">
                            </div>
                            <div class="form-group col-12 col-sm-6 col-md-3 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block btn-sm">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen global -->
            @if($resumenGlobal)
                <div class="row">
                    <div class="col-12 col-md-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Ventas totales</h4>
                                </div>
                                <div class="card-body text-truncate" title="{{ number_format($resumenGlobal->total_vendido, 2, '.', ',') }} CRC">
                                    {{ number_format($resumenGlobal->total_vendido, 2, '.', ',') }} CRC
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon bg-info">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Tickets</h4>
                                </div>
                                <div class="card-body">{{ $resumenGlobal->tickets }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon bg-success">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Ticket prom.</h4>
                                </div>
                                <div class="card-body text-truncate" title="{{ number_format($resumenGlobal->ticket_promedio, 2, '.', ',') }} CRC">
                                    {{ number_format($resumenGlobal->ticket_promedio, 2, '.', ',') }} CRC
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Medios pago</h4>
                                </div>
                                <div class="card-body small" style="line-height: 1.35;">
                                    Efec: {{ number_format($resumenGlobal->efectivo, 0, '.', ',') }}<br>
                                    Tjta: {{ number_format($resumenGlobal->tarjeta, 0, '.', ',') }}<br>
                                    SINPE: {{ number_format($resumenGlobal->sinpe, 0, '.', ',') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100 cursor-pointer" data-toggle="modal" data-target="#mdlProductosVendidos" style="cursor: pointer;">
                            <div class="card-icon bg-secondary">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Productos vendidos</h4>
                                </div>
                                <div class="card-body small">
                                    <i class="fas fa-external-link-alt"></i> Ver detalle
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- KPIs por sucursal -->
            <div class="row">
                @foreach($kpisSucursales as $kpi)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-2 mb-md-3">
                        <div class="card h-100">
                            <div class="card-header py-1 py-sm-2">
                                <h4 class="mb-0 text-truncate" title="{{ $kpi->sucursal_nombre }}">{{ $kpi->sucursal_nombre }}</h4>
                            </div>
                            <div class="card-body pt-1 pb-1 pt-sm-2 pb-sm-2 small">
                                <div><strong>Ventas:</strong> {{ number_format($kpi->total_vendido, 2, '.', ',') }} CRC</div>
                                <div><strong>Tickets:</strong> {{ $kpi->tickets }}</div>
                                <div><strong>Promedio:</strong> {{ number_format($kpi->ticket_promedio, 2, '.', ',') }} CRC</div>
                                <div class="mt-1">
                                    <strong>Medios pago:</strong><br>
                                    Efec: {{ number_format($kpi->efectivo, 0, '.', ',') }}<br>
                                    Tjta: {{ number_format($kpi->tarjeta, 0, '.', ',') }}<br>
                                    SINPE: {{ number_format($kpi->sinpe, 0, '.', ',') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tiempos operativos (preparación y entrega) -->
            @if($resumenTiempos)
                <div class="row mt-2 mt-md-3">
                    <div class="col-12">
                        <h5 class="mb-2 section-title"><i class="fas fa-clock"></i> Tiempos operativos</h5>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon bg-info">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Prep. prom. (orden)</h4></div>
                                <div class="card-body">{{ $resumenTiempos->prep_promedio_min ?? 0 }} min</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Prep. por ítem</h4></div>
                                <div class="card-body">
                                    {{ $resumenTiempos->prep_por_item_promedio_min ?? 0 }} min
                                    @if(($resumenTiempos->prep_por_item_cantidad ?? 0) > 0)
                                        <small class="d-block text-muted">{{ number_format($resumenTiempos->prep_por_item_cantidad) }} ítems</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon bg-success">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Entrega prom.</h4></div>
                                <div class="card-body">{{ $resumenTiempos->entrega_promedio_min ?? 0 }} min</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-md-3">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon {{ ($resumenTiempos->pct_sla_prep ?? 0) >= 80 ? 'bg-success' : (($resumenTiempos->pct_sla_prep ?? 0) >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>SLA orden ({{ $resumenTiempos->sla_minutos ?? 15 }} min)</h4></div>
                                <div class="card-body">{{ $resumenTiempos->pct_sla_prep ?? 0 }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                @if(($resumenTiempos->prep_por_item_cantidad ?? 0) > 0)
                <div class="row">
                    <div class="col-12 col-md-4 mb-2">
                        <div class="card card-statistic-1 h-100">
                            <div class="card-icon {{ ($resumenTiempos->pct_sla_por_item ?? 0) >= 80 ? 'bg-success' : (($resumenTiempos->pct_sla_por_item ?? 0) >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>SLA por ítem ({{ $resumenTiempos->sla_minutos ?? 15 }} min)</h4></div>
                                <div class="card-body">{{ $resumenTiempos->pct_sla_por_item ?? 0 }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(count($tiemposPrep) > 0)
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-1 py-sm-2"><h4 class="mb-0">Tiempos por sucursal</h4></div>
                                <div class="card-body p-1 p-sm-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Sucursal</th>
                                                    <th class="text-center">Órdenes con prep.</th>
                                                    <th class="text-center">Prep. orden (min)</th>
                                                    <th class="text-center">% SLA orden</th>
                                                    <th class="text-center">Ítems prep.</th>
                                                    <th class="text-center">Prep. ítem (min)</th>
                                                    <th class="text-center">% SLA ítem</th>
                                                    <th class="text-center">Órdenes entregadas</th>
                                                    <th class="text-center">Entrega prom. (min)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tiemposPrep as $tp)
                                                    @php
                                                        $te = collect($tiemposEntrega)->firstWhere('sucursal_id', $tp->sucursal_id);
                                                        $titem = collect($tiemposPrepPorItem)->firstWhere('sucursal_id', $tp->sucursal_id);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $tp->sucursal_nombre }}</td>
                                                        <td class="text-center">{{ $tp->cantidad }}</td>
                                                        <td class="text-center">{{ $tp->promedio_min !== null ? $tp->promedio_min : '—' }}</td>
                                                        <td class="text-center">{{ $tp->pct_sla }}%</td>
                                                        <td class="text-center">{{ $titem ? $titem->cantidad_items : '—' }}</td>
                                                        <td class="text-center">{{ $titem && $titem->promedio_min !== null ? $titem->promedio_min : '—' }}</td>
                                                        <td class="text-center">{{ $titem ? $titem->pct_sla . '%' : '—' }}</td>
                                                        <td class="text-center">{{ $te ? $te->cantidad : '—' }}</td>
                                                        <td class="text-center">{{ $te && $te->promedio_min !== null ? $te->promedio_min : '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Incidentes / Calidad -->
            <div class="row mt-2 mt-md-3">
                <div class="col-12 col-md-4 mb-2 mb-md-3">
                    <div class="card card-statistic-1 h-100">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Incidentes</h4></div>
                            <div class="card-body">{{ $totalIncidentes }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-3">
                    <div class="card card-statistic-1 h-100">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Monto rebajado</h4></div>
                            <div class="card-body text-truncate" title="{{ number_format($totalMontoRebajado, 2, '.', ',') }} CRC">{{ number_format($totalMontoRebajado, 2, '.', ',') }} CRC</div>
                        </div>
                    </div>
                </div>
            </div>
            @if(count($incidentesSucursal) > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header py-1 py-sm-2"><h4 class="mb-0">Incidentes por sucursal</h4></div>
                            <div class="card-body p-1 p-sm-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Sucursal</th>
                                                <th class="text-center">Incidentes</th>
                                                <th class="text-center">Monto rebajado</th>
                                                <th class="text-center">Tickets</th>
                                                <th class="text-center">Tasa %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($incidentesSucursal as $inc)
                                                <tr>
                                                    <td>{{ $inc->sucursal_nombre }}</td>
                                                    <td class="text-center">{{ $inc->cantidad_incidentes }}</td>
                                                    <td class="text-center">{{ number_format($inc->monto_rebajado, 2, '.', ',') }} CRC</td>
                                                    <td class="text-center">{{ $inc->tickets_sucursal }}</td>
                                                    <td class="text-center">{{ $inc->tasa_incidentes }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Desempeño por comanda -->
            <div class="row mt-2 mt-md-3">
                <div class="col-12">
                    <h5 class="mb-2 section-title"><i class="fas fa-chalkboard"></i> Desempeño por comanda</h5>
                </div>
                <div class="col-12 mb-2 mb-md-3">
                    <div class="card">
                        <div class="card-header py-1 py-sm-2"><h4 class="mb-0">Promedios por tipo de comanda</h4></div>
                        <div class="card-body p-1 p-sm-2">
                            @if(count($promediosPorComanda) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Comanda</th>
                                                <th class="text-center">Ítems terminados</th>
                                                <th class="text-center">Tiempo promedio (min)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($promediosPorComanda as $pc)
                                                <tr>
                                                    <td>{{ $pc->comanda_nombre }}</td>
                                                    <td class="text-center">{{ $pc->cantidad_terminados }}</td>
                                                    <td class="text-center">{{ $pc->promedio_minutos !== null ? number_format($pc->promedio_minutos, 1, '.', ',') : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">No hay datos de comandas en el rango seleccionado.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-2 mt-md-3">
                    <div class="card">
                        <div class="card-header py-1 py-sm-2"><h4 class="mb-0">Comandas de mayor duración</h4></div>
                        <div class="card-body p-1 p-sm-2">
                            @if(count($comandasMayorDuracion) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">No. Orden</th>
                                                <th class="text-center">No. Comanda</th>
                                                <th>Sucursal</th>
                                                <th class="text-center">Inicio</th>
                                                <th class="text-center">Fin</th>
                                                <th class="text-center">Duración (min)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($comandasMayorDuracion as $cmd)
                                                @php
                                                    $inicio = $cmd->cmd_fecha_inicio ? date('d/m/Y H:i', strtotime($cmd->cmd_fecha_inicio)) : '—';
                                                    $fin = $cmd->cmd_fecha_fin ? date('d/m/Y H:i', strtotime($cmd->cmd_fecha_fin)) : '—';
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $cmd->numero_orden ?? $cmd->orden_id }}</td>
                                                    <td class="text-center">{{ $cmd->num_comanda ?? '—' }}</td>
                                                    <td>{{ $cmd->sucursal_nombre ?? '—' }}</td>
                                                    <td class="text-center">{{ $inicio }}</td>
                                                    <td class="text-center">{{ $fin }}</td>
                                                    <td class="text-center"><strong>{{ $cmd->duracion_minutos }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">No hay comandas con cierre en el rango seleccionado.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Órdenes en curso -->
            <div class="row mt-2 mt-md-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header py-1 py-sm-2">
                            <h4 class="mb-0">Órdenes en curso (no pagadas)</h4>
                        </div>
                        <div class="card-body p-1 p-sm-2">
                            @if(count($ordenesAbiertas) === 0)
                                <p class="text-muted mb-0 small">No hay órdenes pendientes de pago en el rango seleccionado.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">Sucursal</th>
                                                <th class="text-center">No. Orden</th>
                                                <th class="text-center">Mesa / Tipo</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Inicio</th>
                                                <th class="text-center">Cliente</th>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Pagado</th>
                                                <th class="text-center">Pendiente</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ordenesAbiertas as $o)
                                                @php
                                                    $pendiente = ($o->total_con_descuento ?? 0) - ($o->mto_pagado ?? 0);
                                                    $mesaTexto = $o->numero_mesa ? 'Mesa ' . $o->numero_mesa : 'Para llevar';
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $o->sucursal_nombre ?? '-' }}</td>
                                                    <td class="text-center">{{ $o->numero_orden ?? $o->id }}</td>
                                                    <td class="text-center">{{ $mesaTexto }}</td>
                                                    <td class="text-center">{{ $o->estado_nombre ?? '' }}</td>
                                                    <td class="text-center">{{ $o->fecha_inicio_formateada }}</td>
                                                    <td class="text-center">{{ $o->nombre_cliente ?? '' }}</td>
                                                    <td class="text-center">
                                                        {{ number_format($o->total_con_descuento ?? 0, 2, '.', ',') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ number_format($o->mto_pagado ?? 0, 2, '.', ',') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ number_format($pendiente, 2, '.', ',') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Productos vendidos -->
            <div class="modal fade" id="mdlProductosVendidos" tabindex="-1" role="dialog" aria-labelledby="mdlProductosVendidosLabel" aria-hidden="true" data-url-productos="{{ url('informes/panelControl/productosVendidos') }}">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header py-2 bg-light">
                            <h5 class="modal-title" id="mdlProductosVendidosLabel">Productos vendidos</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-2" id="pvPeriodo">Período: <span id="pvPeriodoTexto">-</span></p>
                            <div id="pvCargando" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                <p class="mt-2 mb-0">Cargando...</p>
                            </div>
                            <div id="pvError" class="alert alert-warning py-3" style="display: none;" role="alert">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span id="pvErrorTexto">Error al cargar. Verifique permisos o conexión.</span>
                            </div>
                            <div id="pvSinDatos" class="text-center py-5 text-muted" style="display: none;">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p class="mb-0">No hay productos vendidos en el período seleccionado.</p>
                            </div>
                            <div id="pvContenido" style="display: none;">
                                <div class="row mb-3 small">
                                    <div class="col-6 col-md-3"><strong>Productos distintos:</strong> <span id="pvResumenProductos">0</span></div>
                                    <div class="col-6 col-md-3"><strong>Unidades:</strong> <span id="pvResumenUnidades">0</span></div>
                                    <div class="col-6 col-md-3"><strong>Venta total:</strong> <span id="pvResumenVenta">0</span> CRC</div>
                                    <div class="col-6 col-md-3"><strong>Tickets:</strong> <span id="pvResumenTickets">0</span></div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Producto</th>
                                                <th>Código</th>
                                                <th class="text-right">Unidades</th>
                                                <th class="text-right">Total vendido</th>
                                                <th class="text-center">En # tickets</th>
                                                <th class="text-center">% tickets</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="pvTbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection

@section('script')
<script>
(function() {
    var urlProductos = $('#mdlProductosVendidos').attr('data-url-productos') || '';
    var token = $('meta[name="csrf-token"]').attr('content');

    $('#mdlProductosVendidos').on('show.bs.modal', function () {
        var desde = $('#desde').val() || '';
        var hasta = $('#hasta').val() || '';
        $('#pvPeriodoTexto').text(desde && hasta ? desde + ' — ' + hasta : '-');
        $('#pvCargando').show();
        $('#pvError').hide();
        $('#pvSinDatos').hide();
        $('#pvContenido').hide();

        if (!urlProductos) {
            $('#pvCargando').hide();
            $('#pvErrorTexto').text('Configuración incorrecta. Recargue la página.');
            $('#pvError').show();
            return;
        }

        $.ajax({
            url: urlProductos,
            type: 'POST',
            data: { _token: token, desde: desde, hasta: hasta },
            dataType: 'json'
        }).done(function (data) {
            $('#pvCargando').hide();
            if (data && data.estado && data.datos) {
                var resumen = data.datos.resumen || {};
                var productos = data.datos.productos || [];
                if (productos.length === 0) {
                    $('#pvSinDatos').show();
                    return;
                }
                $('#pvResumenProductos').text(resumen.total_productos_distintos || 0);
                $('#pvResumenUnidades').text((resumen.total_unidades || 0).toLocaleString());
                var fmtNum = function (n) { return (n || 0).toLocaleString('es-CR', { minimumFractionDigits: 2 }); };
                $('#pvResumenVenta').text(fmtNum(resumen.total_venta));
                $('#pvResumenTickets').text(resumen.total_tickets || 0);
                var tbody = $('#pvTbody').empty();
                productos.forEach(function (p) {
                    var tr = $('<tr></tr>');
                    var nom = (p.nombre_producto || '').replace(/"/g, '&quot;');
                    tr.append($('<td class="text-truncate" style="max-width:180px"></td>').attr('title', nom).text(p.nombre_producto || '-'));
                    tr.append($('<td></td>').addClass('text-nowrap').text(p.codigo_producto || '-'));
                    tr.append($('<td></td>').addClass('text-right').text((p.cantidad_vendida || 0).toLocaleString()));
                    tr.append($('<td></td>').addClass('text-right').text(fmtNum(p.total_vendido) + ' CRC'));
                    tr.append($('<td></td>').addClass('text-center').text(p.num_ordenes || 0));
                    tr.append($('<td></td>').addClass('text-center').text((p.pct_tickets || 0) + '%'));
                    var btnCell = $('<td class="text-center"></td>');
                    if (p.extras && p.extras.length > 0) {
                        var btn = $('<button type="button" class="btn btn-xs btn-outline-secondary py-0 toggle-extras" data-producto="' + (p.nombre_producto || '').replace(/"/g, '&quot;') + '" data-codigo="' + (p.codigo_producto || '').replace(/"/g, '&quot;') + '"><i class="fas fa-plus"></i> Extras</button>');
                        btn.on('click', function () {
                            var row = $(this).closest('tr');
                            var next = row.next('tr.extras-row');
                            if (next.length) {
                                next.toggle();
                                $(this).find('i').toggleClass('fa-plus fa-minus');
                                return;
                            }
                            var extras = p.extras;
                            var trExtras = $('<tr class="extras-row bg-light"><td colspan="7" class="py-2"></td></tr>');
                            var cell = trExtras.find('td');
                            var tbl = $('<table class="table table-sm table-bordered mb-0 small"><thead><tr><th>Extra</th><th class="text-right">Veces</th><th class="text-right">Total</th></tr></thead><tbody></tbody></table>');
                            extras.forEach(function (e) {
                                tbl.find('tbody').append(
                                    $('<tr></tr>').append($('<td>' + (e.descripcion_extra || '-') + '</td>'))
                                        .append($('<td class="text-right">' + (e.cantidad_veces || 0) + '</td>'))
                                        .append($('<td class="text-right">' + fmtNum(e.total_extra) + ' CRC</td>'))
                                );
                            });
                            cell.append(tbl);
                            row.after(trExtras);
                            $(this).find('i').toggleClass('fa-plus fa-minus');
                        });
                        btnCell.append(btn);
                    } else {
                        btnCell.html('<span class="text-muted">-</span>');
                    }
                    tr.append(btnCell);
                    tbody.append(tr);
                });
                $('#pvContenido').show();
            } else {
                var msg = (data && data.mensaje) ? data.mensaje : 'No hay datos de productos vendidos en el período seleccionado.';
                $('#pvErrorTexto').text(msg);
                $('#pvError').show();
            }
        }).fail(function (xhr) {
            $('#pvCargando').hide();
            var msg = 'Error al cargar. ';
            if (xhr && xhr.responseJSON && xhr.responseJSON.mensaje) {
                msg += xhr.responseJSON.mensaje;
            } else if (xhr && xhr.status === 403) {
                msg += 'Sin permisos.';
            } else if (xhr && xhr.status === 500) {
                msg += 'Error del servidor.';
            } else {
                msg += 'Verifique su conexión.';
            }
            $('#pvErrorTexto').text(msg);
            $('#pvError').show();
        });
    });
})();
</script>
@endsection
