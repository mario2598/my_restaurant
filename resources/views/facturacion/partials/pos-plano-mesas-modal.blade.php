<!-- Modal mapa de mesas (POS) -->
<div class="modal fade" id="mdl-pos-plano-mesas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="fas fa-map-marked-alt text-info"></i> Mapa del local</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body py-2">
                <ul class="nav nav-tabs mb-2" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-pos-plano-mapa" data-toggle="tab"
                            href="#pos-plano-tab-mapa" role="tab">Mapa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pos-plano-generales" data-toggle="tab"
                            href="#pos-plano-tab-generales" role="tab">Todas las cuentas</a>
                    </li>
                </ul>
                <div id="pos-plano-resumen" class="alert alert-light border py-2 px-3 mb-2 small d-none"></div>
                <p class="small text-muted mb-2" id="pos-plano-ayuda">
                    <i class="fas fa-hand-pointer"></i> Toque una mesa → el panel derecho muestra sus cuentas y opciones.
                </p>
                <div class="tab-content d-none">
                    <div class="tab-pane fade show active" id="pos-plano-tab-mapa" role="tabpanel"></div>
                    <div class="tab-pane fade" id="pos-plano-tab-generales" role="tabpanel"></div>
                </div>
                <div id="pos-plano-tabs-pisos" class="d-flex flex-wrap align-items-center mb-2" style="gap:4px;display:none!important"></div>

                <div class="row" id="pos-plano-layout-mapa">
                    <div class="col-12 col-lg-7" id="pos-plano-col-mapa">
                        <div id="pos-plano-wrapper">
                            <div id="pos-plano-canvas-scaler" class="pos-plano-canvas-scaler">
                                <div id="pos-plano-canvas" class="plano-canvas">
                                    <div id="pos-plano-zonas"></div>
                                    <div id="pos-plano-mesas"></div>
                                </div>
                            </div>
                            <div class="pos-plano-leyenda">
                                <span class="lg-disponible">Libre</span>
                                <span class="lg-ocupada">Ocupada</span>
                                <span class="lg-pendiente">Por cobrar</span>
                                <span class="lg-sillas">Sillas</span>
                                <span class="lg-actual">Mesa actual</span>
                                <span class="lg-seleccion">Seleccionada</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5" id="pos-plano-col-sidebar">
                        <div id="pos-plano-detalle-hint" class="pos-plano-detalle-hint d-none" aria-live="polite">
                            <i class="fas fa-chevron-down"></i>
                            Detalle de <strong class="pos-plano-detalle-hint__mesa">la mesa</strong> — cuentas y acciones abajo
                        </div>
                        <div id="pos-plano-sidebar" class="pos-plano-sidebar-panel">
                            <div class="pos-plano-sidebar-welcome">
                                <div class="pos-plano-welcome-icon"><i class="fas fa-map-marked-alt"></i></div>
                                <p class="mb-1 font-weight-bold">Seleccione una mesa</p>
                                <p class="small text-muted mb-0">Verá el desglose de cuentas, podrá abrir cada factura o asignar la mesa a su orden.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="pos-plano-layout-generales" class="d-none">
                    <div id="pos-plano-lista-generales" class="pos-plano-sidebar-panel p-2" style="max-height:58vh;overflow-y:auto;">
                        <p class="text-muted small mb-0">Cargando cuentas…</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="cargarPlanoPos()">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>
