{{-- Modales compartidos POS / POS Barra --}}
@section('popup')
<div class="modal fade" id="mdl-pago" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Procesar Pago</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">


                    <!-- CÃ³digo de descuento -->
                    <div class="row mb-3">
                        <div class="col-8">
                            <input type="text" class="form-control" name="txt_codigo_descuento"
                                id="txt_codigo_descuento" placeholder="CÃ³digo de Descuento"
                                onkeyup="enterDescuento(event)">
                        </div>
                        <div class="col-4 text-right">
                            <button class="btn btn-success mr-2" onclick="validarCodDescuento()">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-danger" onclick="eliminarCodDescuento()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="col-12 mt-2" id="cont-dsc_promo" style="display: none">
                            <strong id="txt-dsc_promo"></strong>
                        </div>
                    </div>

                    <!-- InformaciÃ³n del cliente -->
                    <div class="row mb-3">

                        <div class="input-group">
                            <input type="text" class="form-control" id="nombreCliente"
                                placeholder="Nombre del Cliente">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button"
                                    onclick="abrirModalBuscarCliente()" title="Buscar Cliente">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>


                    </div>

                    <div class="col-12 mb-3">
                        <!-- Panel compacto de informaciÃ³n del cliente seleccionado -->
                        <div id="cliente-info-panel" class="mt-1" style="display: none;">
                            <div class="alert alert-success py-2 mb-0" style="border-left: 3px solid #28a745;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-check text-success mr-2"></i>
                                        <small class="text-muted">Cliente:</small>
                                        <strong class="ml-1" id="cliente-nombre-info">-</strong>
                                        <span class="mx-2 text-muted">|</span>
                                        <small class="text-muted">Tel:</small>
                                        <span class="ml-1" id="cliente-telefono-info">-</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-sm"
                                        onclick="limpiarClienteSeleccionado()" title="Limpiar Cliente">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div id="cliente-detalles-extra" class="mt-1" style="display: none;">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope mr-1"></i><span id="cliente-correo-info">-</span>

                                    </small>
                                </div>
                                <div id="cliente-fe-info" class="mt-1">
                                    <button type="button" class="btn btn-sm badge-info border-0" id="cliente-fe-info-2" onclick="abrirModalFE()" style="cursor: pointer; font-size: 0.75rem; padding: 0.35rem 0.7rem;">
                                        <i class="fas fa-file-invoice mr-1"></i> Factura ElectrÃ³nica Disponible
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acciones adicionales -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <button class="btn btn-success btn-block" onclick="abrirModalEnvio()">
                                <i class="fas fa-truck"></i> Datos EnvÃ­o
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-success btn-block" id="btn_fe" onclick="changeFacturacionElectronica()">
                                <i class="fas fa-user"></i> Factura ElectrÃ³nica: NO
                            </button>
                        </div>
                        <div class="col-4" id="cont-btn-incidente-pago" style="display: none;">
                            <button type="button" class="btn btn-success btn-block" onclick="abrirModalIncidentePago()">
                                <i class="fas fa-exclamation-triangle"></i> Agregar Incidente
                            </button>
                        </div>
                    </div>

                    <!-- BotÃ³n principal de pago -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-block" id="btnPago"
                                onclick="procesarPagoMixto()">
                                Pagar en diferentes metodos
                            </button>
                        </div>
                    </div>

                    <!-- Formas de pago -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-3">
                            <label for="monto_tarjeta">Monto Tarjeta (â‚¡)</label>
                            <input type="number" class="form-control" step="any" id="monto_tarjeta"
                                name="monto_tarjeta" placeholder="0.00" onkeyup="enterCampoPago(event)"
                                min="0">
                            <button type="button" class="btn btn-primary btn-block mt-2" id="btnPagoTarjeta"
                                onclick="verificarAbrirModalPagoTarjeta()">
                                Pagar Todo con Tarjeta
                            </button>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label for="monto_efectivo">Monto Efectivo (â‚¡)</label>
                            <input type="number" class="form-control" step="any" id="monto_efectivo"
                                name="monto_efectivo" placeholder="0.00" onkeyup="enterCampoPago(event)"
                                min="0">
                            <button type="button" class="btn btn-primary btn-block mt-2" id="btnPagoEfectivo"
                                onclick="verificarAbrirModalPagoEfectivo()">
                                Pagar Todo con Efectivo
                            </button>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label for="monto_sinpe">Monto Sinpe (â‚¡)</label>
                            <input type="number" class="form-control" step="any" id="monto_sinpe"
                                name="monto_sinpe" placeholder="0.00" onkeyup="enterCampoPago(event)"
                                min="0">
                            <button type="button" class="btn btn-primary btn-block mt-2" id="btnPagoSinpe"
                                onclick="verificarAbrirModalPagoSinpe()">
                                Pagar Todo con Sinpe
                            </button>
                        </div>
                    </div>

                    <!-- Moneda / tipo de cambio (tiquete y pago_orden); TC se toma del Ãºltimo registro en sis_tipo_cambio (base = 1). -->
                    <div class="row mb-3 border-top pt-3">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="pos_moneda_factura_id">Moneda del cobro</label>
                            <select class="form-control" id="pos_moneda_factura_id" name="pos_moneda_factura_id">
                                @foreach ($data['monedasFacturaPos'] ?? [] as $mf)
                                    @php
                                        $tcAttr = ($mf->es_base ?? '') === 'S' ? '1' : ($mf->tipo_cambio_vigente !== null ? (string) $mf->tipo_cambio_vigente : '');
                                    @endphp
                                    <option value="{{ $mf->id }}"
                                        data-es-base="{{ $mf->es_base ?? 'N' }}"
                                        data-cod="{{ $mf->cod_general }}"
                                        data-simbolo="{{ $mf->simbolo }}"
                                        data-decimales="{{ (int) ($mf->decimales ?? 2) }}"
                                        data-tc="{{ $tcAttr }}">{{ $mf->simbolo }} {{ $mf->nombre }} ({{ $mf->cod_general }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">El tipo de cambio se carga automÃ¡ticamente desde la tabla vigente (moneda base: TC = 1).</small>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label>Tipo de cambio aplicado</label>
                            <input type="hidden" id="pos_tipo_cambio_snapshot" name="pos_tipo_cambio_snapshot" value="">
                            <p class="form-control mb-0 bg-light" id="pos_tc_vigente_display" style="min-height: 38px; line-height: 38px;">â€”</p>
                            <small class="text-muted">Unidades de moneda base por <strong>1</strong> unidad de la moneda elegida.</small>
                        </div>
                    </div>
                    <div class="col-12 mb-2" id="pos_aviso_solo_efectivo" style="display: none;">
                        <div class="alert alert-info py-2 mb-0 small">
                            Moneda distinta de la base: el cobro solo puede hacerse en <strong>efectivo</strong> (tarjeta y SINPE no estÃ¡n disponibles para esta moneda).
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="row mb-3 text-center">
                        <div class="col-6 col-md-3">
                            <h6 id="txt-total-pagar_mdl" class="text-muted">Total Orden: 0,00</h6>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="text-muted" id="txt-descuento-pagar_mdl">Descuento: 0,00</h6>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 id="txt-mto-envio_mdl" class="text-muted">EnvÃ­o: No aplica</h6>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 id="txt-mto-pagado_mdl" class="text-muted">Monto Pagado: 0,00</h6>
                        </div>
                    </div>
                    <div class="row mb-2 text-center" id="row-rebajar-incidentes-mdl" style="display: none;">
                        <div class="col-12">
                            <h6 id="txt-rebajar-incidentes-mdl" class="text-warning mb-0">Total a rebajar en incidentes: 0,00</h6>
                        </div>
                    </div>
                    <div id="cont-incidente-orden-mdl" class="row mb-2" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-warning py-2 mb-0 small">
                                <strong><i class="fas fa-exclamation-triangle"></i> Incidente:</strong>
                                <span id="incidente-descripcion-mdl"></span>
                                <span id="incidente-monto-mdl" class="font-weight-bold ml-1"></span>
                                <button type="button" class="btn btn-danger btn-sm ml-2 py-0" id="btn-eliminar-incidente-mdl" onclick="eliminarIncidenteOrden()" title="Eliminar incidente"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Total seleccionado -->
                    <div class="row mb-3">
                        <div class="col-12 text-center">
                            <h4 id="txt-total-seleccionado" class="text-muted">Total Seleccionado a Pagar: 0,00</h4>
                        </div>
                    </div>

                    <!-- Opciones de selecciÃ³n de lÃ­neas -->
                    <div class="row mb-3">
                        <div class="col-12 d-flex justify-content-between">
                            <button type="button" class="btn btn-link"
                                onclick="seleccionarTodasLasLineas(true)">Seleccionar Todas</button>
                            <button type="button" class="btn btn-link"
                                onclick="seleccionarTodasLasLineas(false)">Deseleccionar Todas</button>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Seleccionar</th>
                                            <th>Detalle</th>
                                            <th>Cantidad Total</th>
                                            <th>Cantidad Pagada</th>
                                            <th>Cantidad a Pagar</th>
                                            <th>Precio</th>
                                            <th>Total Pagar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-detalles-dividir-cuentas">
                                        <!-- Los detalles se llenarÃ¡n dinÃ¡micamente aquÃ­ -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick='cerrarMdlPago()'>Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Incidente (desde modal de pago) -->
<div class="modal fade" id="mdl-incidente-pago" role="dialog" aria-labelledby="mdlIncidentePagoLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlIncidentePagoLabel"><i class="fas fa-exclamation-triangle"></i> Agregar incidente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="cerrarModalIncidentePago()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>DescripciÃ³n <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="incidente_pago_descripcion" rows="2" maxlength="2500" placeholder="Describa el incidente..."></textarea>
                </div>
                <div class="form-group">
                    <label>Monto afectado (â‚¡)</label>
                    <input type="number" class="form-control" id="incidente_pago_monto" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Clave maestra <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="incidente_pago_clave_maestra" placeholder="Ingrese su clave maestra" autocomplete="off">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalIncidentePago()">Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="guardarIncidenteDesdeModalPago()"><i class="fas fa-save"></i> Registrar incidente</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-center" id='mdl-loader-pago' tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="padding: 5%;">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12" style="text-align: center; margin-bottom:10px;">
                    <h2>Procesando pago</h2>
                </div>
                <div class="col-4 col-md-4 col-lg-4"></div>
                <div class="col-4 col-md-4 col-lg-4" style="text-align: center;">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Procesando pago</span>
                    </div>
                </div>
                <div class="col-4 col-md-4 col-lg-4"></div>
                <div class="col-12 col-md-12 col-lg-12" style="text-align: center;">
                    <small id="texto_pago_aux"></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-center" id='mdl-extras' tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="width: 100%">
                <div class="row" id="cont-extras" style="width: 100%">

                </div>

            </div>
            <div class="modal-footer">
                <div class="form-group">
                    <a class="btn btn-primary" title="Guardar " onclick="seleccionarExtrasProd()"
                        style="color:white;cursor:pointer;">Agregar</a>
                    <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarExtras()'
                        style="cursor: pointer;">Cerrar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-center" id='mdl-extras-detalle' role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body" style="width: 100%">
                <div class="row">
                    <div class="col-sm-12">
                        <label>Detalle </label>
                        <textarea name="detAdicional" id="detAdicional" style="width: 100%">
                        </textarea>
                    </div>
                    <div class="col-sm-12">
                        <label>Extras</label>
                        <div class="row" id="cont-extras-detalle" style="width: 100%">

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-group">
                    <a class="btn btn-primary" title="Guardar" onclick="actualizarExtrasDetalle()"
                        style="color:white;cursor:pointer;">Agregar</a>
                    <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarExtrasDetalle()'
                        style="cursor: pointer;">Cerrar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bd-example-modal-lg" id='mdl-cerrar-caja' role="dialog" aria-labelledby="mySmallModalLabel"
    aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered  modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="width: 100%">
                <h4>Cierre Caja - {{ session('usuario')['nombre'] }}</h4>
            </div>
            <div class="modal-body" style="width: 100%;padding:10px!important;">

                <div class="row">
                    <!-- cierre caja -->
                    <div class="col-12 col-md-12 col-lg-12" style="margin-top: 15px;">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-white">
                                    <!-- Efectivo con Input -->
                                    <div class="row" id="fila_efectivo_cierre_monedas" style="display:none; border-bottom: dotted 1px black; margin-top:15px;">
                                        <div class="col-12 pb-2">
                                            <p class="font-20 mb-1" style="font-size:14px; color: black;">Efectivo contado (por moneda)</p>
                                            <small class="text-muted">Indique lo fÃ­sico en caja. TC = unidades de moneda base por 1 unidad de la moneda.</small>
                                            <div id="body_inputs_efectivo_cierre" class="mt-2"></div>
                                            <p class="mt-2 mb-0" style="font-size:13px; color: black;">
                                                <strong>Equivalente moneda base:</strong>
                                                <span id="lbl_efectivo_cierre_total_base">0,00</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row" id="fila_efectivo_cierre_legacy"
                                        style="border-bottom: dotted 1px black; margin-top:15px; align-items: center;">
                                        <div class="col-xs-4 col-md-4 col-lg-4">
                                            <p class="font-20"
                                                style="font-size:14px; color: black; text-align: left; margin: 0;">
                                                Efectivo
                                            </p>
                                        </div>
                                        <div class="col-xs-8 col-md-8 col-lg-8 pb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"
                                                    style="background-color: #f8f9fa; border: 1px solid #ced4da;">CRC</span>
                                                <input type="number" class="form-control" id="monto_efectivo_input"
                                                    name="monto_efectivo" placeholder="Ingrese el monto en efectivo de la caja"
                                                    style="text-align: right; border: 1px solid #ced4da;"
                                                    min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tarjetas -->
                                    <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                        <div class="col-xs-4 col-md-4 col-lg-4">
                                            <p class="font-20" style="font-size:12px;color: black; text-align: left;">
                                                Tarjetas
                                            </p>
                                        </div>
                                        <div class="col-xs-8 col-md-8 col-lg-8">
                                            <p class="font-20" style="color: black; text-align: right;"
                                                id="monto_tarjetas_lbl">
                                                CRC <strong>{{ number_format('0.00', 2, '.', ',') }}</strong>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- SINPE -->
                                    <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                        <div class="col-xs-4 col-md-4 col-lg-4">
                                            <p class="font-20" style="font-size:12px;color: black; text-align: left;">
                                                SINPE</p>
                                        </div>
                                        <div class="col-xs-8 col-md-8 col-lg-8">
                                            <p class="font-20" style="color: black;text-align: right;"
                                                id="monto_sinpe_lbl">
                                                CRC <strong>{{ number_format('0.00', 2, '.', ',') }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>

                    <div class="form-group">
                        <label>Terminar</label>
                        <input type="buttom" style="cursor: pointer;" class="btn btn-primary form-control"
                            onclick='cerrarCaja()' value="Cerrar Caja" />
                    </div>

                    <div class="form-group">
                        <label>Volver</label>
                        <input type="buttom" style="cursor: pointer;" class="btn btn-secondary form-control"
                            onclick='cerrarModalCerrarCaja()' value="Regresar" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@php
    $__monCierrePos = isset($data['monedasFacturaPos']) ? $data['monedasFacturaPos'] : collect();
@endphp
<script>
    window.POS_MONEDAS_CIERRE = @json($__monCierrePos->values()->all());
</script>

<!-- Modal de nuevo cliente (simplificado) -->
<div class="modal fade" id="mdl-nuevo-cliente" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_cliente_text">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-nuevo-cliente">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_cliente">Nombre *</label>
                                <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellidos_cliente">Apellidos</label>
                                <input type="text" class="form-control" id="apellidos_cliente" name="apellidos_cliente">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono_cliente">TelÃ©fono</label>
                                <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correo_cliente">Correo ElectrÃ³nico</label>
                                <input type="email" class="form-control" id="correo_cliente" name="correo_cliente">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="ubicacion_cliente">UbicaciÃ³n</label>
                                <input type="text" class="form-control" id="ubicacion_cliente" name="ubicacion_cliente">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevoCliente()">
                    <i class="fas fa-save"></i> Guardar Cliente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal mapa de mesas (POS) -->
<div class="modal fade" id="mdl-pos-plano-mesas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
                            href="#pos-plano-tab-ordenes" role="tab">Ver Ã³rdenes</a>
                    </li>
                </ul>
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
                                <span class="lg-disponible">Disponible</span>
                                <span class="lg-ocupada">Ocupada</span>
                                <span class="lg-pendiente">Orden pendiente</span>
                                <span class="lg-actual">Mesa actual</span>
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
