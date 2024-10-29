@extends('layout.master')

@section('content')
    @include('layout.sidebar')
    <!-- Listas de productos -->
    <script>
        var tipos = []; 
        var productosGeneral = [];
        var ordenGestion = {
            "id": null,
            "cliente": "",
            "nueva": true,
            "total": 0,
            "envio": 0,
            "subTotal": 0,
            "codigoPromocion": "",
            "codigo_descuento": null,
            "mesa": -1,
            "numero_orden": "",
            "mto_pagado": 0,
            "pagado": false
        };
        var sucursalFacturaIva = "{{ $data['sucursalFacturaIva'] ?? false }}";
        var cajaAbierta = "{{ $data['cajaAbierta'] ?? false }}";
    </script>
    <style>
        icon-shape {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            vertical-align: middle;
        }

        .icon-sm {
            width: 2rem;
            height: 2rem;

        }

        .main-footer {
            margin-top: 0px !important;
        }
    </style>

    <!-- Main Content -->

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="row">

                            <div class="col-sm-12 col-md-5 col-lg-5" id="contEscogerProductos"
                                style="padding-right: 0px !important;padding-left: 0px !important;">

                                <div class="col-lg-12 col-md-12 pr-25">

                                    <!-- Productos -->
                                    <ul class="nav nav-pills" id="nv-tipos">
                                        <!-- Lista dinámica de tipos -->

                                    </ul>

                                    <div class="card">
                                        <!-- Categorías -->
                                        <div class="card-header col-12 mt-1"
                                            style="max-height: 450px;padding: 5px !important;">
                                            <ul id="scrl-categorias"
                                                class="nav nav-pills d-flex flex-row justify-content-space-between draggable-scroller"
                                                style="overflow-x: auto; cursor: grab; white-space: nowrap; flex-wrap: nowrap;">
                                                <!-- Lista dinámica categorías -->
                                            </ul>
                                        </div>
                                        <!-- Productos -->
                                        <div id="scrl-productos"
                                            class="col-12 d-flex flex-column justify-content-space-between card-body draggable-scroller"
                                            style="max-height: 450px;min-height: 450px; overflow-y: auto; cursor:grab;padding: 5px !important;">
                                            <table class="table table-borderless" style="background-color: white">
                                                <thead>
                                                    <th>Producto</th>
                                                    <th class="text-center">Precio</th>
                                                </thead>
                                                <tbody id="tbody-productos">
                                                    <!-- Lista dinámica de productos -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-7 col-lg-7"
                                style="padding-right: 0px !important;padding-left: 0px !important;">
                                <!-- Panel orden -->
                                <div class="col-lg-12 col-md-12 pl-0">
                                    <!-- Acciones -->
                                    <div style="padding: 0 5% 1.3% 0">
                                        <ul class="nav nav-pills d-flex flex-row justify-content-end" id="nv-acciones">
                                            <li id="contAbrirCaja">
                                                <button type="button" class="btn btn-success px-2 mr-1"
                                                    onclick="abrirCaja()">Abrir Caja <i class="fas fa-list"
                                                        aria-hidden="true"></i></button>
                                            </li>

                                            <li id="contRecargarOrden" style="display: none">
                                                <button type="button" class="btn btn-info px-2 mr-1"
                                                    onclick="recargarOrden()">Recargar Orden<i class="fas fa-reload"
                                                        aria-hidden="true"></i></button>
                                            </li>

                                            <li id="contLimiarCaja">
                                                <button type="button" class="btn btn-info px-2 mr-1"
                                                    onclick="limpiarOrden()">Nueva Orden<i class="fas fa-broom"
                                                        aria-hidden="true"></i></button>
                                            </li>

                                            <li id="contOrdenesCaja">
                                                <button type="button" class="btn btn-info px-2 mr-1"
                                                    onclick="recargarOrdenes()">Ver Ordenes <i class="fas fa-list"
                                                        aria-hidden="true"></i></button>
                                            </li>

                                            <li id="contCerrarCaja">
                                                <button type="button" class="btn btn-danger px-2 mr-1"
                                                    onclick="abrirModalCerrarCaja()">Cerrar Caja <i class="fas fa-list"
                                                        aria-hidden="true"></i></button>
                                            </li>

                                        </ul>

                                    </div>
                                    <!-- Orden -->
                                    <div class="col-12" id="contDetalles">
                                        <div class="card">
                                            <div class="card-header d-block" style="padding: 10px !important;">
                                                <div class="card-title">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <h4 id="infoHeaderOrden">Orden Nueva</h4>
                                                        </div>
                                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control h-75"
                                                                    name="txt-cliente" id="txt-cliente"
                                                                    placeholder="Nombre cliente..."
                                                                    onchange="changeNombreCliente(this.value)">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-md-6 col-lg-6 mt-1">
                                                            <div class="input-group">
                                                                <label class="mr-4 pt-2 pl-2">Mesa</label>
                                                                <select class="form-control" onchange="cambiarMesa()"
                                                                    id="select_mesa" name="select_mesa">
                                                                    <option value="-1" selected>PARA LLEVAR</option>
                                                                    @foreach ($data['mesas'] as $i)
                                                                        <option value="{{ $i->id ?? '' }}"
                                                                            title="{{ $i->numero_mesa ?? '' }}, Capacidad {{ $i->capacidad ?? '' }}">
                                                                            Mesa : {{ $i->numero_mesa ?? '' }} , Capacidad
                                                                            {{ $i->capacidad ?? '' }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>


                                            <div class="col-sm-12 col-md-2 col-lg-12" id="contFacturar" style="padding: 0;">
                                                <div class="container-fluid">
                                                    <div class="row" class="mb-3">
                                                        <div class="col-12">
                                                            <h4 id="txt-total-pagar" class="text-muted ">Total:
                                                                0,00</h4>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-12">
                                                            <ul class="nav nav-pills d-flex justify-content-start">
                                                                <button type="button" class="btn btn-info px-2 mr-1"
                                                                    id="btnPago" style="width: 100%;"
                                                                    onclick="abrirModalPago()">Procesar Pago Orden<i
                                                                        class="fas fa-bill" aria-hidden="true"></i>
                                                                </button>
                                                            </ul>

                                                            <ul class="nav nav-pills d-flex justify-content-start">
                                                                <button type="button" class="btn btn-info px-2 mr-1 mt-3"
                                                                    id="btnIniciarOrden" style="width: 100%;"
                                                                    onclick="iniciarOrden()">Iniciar Preparación Orden<i
                                                                        class="fas fa-bill" aria-hidden="true"></i>
                                                                </button>
                                                            </ul>

                                                            <ul class="nav nav-pills d-flex justify-content-start">
                                                                <button type="button" class="btn btn-info px-2 mr-1 mt-3"
                                                                    id="btnActualizarOrden"
                                                                    style="width: 100%; display:none;"
                                                                    onclick="actualizarOrdenGestion()">Guardar
                                                                    Modificaciones<i class="fas fa-update"
                                                                        aria-hidden="true"></i>
                                                                </button>
                                                            </ul>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="card-body" style="padding: 5px !important;">
                                                <table class="table">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Información</th>
                                                            <th>Extras</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody-orden">

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
@endsection

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


                        <!-- Código de descuento -->
                        <div class="row mb-3">
                            <div class="col-8">
                                <input type="text" class="form-control" name="txt_codigo_descuento"
                                    id="txt_codigo_descuento" placeholder="Código de Descuento"
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

                        <!-- Información del cliente -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="nombreCliente">Nombre del Cliente que Paga</label>
                                <input type="text" class="form-control" id="nombreCliente"
                                    placeholder="Nombre del Cliente">
                            </div>
                        </div>

                        <!-- Botones de acciones adicionales -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <button class="btn btn-success btn-block" onclick="abrirModalEnvio()">
                                    <i class="fas fa-truck"></i> Datos Envío
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-success btn-block" id="btn_fe" onclick="abrirModalFE()">
                                    <i class="fas fa-user"></i> Factura Electrónica: NO
                                </button>
                            </div>
                        </div>

                        <!-- Botón principal de pago -->
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
                                <label for="monto_tarjeta">Monto Tarjeta (₡)</label>
                                <input type="number" class="form-control" step="any" id="monto_tarjeta"
                                    name="monto_tarjeta" placeholder="0.00" onkeyup="enterCampoPago(event)"
                                    min="0">
                                <button type="button" class="btn btn-primary btn-block mt-2" id="btnPagoTarjeta"
                                    onclick="verificarAbrirModalPagoTarjeta()">
                                    Pagar Todo con Tarjeta
                                </button>
                            </div>
                            <div class="col-12 col-md-4 mb-3">
                                <label for="monto_efectivo">Monto Efectivo (₡)</label>
                                <input type="number" class="form-control" step="any" id="monto_efectivo"
                                    name="monto_efectivo" placeholder="0.00" onkeyup="enterCampoPago(event)"
                                    min="0">
                                <button type="button" class="btn btn-primary btn-block mt-2" id="btnPagoEfectivo"
                                    onclick="verificarAbrirModalPagoEfectivo()">
                                    Pagar Todo con Efectivo
                                </button>
                            </div>
                            <div class="col-12 col-md-4 mb-3">
                                <label for="monto_sinpe">Monto Sinpe (₡)</label>
                                <input type="number" class="form-control" step="any" id="monto_sinpe"
                                    name="monto_sinpe" placeholder="0.00" onkeyup="enterCampoPago(event)"
                                    min="0">
                                <button type="button" class="btn btn-primary btn-block mt-2" id="btnPagoSinpe"
                                    onclick="verificarAbrirModalPagoSinpe()">
                                    Pagar Todo con Sinpe
                                </button>
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
                                <h6 id="txt-mto-envio_mdl" class="text-muted">Envío: No aplica</h6>
                            </div>
                            <div class="col-6 col-md-3">
                                <h6 id="txt-mto-pagado_mdl" class="text-muted">Monto Pagado: 0,00</h6>
                            </div>
                            
                        </div>

                        <!-- Total seleccionado -->
                        <div class="row mb-3">
                            <div class="col-12 text-center">
                                <h4 id="txt-total-seleccionado" class="text-muted">Total Seleccionado a Pagar: 0,00</h4>
                            </div>
                        </div>

                        <!-- Opciones de selección de líneas -->
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
                                            <!-- Los detalles se llenarán dinámicamente aquí -->
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

    <div class="modal fade bd-example-modal-lg" id='mdl-ordenes' role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="" id="input_buscar_generico" class="form-control"
                                style="width: 80%;" placeholder="Buscar..">
                        </div>
                    </form>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="width: 100%;">
                    <div class="table-responsive">
                        <table class="table" id="tbl-ordenes" style="max-height: 100%;">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" style="text-align: center;">No.Orden</th>
                                    <th scope="col" style="text-align: center;">Mesa</th>
                                    <th scope="col" style="text-align: center;">Fecha</th>
                                    <th scope="col" style="text-align: center;">Cliente</th>
                                    <th scope="col" style="text-align: center;">Estado</th>
                                    <th scope="col" style="text-align: center;">Estado Pago</th>
                                    <th scope="col" style="text-align: center;">Total Pago</th>
                                    <th scope="col" style="text-align: center;">Pagado</th>
                                    <th scope="col" style="text-align: center;">Pendiente</th>
                                    <th scope="col" style="text-align: center;">Tiquete</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-ordenes">
                                <!-- Los datos de las órdenes se llenarán dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="form-group">
                        <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarMdlOrdenes()'
                            style="cursor: pointer;">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- modal modal -->
    <div class="modal fade bs-example-modal-center" id='mdl_envio' role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-truck"></i> Información de Envío
                    </h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        onclick="cerrarModalEnvio()">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label for="incluyeEnvio">Incluye envío: </label>
                                    <input type="checkbox" id="incluyeEnvio">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Precio Envío</label>
                                    <input type="number" class="form-control space_input_modal" id="mdl_precio_envio"
                                        name="mdl_precio_envio">
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Contacto de entrega</label>
                                    <input type="text" class="form-control space_input_modal"
                                        id="mdl_contacto_entrega" name="mdl_contacto_entrega" maxlength="500">

                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Descripción Lugar Entrega</label>
                                    <textarea class="form-control" name="mdl_lugar_entrega" id="mdl_lugar_entrega" maxlength="2000"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">URL Lugar Entrega (MAPS)</label>
                                    <textarea class="form-control" name="mdl_lugar_entrega_maps" id="mdl_lugar_entrega_maps" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                    <a href="#" class="btn btn-secondary" onclick="cerrarModalEnvio()">Volver</a>
                    <a href="#" class="btn btn-primary" onclick="guardarInfoEnvio()">Guardar</a>
                </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal ---->

    <div class="modal fade bs-example-modal-center" id='mdl_fe' role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-truck"></i> Información de
                        Facturación Electrónica
                    </h5>
                    <button type="button" id='btnSalirFac' class="close" aria-hidden="true"
                        onclick="cerrarModalFe()">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label for="incluyeEnvio">Incluye factura electrónica : </label>
                                    <input type="checkbox" id="incluyeFE">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cédula cliente</label>
                                    <input type="text" class="form-control space_input_modal" id="info_ced_fe"
                                        name="info_ced_fe" maxlength="25">
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Nombre cliente</label>
                                    <input type="text" class="form-control space_input_modal" id="info_nombre_fe"
                                        name="info_nombre_fe" maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Correo cliente</label>
                                    <input type="text" class="form-control space_input_modal" id="info_correo_fe"
                                        name="info_correo_fe" maxlength="250">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                    <a href="#" class="btn btn-secondary" onclick="cerrarModalFE()">Volver</a>
                    <a href="#" class="btn btn-primary" onclick="guardarInfoFE()">Guardar</a>
                </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal ---->


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
                                        <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                            <div class="col-xs-4 col-md-4 col-lg-4">
                                                <p class="font-20"
                                                    style="font-size:12px;color: black;  text-align: left;">
                                                    Efectivo</p>
                                            </div>
                                            <div class="col-xs-8 col-md-8 col-lg-8">
                                                <p class="font-20" style="color: black; text-align: right;"
                                                    id="monto_efectivo_lbl">CRC <strong>
                                                        {{ number_format('0.00', 2, '.', ',') }}</strong></p>
                                            </div>

                                        </div>
                                        <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                            <div class="col-xs-4 col-md-4 col-lg-4">
                                                <p class="font-20" style="font-size:12px;color: black; text-align: left;">
                                                    Tarjetas
                                                </p>
                                            </div>
                                            <div class="col-xs-8 col-md-8 col-lg-8">
                                                <p class="font-20" style="color: black; text-align: right;"
                                                    id="monto_tarjetas_lbl">CRC <strong>
                                                        {{ number_format('0.00', 2, '.', ',') }}</strong></p>
                                            </div>

                                        </div>
                                        <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                            <div class="col-xs-4 col-md-4 col-lg-4">
                                                <p class="font-20"
                                                    style="font-size:12px;color: black;  text-align: left;">SINPE
                                                </p>
                                            </div>
                                            <div class="col-xs-8 col-md-8 col-lg-8">
                                                <p class="font-20" style="color: black;text-align: right;"
                                                    id="monto_sinpe_lbl">CRC <strong>
                                                        {{ number_format('0.00', 2, '.', ',') }}</strong></p>
                                            </div>

                                        </div>



                                        <div class="row" style="border-bottom: dotted 1px black; margin-top:15px;">
                                            <div class="col-xs-4 col-md-4 col-lg-4">
                                                <p class="font-20" style="font-size:12px;color: black; text-align: left;">
                                                    Total
                                                </p>
                                            </div>
                                            <div class="col-xs-8 col-md-8 col-lg-8">
                                                <p class="font-20" style="color: black; text-align: right;"
                                                    id="monto_total_lbl">CRC <strong>
                                                        {{ number_format('0.00', 2, '.', ',') }}</strong></p>
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
@endsection
@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

    <script src="{{ asset('assets/js/facturacion/pos.js') }}"></script>
@endsection
