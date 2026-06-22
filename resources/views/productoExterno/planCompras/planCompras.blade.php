@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4><i class="fas fa-shopping-cart mr-2"></i>Plan de Compras</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" id="btn_buscar_pro" class="form-control" placeholder="Buscar producto...">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor:pointer;"><i class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">

                        {{-- Filtros --}}
                        <form id="form_plan_compras" action="{{ URL::to('productoExterno/planCompras/filtro') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="row align-items-end mb-3">
                                <div class="col-sm-12 col-md-5">
                                    <label class="font-weight-bold text-muted mb-1" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">
                                        <i class="fas fa-store mr-1"></i>Sucursal
                                    </label>
                                    <select class="form-control form-control-sm" id="sucursal" name="sucursal" required>
                                        <option value="-1">Seleccione una sucursal</option>
                                        @foreach ($data['sucursales'] as $s)
                                            <option value="{{ $s->id ?? '' }}"
                                                @if ($s->id == $data['filtros']['sucursal']) selected @endif>
                                                {{ $s->descripcion ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-12 col-md-3 mt-2 mt-md-0">
                                    <label class="font-weight-bold text-muted mb-1" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">
                                        <i class="fas fa-calendar-alt mr-1"></i>Período de análisis
                                    </label>
                                    <select class="form-control form-control-sm" name="dias">
                                        <option value="7"  @if($data['filtros']['dias']==7)  selected @endif>Últimos 7 días</option>
                                        <option value="14" @if($data['filtros']['dias']==14) selected @endif>Últimos 14 días</option>
                                        <option value="30" @if($data['filtros']['dias']==30) selected @endif>Últimos 30 días</option>
                                        <option value="60" @if($data['filtros']['dias']==60) selected @endif>Últimos 60 días</option>
                                        <option value="90" @if($data['filtros']['dias']==90) selected @endif>Últimos 90 días</option>
                                    </select>
                                </div>
                                <div class="col-sm-12 col-md-auto mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-calculator mr-1"></i>Calcular Plan
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(count($data['plan']) > 0)
                        @php
                            $criticos = 0; $bajos = 0; $atencion = 0; $ok = 0; $total_compra = 0;
                            $top10_arr = []; $top10_nombres = []; $top10_consumo = [];
                            $por_categoria = []; $por_proveedor = [];

                            foreach ($data['plan'] as $p) {
                                if ($p->dias_restantes >= 9999)    $ok++;
                                elseif ($p->dias_restantes <= 3)   $criticos++;
                                elseif ($p->dias_restantes <= 7)   $bajos++;
                                elseif ($p->dias_restantes <= 14)  $atencion++;
                                else                               $ok++;
                                $total_compra += $p->sugerido_comprar * $p->precio_compra;

                                $cat = $p->categoria ?? 'Sin categoría';
                                if (!isset($por_categoria[$cat])) $por_categoria[$cat] = ['categoria'=>$cat,'costo'=>0,'productos'=>0];
                                $por_categoria[$cat]['costo'] += $p->sugerido_comprar * $p->precio_compra;
                                if ($p->sugerido_comprar > 0) $por_categoria[$cat]['productos']++;

                                $prov = $p->proveedor ?? 'Sin proveedor';
                                if (!isset($por_proveedor[$prov])) $por_proveedor[$prov] = ['proveedor'=>$prov,'items'=>[],'total'=>0,'count'=>0];
                                if ($p->sugerido_comprar > 0) {
                                    $por_proveedor[$prov]['items'][] = $p;
                                    $por_proveedor[$prov]['total'] += $p->sugerido_comprar * $p->precio_compra;
                                    $por_proveedor[$prov]['count']++;
                                }

                                if ($p->consumo_periodo > 0) $top10_arr[] = $p;
                            }
                            usort($top10_arr, function($a,$b){ return $b->consumo_periodo - $a->consumo_periodo; });
                            $top10_arr = array_slice($top10_arr, 0, 10);
                            foreach ($top10_arr as $t) {
                                $top10_nombres[] = mb_strlen($t->nombre) > 22 ? mb_substr($t->nombre,0,20).'…' : $t->nombre;
                                $top10_consumo[] = (int)$t->consumo_periodo;
                            }
                            usort($por_categoria, function($a,$b){ return $b['costo'] - $a['costo']; });
                            usort($por_proveedor, function($a,$b){ return $b['total'] - $a['total']; });
                        @endphp

                        {{-- 4 cards resumen --}}
                        <div class="row mb-3">
                            <div class="col-6 col-md-3 mb-2">
                                <div class="card mb-0" style="border-left:4px solid #dc3545;">
                                    <div class="card-body py-2 px-3">
                                        <div class="text-danger font-weight-bold" style="font-size:1.6rem;">{{ $criticos }}</div>
                                        <div class="text-muted" style="font-size:0.73rem;"><i class="fas fa-exclamation-triangle mr-1"></i>Críticos (≤3 días)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="card mb-0" style="border-left:4px solid #fd7e14;">
                                    <div class="card-body py-2 px-3">
                                        <div style="font-size:1.6rem;font-weight:700;color:#fd7e14;">{{ $bajos }}</div>
                                        <div class="text-muted" style="font-size:0.73rem;"><i class="fas fa-arrow-down mr-1"></i>Bajos (4–7 días)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="card mb-0" style="border-left:4px solid #28a745;">
                                    <div class="card-body py-2 px-3">
                                        <div class="text-success font-weight-bold" style="font-size:1.6rem;">{{ $ok }}</div>
                                        <div class="text-muted" style="font-size:0.73rem;"><i class="fas fa-check mr-1"></i>OK (>14 días)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="card mb-0" style="border-left:4px solid #007bff;">
                                    <div class="card-body py-2 px-3">
                                        <div class="text-primary font-weight-bold" style="font-size:1.1rem;">&#8353;{{ number_format($total_compra,0,'.',',') }}</div>
                                        <div class="text-muted" style="font-size:0.73rem;"><i class="fas fa-coins mr-1"></i>Costo estimado compra</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Gráficas: Barras + Dona --}}
                        <div class="row mb-3">
                            <div class="col-lg-7 mb-3">
                                <div class="card mb-0 h-100">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0"><i class="fas fa-chart-bar mr-1 text-primary"></i>Top 10 más consumidos</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <canvas id="chartTopConsumo" style="max-height:270px;"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 mb-3">
                                <div class="card mb-0 h-100">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0"><i class="fas fa-chart-pie mr-1 text-warning"></i>Estado del stock</h6>
                                    </div>
                                    <div class="card-body p-3 d-flex align-items-center justify-content-center">
                                        <canvas id="chartEstado" style="max-height:250px;max-width:250px;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tendencia semanal --}}
                        @if(isset($data['tendencia']) && count($data['tendencia']) > 1)
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card mb-0">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0"><i class="fas fa-chart-line mr-1 text-success"></i>Tendencia de consumo semanal</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <canvas id="chartTendencia" style="max-height:180px;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Por Proveedor + Por Categoría --}}
                        <div class="row mb-3">
                            <div class="col-lg-7 mb-3">
                                <div class="card mb-0">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-truck mr-1 text-info"></i>Orden de compra por proveedor</h6>
                                        <small class="text-muted">Solo ítems a comprar</small>
                                    </div>
                                    <div class="card-body p-0">
                                        @forelse($por_proveedor as $pv)
                                        <div class="border-bottom">
                                            <div class="d-flex justify-content-between align-items-center px-3 py-2"
                                                style="cursor:pointer;background:#f8f9fa;"
                                                onclick="togglePv('pv{{ $loop->index }}')">
                                                <span class="font-weight-bold" style="font-size:0.88rem;">
                                                    <i class="fas fa-chevron-right mr-1 pv-ico" id="ico_pv{{ $loop->index }}" style="font-size:0.65rem;transition:transform .2s;"></i>
                                                    {{ $pv['proveedor'] }}
                                                    <span class="badge badge-secondary ml-1" style="font-size:0.7rem;">{{ $pv['count'] }} prod.</span>
                                                </span>
                                                <strong class="text-primary" style="font-size:0.88rem;">&#8353;{{ number_format($pv['total'],0,'.',',') }}</strong>
                                            </div>
                                            <div id="pv{{ $loop->index }}" style="display:none;">
                                                <table class="table table-sm mb-0" style="font-size:0.8rem;">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Producto</th>
                                                            <th class="text-center">Stock</th>
                                                            <th class="text-center">Comprar</th>
                                                            <th class="text-center">Precio unit.</th>
                                                            <th class="text-center">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($pv['items'] as $item)
                                                        <tr>
                                                            <td>{{ $item->nombre }}</td>
                                                            <td class="text-center">
                                                                <span class="badge badge-{{ $item->stock_actual <= 0 ? 'danger' : 'secondary' }}">{{ $item->stock_actual }}</span>
                                                            </td>
                                                            <td class="text-center font-weight-bold text-primary">{{ $item->sugerido_comprar }}</td>
                                                            <td class="text-center">&#8353;{{ number_format($item->precio_compra,0,'.',',') }}</td>
                                                            <td class="text-center font-weight-bold">&#8353;{{ number_format($item->sugerido_comprar*$item->precio_compra,0,'.',',') }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-check-circle mr-1 text-success"></i>No hay productos a comprar
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5 mb-3">
                                <div class="card mb-0 h-100">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0"><i class="fas fa-tags mr-1 text-warning"></i>Costo estimado por categoría</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0" style="font-size:0.82rem;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Categoría</th>
                                                    <th class="text-center">Productos</th>
                                                    <th class="text-right">Costo est.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($por_categoria as $cat_item)
                                                @if($cat_item['costo'] > 0)
                                                <tr>
                                                    <td><span class="badge badge-info" style="font-size:0.75rem;">{{ $cat_item['categoria'] }}</span></td>
                                                    <td class="text-center">{{ $cat_item['productos'] }}</td>
                                                    <td class="text-right font-weight-bold">&#8353;{{ number_format($cat_item['costo'],0,'.',',') }}</td>
                                                </tr>
                                                @endif
                                                @endforeach
                                            </tbody>
                                            @if($total_compra > 0)
                                            <tfoot>
                                                <tr class="thead-light">
                                                    <td colspan="2" class="font-weight-bold">TOTAL</td>
                                                    <td class="text-right font-weight-bold text-primary">&#8353;{{ number_format($total_compra,0,'.',',') }}</td>
                                                </tr>
                                            </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif {{-- end plan > 0 --}}

                        {{-- Tabla principal --}}
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaPlanCompras">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:75px;">Código</th>
                                        <th>Producto</th>
                                        <th class="text-center d-none d-md-table-cell">Categoría</th>
                                        <th class="d-none d-lg-table-cell">Proveedor</th>
                                        <th class="text-center">Stock</th>
                                        <th class="text-center">Consumo<br><small class="text-muted">({{ $data['filtros']['dias'] ?? 30 }}d)</small></th>
                                        <th class="text-center">Prom/día</th>
                                        <th class="text-center">Días rest.</th>
                                        <th class="text-center">Sugerido</th>
                                        <th class="text-center d-none d-md-table-cell">Costo est.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['plan'] as $p)
                                    @php
                                        if ($p->dias_restantes >= 9999)     { $badge='secondary'; $dias_txt='Sin mov.'; }
                                        elseif ($p->dias_restantes <= 3)    { $badge='danger';    $dias_txt=$p->dias_restantes.' días'; }
                                        elseif ($p->dias_restantes <= 7)    { $badge='warning';   $dias_txt=$p->dias_restantes.' días'; }
                                        elseif ($p->dias_restantes <= 14)   { $badge='info';      $dias_txt=$p->dias_restantes.' días'; }
                                        else                                 { $badge='success';   $dias_txt=$p->dias_restantes.' días'; }
                                        $stock_badge = $p->stock_actual <= 0 ? 'danger' : ($p->stock_actual <= 5 ? 'warning' : 'success');
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-dark" style="font-size:0.75rem;">{{ $p->codigo_barra ?? '—' }}</span>
                                        </td>
                                        <td class="align-middle"><strong>{{ $p->nombre }}</strong></td>
                                        <td class="text-center align-middle d-none d-md-table-cell">
                                            <span class="badge badge-light border" style="font-size:0.75rem;">{{ $p->categoria }}</span>
                                        </td>
                                        <td class="align-middle d-none d-lg-table-cell text-muted" style="font-size:0.82rem;">{{ $p->proveedor }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-{{ $stock_badge }}" style="font-size:0.88rem;min-width:28px;">{{ $p->stock_actual }}</span>
                                        </td>
                                        <td class="text-center align-middle" style="font-size:0.88rem;">{{ $p->consumo_periodo > 0 ? $p->consumo_periodo : '—' }}</td>
                                        <td class="text-center align-middle text-muted" style="font-size:0.82rem;">{{ $p->promedio_diario > 0 ? $p->promedio_diario : '—' }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-{{ $badge }} px-2 py-1" style="font-size:0.8rem;">{{ $dias_txt }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($p->sugerido_comprar > 0)
                                                <span class="badge badge-primary px-2 py-1" style="font-size:0.88rem;">{{ $p->sugerido_comprar }}</span>
                                            @else
                                                <span class="text-success"><i class="fas fa-check"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle d-none d-md-table-cell" style="font-size:0.82rem;">
                                            @if($p->sugerido_comprar > 0 && $p->precio_compra > 0)
                                                &#8353;{{ number_format($p->sugerido_comprar*$p->precio_compra,0,'.',',') }}
                                            @else —
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            @if(($data['filtros']['sucursal'] ?? -1) > 0)
                                                <i class="fas fa-info-circle mr-1"></i>No hay productos externos registrados para esta sucursal.
                                            @else
                                                <i class="fas fa-hand-point-up mr-1"></i>Seleccione una sucursal y período para calcular el plan de compras.
                                            @endif
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        window.addEventListener("load", function () {
            var sel = document.getElementById('sucursal');
            if (sel && sel.value == '-1' && sel.options.length > 1) {
                sel.value = sel.options[1].value;
                document.getElementById('form_plan_compras').submit();
                return;
            }

            var table = $('#tablaPlanCompras').DataTable({
                dom: 'Bfrtip', searching: true, paging: false,
                fixedHeader: { header: true },
                buttons: [
                    { extend: 'excel', title: 'Plan de Compras', filename: 'plan_compras' },
                    { extend: 'pdf',   title: 'Plan de Compras', filename: 'plan_compras' },
                    { extend: 'print', title: 'Plan de Compras' }
                ]
            });
            document.getElementById('btn_buscar_pro').addEventListener('keyup', function () {
                table.search(this.value).draw();
            });

            @if(count($data['plan']) > 0)
            // Bar horizontal - Top 10 consumidos
            new Chart(document.getElementById('chartTopConsumo'), {
                type: 'bar',
                data: {
                    labels: @json($top10_nombres),
                    datasets: [{ label: 'Unidades', data: @json($top10_consumo),
                        backgroundColor: 'rgba(54,162,235,0.75)', borderColor: 'rgba(54,162,235,1)', borderWidth: 1 }]
                },
                options: { indexAxis:'y', responsive:true, plugins:{legend:{display:false}},
                    scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } } }
            });

            // Dona - Estado stock
            new Chart(document.getElementById('chartEstado'), {
                type: 'doughnut',
                data: {
                    labels: ['Crítico (≤3d)','Bajo (4-7d)','Atención (8-14d)','OK (>14d)'],
                    datasets: [{ data: [{{ $criticos }},{{ $bajos }},{{ $atencion }},{{ $ok }}],
                        backgroundColor: ['#dc3545','#fd7e14','#17a2b8','#28a745'], borderWidth: 2 }]
                },
                options: { responsive:true, plugins:{ legend:{ position:'bottom', labels:{ font:{size:11} } } } }
            });

            @if(isset($data['tendencia']) && count($data['tendencia']) > 1)
            // Línea - Tendencia semanal
            new Chart(document.getElementById('chartTendencia'), {
                type: 'line',
                data: {
                    labels: @json(array_column($data['tendencia'], 'inicio_semana')),
                    datasets: [{ label: 'Consumo total', data: @json(array_column($data['tendencia'], 'consumo_semana')),
                        borderColor:'#28a745', backgroundColor:'rgba(40,167,69,0.1)',
                        tension:0.35, fill:true, pointRadius:5, pointHoverRadius:7 }]
                },
                options: { responsive:true, plugins:{legend:{display:false}},
                    scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } } }
            });
            @endif
            @endif
        });

        function togglePv(id) {
            var el = document.getElementById(id);
            var ico = document.getElementById('ico_' + id);
            if (el.style.display === 'none') { el.style.display='block'; if(ico) ico.style.transform='rotate(90deg)'; }
            else                             { el.style.display='none';  if(ico) ico.style.transform='rotate(0deg)'; }
        }
    </script>
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
@endsection
