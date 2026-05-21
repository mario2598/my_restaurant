<!-- Modal mapa de mesas (POS) -->
<div class="modal fade" id="mdl-pos-plano-mesas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="fas fa-map-marked-alt text-info"></i> Mapa del local</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body py-2">
                <ul class="nav nav-tabs mb-2" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-pos-plano-seleccionar" data-toggle="tab"
                            href="#pos-plano-tab-seleccionar" role="tab">Seleccionar mesa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pos-plano-ordenes" data-toggle="tab"
                            href="#pos-plano-tab-ordenes" role="tab">Ver órdenes por mesa</a>
                    </li>
                </ul>
                <div id="pos-plano-resumen" class="alert alert-light border py-2 px-3 mb-2 small d-none"></div>
                <p class="small text-muted mb-2" id="pos-plano-ayuda">Toque una mesa para asignarla a la orden actual.</p>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pos-plano-tab-seleccionar" role="tabpanel"></div>
                    <div class="tab-pane fade" id="pos-plano-tab-ordenes" role="tabpanel"></div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div id="pos-plano-wrapper">
                            <div id="pos-plano-canvas">
                                <div id="pos-plano-zonas"></div>
                                <div id="pos-plano-mesas"></div>
                            </div>
                            <div class="pos-plano-leyenda">
                                <span class="lg-disponible">Libre</span>
                                <span class="lg-ocupada">Ocupada</span>
                                <span class="lg-pendiente">Cuenta pendiente</span>
                                <span class="lg-pagada">Solo pagadas</span>
                                <span class="lg-actual">Mesa actual</span>
                                <span class="lg-forma-redonda">Redonda</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div id="pos-plano-sidebar" class="border rounded bg-light">
                            <p class="text-muted small p-2 mb-0">Toque una mesa en el mapa.</p>
                        </div>
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
