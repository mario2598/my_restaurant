@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4><i class="fas fa-file-invoice"></i> Facturas Electrónica</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <button type="button" class="btn btn-info btn-sm mr-2" onclick="verOrdenesSinFactura()" 
                                    style="cursor: pointer;" title="Ver órdenes sin factura asociada">
                                    <i class="fas fa-list"></i> Ver órdenes sin factura asociada
                                </button>
                                <input type="text" id="input_buscar_generico" class="form-control"
                                    placeholder="Buscar..">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"
                                        onclick="$('#input_buscar_generico').trigger('change');"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body" style="padding-top: 10px;">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> <strong>Instrucciones:</strong>
                            <ul style="margin-bottom: 0; padding-left: 20px;">
                                <li>Las facturas con estado <strong>"Pendiente"</strong> pueden ser enviadas a Hacienda automáticamente</li>
                                <li>Las facturas con estado <strong>"Rechazado"</strong> pueden ser <strong>reenviadas</strong> después de corregir los errores</li>
                                <li><strong><i class="fas fa-file-invoice"></i> Enviar/Reenviar Factura:</strong> Incluye datos del cliente (formato nuevo FactuX - Recomendado)</li>
                                <li><strong><i class="fas fa-paper-plane"></i> Enviar/Reenviar Comprobante:</strong> Sin datos del cliente (método original)</li>
                                <li>Use el botón <strong><i class="fas fa-eye"></i> Ver JSON</strong> para revisar el comprobante antes de enviar</li>
                                <li>Use el botón <strong><i class="fas fa-sync-alt"></i> Consultar Estado</strong> para verificar el estado en Hacienda y ver los errores</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" style="width: 100%">

                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Sucursal</label>
                                    <select class="form-control" id="select_sucursal" name="sucursal">
                                        <option value="T" selected>Todos</option>
                                        @foreach ($data['sucursales'] as $i)
                                            <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}">
                                                {{ $i->descripcion ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" class="form-control" id="desde" value="" />

                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date" class="form-control" id="hasta" value="" />
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label>Buscar</label>
                                    <button onclick="filtrar()" class="btn btn-primary btn-icon form-control"
                                        style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                </div>
                            </div>

                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tbl-ordenes" style="max-height: 100%;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" style="text-align: center">No.Orden</th>
                                            <th scope="col" style="text-align: center">Sucursal</th>
                                            <th scope="col" style="text-align: center">Fecha</th>
                                            <th scope="col" style="text-align: center">Cédula</th>
                                            <th scope="col" style="text-align: center">Nombre</th>
                                            <th scope="col" style="text-align: center">Correo</th>
                                            <th scope="col" style="text-align: center">No.Comprobante</th>
                                            <th scope="col" style="text-align: center">Estado</th>
                                            <th scope="col" style="text-align: center">Estado Hacienda</th>
                                            <th scope="col" style="text-align: center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-ordenes">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    </div>


    <div class="modal fade bd-example-modal-lg" id='mdl-envia' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered  modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <h5 class="modal-title mt-0" id="edit_cliente_text">
                        <i class="fas fa-payment"></i> Factura electrónica
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="width: 100%">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Número comprobante hacienda</label>
                                    <textarea class="form-control" name="num_comprobante" id="num_comprobante" maxlength="250"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <a class="btn btn-warning" title="Anular Orden" onclick="enviarOrden()"
                            style="color:white;cursor:pointer;">Marcar como envíada</a>
                        <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarMdlEnvia()'
                            style="cursor: pointer;">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar pagos sin FE -->
    <div class="modal fade" id='mdl-pagos-sin-fe' tabindex="-1" role="dialog"
        aria-labelledby="mdlPagosSinFeLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdlPagosSinFeLabel">
                        <i class="fas fa-list"></i> Pagos sin Factura Electrónica Asociada
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="tbl-pagos-sin-fe">
                            <thead class="thead-light">
                                <tr>
                                    <th style="text-align: center">ID Pago</th>
                                    <th style="text-align: center">No. Orden</th>
                                    <th style="text-align: center">Fecha</th>
                                    <th style="text-align: center">Cliente</th>
                                    <th style="text-align: center">Sucursal</th>
                                    <th style="text-align: center">Total</th>
                                    <th style="text-align: center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-pagos-sin-fe">
                                <tr>
                                    <td colspan="7" style="text-align: center;">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear FE desde pago sin FE -->
    <div class="modal fade" id='mdl-crear-fe-pago' tabindex="-1" role="dialog"
        aria-labelledby="mdlCrearFePagoLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdlCrearFePagoLabel">
                        <i class="fas fa-file-invoice"></i> Crear Factura Electrónica
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="crear-fe-id-pago">
                    <input type="hidden" id="crear-fe-id-orden">
                    <input type="hidden" id="crear-fe-numero-orden">
                    
                    <div class="form-group">
                        <label>Orden: <strong id="crear-fe-numero-orden-display"></strong></label>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="crear-fe-es-comprobante" 
                                onchange="toggleCamposCliente()">
                            <label class="custom-control-label" for="crear-fe-es-comprobante">
                                <strong>Comprobante sin cliente</strong> <small class="text-muted">(Tiquete electrónico)</small>
                            </label>
                        </div>
                    </div>

                    <div id="campos-cliente-container">
                        <div class="form-group">
                            <label>Cliente <small class="text-muted">(Opcional)</small></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="crear-fe-cliente-nombre" 
                                    placeholder="Buscar cliente o ingresar manualmente" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" onclick="abrirModalBuscarClienteFE()">
                                        <i class="fas fa-search"></i> Buscar Cliente
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="crear-fe-cliente-id">
                        </div>

                        <div class="form-group">
                            <label>Cédula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="crear-fe-cedula" 
                                placeholder="Ingrese la cédula" required>
                        </div>

                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="crear-fe-nombre" 
                                placeholder="Ingrese el nombre completo" required>
                        </div>

                        <div class="form-group">
                            <label>Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="crear-fe-correo" 
                                placeholder="correo@ejemplo.com" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarCrearFE()">
                        <i class="fas fa-save"></i> Crear FE
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para buscar cliente (reutilizado) -->
    <div class="modal fade" id="mdl-buscar-cliente-fe" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <h4 class="modal-title" id="mdl-buscar-cliente-fe-titulo">Buscar Cliente</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="width: 100%;">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="input-group">
                                <input type="text" class="form-control" id="txt-buscar-cliente-fe"
                                    placeholder="Buscar por nombre, apellidos, teléfono, correo o ubicación..."
                                    onkeyup="buscarClientesFEConDebounce(this.value)">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" 
                                        onclick="buscarClientesFEConDebounce(document.getElementById('txt-buscar-cliente-fe').value)">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="tbl-clientes-fe">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Ubicación</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-clientes-fe">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Buscando clientes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn-quitar-cliente-fe" onclick="quitarClienteDesdeModal()" style="display: none;">
                        <i class="fas fa-times"></i> Quitar Cliente
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/fe/facturas.js') }}"></script>
@endsection
