@extends('layout.master')
@section('style')
@endsection


@section('content')
    @include('layout.sidebar')
    <script>
        var idComanda = "{{ $data['idComanda'] ?? '' }}";
    </script>

    <style>
        .table td,
        .table th {
            height: 32px !important;
        }
        .prep-metricas .card-statistic-1 .card-body { font-size: clamp(0.75rem, 2.2vw, 0.95rem); }
        .prep-metricas .card-statistic-1 .card-header h4 { font-size: clamp(0.8rem, 2vw, 0.95rem); }
        .prep-metricas .card-metrica-clic { cursor: pointer; transition: box-shadow .15s ease; }
        .prep-metricas .card-metrica-clic:hover { box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,.12); }
        .prep-metricas .card-metrica-clic.card-metrica-sin-datos { cursor: default; opacity: .75; }
        .prep-metricas .card-metrica-clic.card-metrica-sin-datos:hover { box-shadow: none; }
        .prep-metricas .sla-indicador {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .78rem;
            margin-top: .2rem;
            opacity: .92;
        }
        .prep-metricas .sla-indicador .sla-emoji {
            font-size: .95rem;
            line-height: 1;
            animation: slaPulse 2.2s ease-in-out infinite;
        }
        .prep-metricas .sla-indicador.sla-warning .sla-emoji { animation-duration: 2s; }
        .prep-metricas .sla-indicador.sla-alert .sla-emoji { animation-duration: 1.6s; }
        @keyframes slaPulse {
            0%, 100% { transform: scale(1); opacity: .8; }
            50% { transform: scale(1.12); opacity: 1; }
        }
        #mdl_peores_lineas_prep .table { font-size: 0.875rem; }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="row prep-metricas mb-3" id="fila_metricas_tiempo">
                <div class="col-12 mb-2">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-clock"></i> Tiempos de referencia
                        <small class="d-block d-sm-inline text-muted font-weight-normal" id="metricas_alcance_txt"></small>
                    </h6>
                </div>
                <div class="col-12" id="contenedor_metricas_tiempo"></div>
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

    <div class="modal fade" id="mdl_peores_lineas_prep" tabindex="-1" role="dialog" aria-labelledby="tituloPeoresLineas"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloPeoresLineas">Líneas que más tardaron hoy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2" id="peores_lineas_leyenda"></p>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Orden</th>
                                    <th>Nº comanda</th>
                                    <th>Producto</th>
                                    <th class="text-center">Cant.</th>
                                    <th>Inicio prep.</th>
                                    <th>Fin prep.</th>
                                    <th class="text-center">Min</th>
                                    <th>Estación</th>
                                    <th class="text-center">vs SLA</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_peores_lineas_prep"></tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-2 mb-0 d-none" id="peores_lineas_vacio">No hay líneas terminadas en el día para mostrar.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
