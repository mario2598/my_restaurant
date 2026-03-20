@extends('layout.master')
@section('style')
@endsection


@section('content')
    @include('layout.sidebar')
    <script>
        var idComanda = "{{ $data['idComanda'] ?? '' }}";
    </script>
    <input type="hidden" id="tp_comanda_filtro_texto" value="{{ $data['comandaFiltroTexto'] ?? 'General (todas las comandas)' }}">

    <style>
        .table td,
        .table th {
            height: 32px !important;
        }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="row mb-2" id="contenedor_tiempos_trabajadores">
                <div class="col-12">
                    <div class="alert alert-light border py-2 px-3 mb-2 d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-1 mb-md-0">
                            <strong>Filtro actual:</strong> <span id="tp_filtro_comanda">-</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#mdlEstadisticasPrep">
                            <i class="fas fa-chart-bar"></i> Ver estadísticas
                        </button>
                    </div>
                    <div class="row" id="tp_cards">
                        <div class="col-12 col-sm-6 col-lg-3 mb-2">
                            <div class="card card-statistic-1 h-100">
                                <div class="card-wrap">
                                    <div class="card-header"><h4 class="mb-0">Líneas</h4></div>
                                    <div class="card-body">
                                        <span id="tp_pendientes_count">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3 mb-2">
                            <div class="card card-statistic-1 h-100">
                                <div class="card-wrap">
                                    <div class="card-header"><h4 class="mb-0">SLA línea (15m)</h4></div>
                                    <div class="card-body">
                                        <span id="tp_lineas_sla_pct">0.0</span> %
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3 mb-2">
                            <div class="card card-statistic-1 h-100">
                                <div class="card-wrap">
                                    <div class="card-header"><h4 class="mb-0">Prom. línea</h4></div>
                                    <div class="card-body">
                                        <span id="tp_pendientes_prom_min">0.0</span> min
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3 mb-2">
                            <div class="card card-statistic-1 h-100">
                                <div class="card-wrap">
                                    <div class="card-header"><h4 class="mb-0">Preparados</h4></div>
                                    <div class="card-body">
                                        <span id="tp_prep_count">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2" id="tp_table_wrap" style="display:none">
                        <div class="card">
                            <div class="card-header py-1 py-sm-2">
                                <h4 class="mb-0">Desglose por idComanda</h4>
                            </div>
                            <div class="card-body p-1 p-sm-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">idComanda</th>
                                                <th class="text-center">Pendientes</th>
                                                <th class="text-center">Prom. (min)</th>
                                                <th class="text-center">% <= 15m</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tp_tbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="contenedor_comandas">
            </div>
        </section>
        <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    </div>
    <!-- Modal para mostrar receta -->
    <!-- Modal para mostrar receta mejorado -->
    <div class="modal fade" id="mdl_mostrar_receta" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nombreProductoAux"></h5>
                    <button type="button" class="close" onclick="ocultarReceta()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="recetaTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="receta-tab" data-toggle="tab" href="#recetaContent"
                                role="tab" aria-controls="recetaContent" aria-selected="true">Receta</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="composicion-tab" data-toggle="tab" href="#composicionContent"
                                role="tab" aria-controls="composicionContent" aria-selected="false">Composición</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="recetaTabContent">
                        <!-- Contenido de la pestaña Receta -->
                        <div class="tab-pane fade show active" id="recetaContent" role="tabpanel"
                            aria-labelledby="receta-tab">
                            <ul id="listaReceta" class="list-group"></ul>
                        </div>
                        <!-- Contenido de la pestaña Composición -->
                        <div class="tab-pane fade" id="composicionContent" role="tabpanel"
                            aria-labelledby="composicion-tab">
                            <ul id="listaComposicion" class="list-group"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ocultarReceta()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('popup')
<div class="modal fade" id="mdlEstadisticasPrep" tabindex="-1" role="dialog" aria-labelledby="mdlEstadisticasPrepLabel" aria-hidden="true"
     data-url-estadisticas="{{ url('comandas/preparacion/estadisticasPreparacion') }}">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h5 class="modal-title" id="mdlEstadisticasPrepLabel">Estadísticas de preparación (por línea)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">
                    <strong>Solo día actual</strong> ({{ date('d/m/Y') }}), por fecha de inicio de la orden. Criterio: de <strong>ingreso</strong> a <strong>fin</strong> en cada línea de comanda. SLA: <span id="est_sla_texto">15</span> min.
                    <span class="d-block mt-1"><strong>Filtro comanda:</strong> <span id="est_filtro_comanda_modal">-</span></span>
                </p>
                <div class="mb-3">
                    <button type="button" class="btn btn-primary btn-sm" id="est_btn_actualizar">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div id="est_cargando" class="text-center py-4" style="display:none;">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 mb-0 small">Cargando…</p>
                </div>
                <div id="est_error" class="alert alert-warning py-2" style="display:none;" role="alert"></div>
                <div id="est_contenido" style="display:none;">
                    <div class="row mb-3 small">
                        <div class="col-6 col-md-3"><strong>Líneas terminadas:</strong> <span id="est_res_lineas">0</span></div>
                        <div class="col-6 col-md-3"><strong>Promedio:</strong> <span id="est_res_prom">—</span> min</div>
                        <div class="col-6 col-md-3"><strong>% SLA:</strong> <span id="est_res_sla">—</span> %</div>
                        <div class="col-6 col-md-3"><strong>Máx. una línea:</strong> <span id="est_res_max">—</span> min</div>
                    </div>
                    <h6 class="text-secondary border-bottom pb-1">Productos que más tardan en promedio</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Veces</th>
                                    <th class="text-center">Prom. (min)</th>
                                    <th class="text-center">Máx. (min)</th>
                                    <th class="text-center">% &gt; SLA</th>
                                </tr>
                            </thead>
                            <tbody id="est_tbody_productos"></tbody>
                        </table>
                    </div>
                    <h6 class="text-secondary border-bottom pb-1">Líneas individuales más largas (en el período)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">No. orden</th>
                                    <th>Producto</th>
                                    <th class="text-center">Minutos</th>
                                    <th class="text-center">Fin preparación</th>
                                </tr>
                            </thead>
                            <tbody id="est_tbody_record"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/comandas/preparacion/comandasGen.js') }}"></script>
@endsection
