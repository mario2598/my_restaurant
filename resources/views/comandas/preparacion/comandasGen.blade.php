@extends('layout.master')
@section('style')
@endsection


@section('content')
    @include('layout.sidebar')
    <script>
        var idComanda = "{{ $data['idComanda'] ?? '' }}";
    </script>

    <style>
        /* ── Comanda cards rediseño ─────────────────────────────────── */
        .cmd-card { border: none; border-radius: 14px; overflow: hidden; }
        .cmd-header {
            padding: 10px 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
        }
        .cmd-header-left { display: flex; flex-direction: column; flex: 1; min-width: 0; }
        .cmd-orden { color: #fff; font-weight: 700; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cmd-meta { color: rgba(255,255,255,.82); font-size: .77rem; margin-top: 2px; }
        .cmd-timer {
            color: #fff; font-weight: 700; font-size: .9rem;
            background: rgba(0,0,0,.22); border-radius: 8px; padding: 5px 11px;
            white-space: nowrap; flex-shrink: 0; text-align: center;
        }
        .cmd-timer.timer-ok  { background: rgba(0,0,0,.18); }
        .cmd-timer.timer-warn { background: rgba(180,130,0,.5); }
        .cmd-timer.timer-alert { background: rgba(185,28,28,.55); animation: cmd-pulse .8s ease-in-out infinite; }
        @keyframes cmd-pulse { 0%,100%{opacity:1} 50%{opacity:.6} }

        .cmd-items { padding: 2px 0; }
        .cmd-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; border-bottom: 1px solid #f0f0f2;
            cursor: pointer; transition: background .1s;
            -webkit-tap-highlight-color: transparent;
        }
        .cmd-item:last-child { border-bottom: none; }
        .cmd-item:active { background: #f5f5ff; }
        .cmd-item-done { opacity: .42; }
        .cmd-item-done .cmd-nombre { text-decoration: line-through; color: #6b7280; }

        .cmd-item-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; background: #f59e0b; transition: background .2s; }
        .cmd-item-done .cmd-item-dot { background: #22c55e; }

        .cmd-item-body { flex: 1; min-width: 0; }
        .cmd-nombre { font-weight: 600; font-size: .92rem; line-height: 1.3; color: #1e1e2e; }
        .cmd-obs {
            display: inline-block; margin-top: 3px;
            font-size: .76rem; font-weight: 600; color: #92400e;
            background: #fef3c7; border-radius: 5px; padding: 1px 7px;
        }
        .cmd-obs i { margin-right: 3px; }
        .cmd-extras { margin-top: 3px; }
        .cmd-extra-tag {
            display: inline-block; margin-right: 4px;
            font-size: .71rem; background: #e0e7ff; color: #3730a3;
            border-radius: 4px; padding: 1px 6px;
        }
        .cmd-qty {
            font-size: 1.35rem; font-weight: 800; color: #1e40af;
            min-width: 30px; text-align: center; flex-shrink: 0; line-height: 1;
        }
        .cmd-item-done .cmd-qty { color: #9ca3af; }
        .cmd-action { flex-shrink: 0; }

        .cmd-btn-listo {
            background: #16a34a; color: #fff; border: none; border-radius: 9px;
            padding: 8px 13px; font-size: .8rem; font-weight: 700;
            display: flex; flex-direction: column; align-items: center; gap: 2px;
            cursor: pointer; transition: background .12s, transform .08s;
            min-width: 52px; min-height: 46px; justify-content: center;
            -webkit-tap-highlight-color: transparent;
        }
        .cmd-btn-listo:active { background: #15803d; transform: scale(.96); }
        .cmd-btn-listo i { font-size: .88rem; }
        .cmd-btn-listo span { font-size: .68rem; line-height: 1; }
        .cmd-listo-done { color: #16a34a; font-size: 1.5rem; padding: 4px 10px; min-width: 52px; text-align: center; }

        .cmd-footer { padding: 8px 12px 12px; }
        .cmd-btn-terminar {
            width: 100%; background: transparent; border: 2px solid #16a34a; color: #16a34a;
            border-radius: 9px; padding: 10px 14px; font-size: .88rem; font-weight: 700;
            cursor: pointer; transition: all .15s; letter-spacing: .3px;
            -webkit-tap-highlight-color: transparent;
        }
        .cmd-btn-terminar:hover, .cmd-btn-terminar:active { background: #16a34a; color: #fff; }

        /* Keep table cells if used elsewhere */
        .table td, .table th { height: 32px !important; }
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
