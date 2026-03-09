@extends('layout.master')

@section('content')
@include('layout.sidebar')

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Panel de control</h1>
        </div>

        <div class="section-body">
            @php
                $dashboard = $data['dashboard'] ?? null;
                $kpisSucursales = $dashboard['kpis_sucursales'] ?? [];
                $resumenGlobal = $dashboard['resumen_global'] ?? null;
                $ordenesAbiertas = $dashboard['ordenes_abiertas'] ?? [];
                $tiemposPrep = $dashboard['tiempos_prep'] ?? [];
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
                    <form method="GET" action="{{ url('informes/panelControl') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="desde" class="mr-2">Desde</label>
                            <input type="date" id="desde" name="desde" class="form-control"
                                   value="{{ $dashboard['fecha_desde'] ?? date('Y-m-d') }}">
                        </div>
                        <div class="form-group mr-2">
                            <label for="hasta" class="mr-2">Hasta</label>
                            <input type="date" id="hasta" name="hasta" class="form-control"
                                   value="{{ $dashboard['fecha_hasta'] ?? date('Y-m-d') }}">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Resumen global -->
            @if($resumenGlobal)
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Ventas totales</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($resumenGlobal->total_vendido, 2, '.', ',') }} CRC
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Tickets</h4>
                                </div>
                                <div class="card-body">
                                    {{ $resumenGlobal->tickets }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Ticket promedio</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($resumenGlobal->ticket_promedio, 2, '.', ',') }} CRC
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Medios de pago</h4>
                                </div>
                                <div class="card-body" style="font-size: 11px; line-height: 1.4;">
                                    Efec: {{ number_format($resumenGlobal->efectivo, 0, '.', ',') }}<br>
                                    Tjta: {{ number_format($resumenGlobal->tarjeta, 0, '.', ',') }}<br>
                                    SINPE: {{ number_format($resumenGlobal->sinpe, 0, '.', ',') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- KPIs por sucursal -->
            <div class="row">
                @foreach($kpisSucursales as $kpi)
                    <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
                        <div class="card">
                            <div class="card-header pb-1">
                                <h4 class="mb-0">{{ $kpi->sucursal_nombre }}</h4>
                            </div>
                            <div class="card-body pt-2 pb-2">
                                <div style="font-size: 12px;">
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
                    </div>
                @endforeach
            </div>

            <!-- Tiempos operativos (preparación y entrega) -->
            @if($resumenTiempos)
                <div class="row mt-3">
                    <div class="col-12">
                        <h5 class="mb-2"><i class="fas fa-clock"></i> Tiempos operativos</h5>
                    </div>
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Prep. promedio</h4></div>
                                <div class="card-body">{{ $resumenTiempos->prep_promedio_min ?? 0 }} min</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Entrega promedio</h4></div>
                                <div class="card-body">{{ $resumenTiempos->entrega_promedio_min ?? 0 }} min</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon {{ ($resumenTiempos->pct_sla_prep ?? 0) >= 80 ? 'bg-success' : (($resumenTiempos->pct_sla_prep ?? 0) >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Dentro SLA ({{ $resumenTiempos->sla_minutos ?? 15 }} min)</h4></div>
                                <div class="card-body">{{ $resumenTiempos->pct_sla_prep ?? 0 }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                @if(count($tiemposPrep) > 0)
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h4 class="mb-0">Tiempos por sucursal</h4></div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Sucursal</th>
                                                    <th class="text-center">Órdenes con prep.</th>
                                                    <th class="text-center">Prep. promedio (min)</th>
                                                    <th class="text-center">% en SLA</th>
                                                    <th class="text-center">Órdenes entregadas</th>
                                                    <th class="text-center">Entrega promedio (min)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tiemposPrep as $tp)
                                                    @php
                                                        $te = collect($tiemposEntrega)->firstWhere('sucursal_id', $tp->sucursal_id);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $tp->sucursal_nombre }}</td>
                                                        <td class="text-center">{{ $tp->cantidad }}</td>
                                                        <td class="text-center">{{ $tp->promedio_min !== null ? $tp->promedio_min : '—' }}</td>
                                                        <td class="text-center">{{ $tp->pct_sla }}%</td>
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
            <div class="row mt-3">
                <div class="col-md-4 col-6 mb-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Incidentes</h4></div>
                            <div class="card-body">{{ $totalIncidentes }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Monto rebajado</h4></div>
                            <div class="card-body">{{ number_format($totalMontoRebajado, 2, '.', ',') }} CRC</div>
                        </div>
                    </div>
                </div>
            </div>
            @if(count($incidentesSucursal) > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h4 class="mb-0">Incidentes por sucursal</h4></div>
                            <div class="card-body p-2">
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
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="mb-2"><i class="fas fa-chalkboard"></i> Desempeño por comanda</h5>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h4 class="mb-0">Promedios por tipo de comanda</h4></div>
                        <div class="card-body p-2">
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
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header"><h4 class="mb-0">Comandas de mayor duración</h4></div>
                        <div class="card-body p-2">
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
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Órdenes en curso (no pagadas)</h4>
                        </div>
                        <div class="card-body">
                            @if(count($ordenesAbiertas) === 0)
                                <p class="text-muted mb-0">No hay órdenes pendientes de pago en el rango seleccionado.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
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

        </div>
    </section>
</div>
@endsection
