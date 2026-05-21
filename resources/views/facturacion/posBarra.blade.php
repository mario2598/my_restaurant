@extends('layout.master')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pos-barra.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mesa-plano-visual.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pos-plano-mesas.css') }}">
@endsection

@section('content')
@include('layout.sidebar')

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
        "cuenta_barra_id": null,
        "numero_orden": "",
        "mto_pagado": 0,
        "pagado": false,
        "idCliente": -1,
        "incidentes": [],
        "totalRebajarIncidentes": 0
    };
    var sucursalFacturaIva = "{{ $data['sucursalFacturaIva'] ?? false }}";
    var cajaAbierta = "{{ $data['cajaAbierta'] ?? false }}";
    window.POS_CONFIG = { modo: 'barra', clienteOpcional: true };
</script>

<div class="main-content pos-barra-page">
    <section class="section pos-barra-section">
        <div class="section-body pos-barra-body">
            <div id="pos-barra-app" class="pos-barra-app">
                {{-- Cuentas --}}
                <aside class="pos-barra-panel pos-barra-panel--cuentas" id="pos-barra-cuentas-col">
                    <div class="pos-barra-panel__head">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="pos-barra-panel__title"><i class="fas fa-receipt mr-1"></i> Cuentas</span>
                            <button type="button" class="btn btn-icon btn-sm btn-light" onclick="cargarCuentasBarra()" title="Actualizar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="pos-barra-panel__body" id="lista-cuentas-barra">
                        <p class="text-muted small mb-0 px-2">Cargando…</p>
                    </div>
                    <div class="pos-barra-panel__foot">
                        <button type="button" class="btn btn-primary btn-block btn-sm" onclick="promptNuevaCuentaBarra()">
                            <i class="fas fa-plus"></i> Nueva cuenta
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-block btn-sm mt-2" onclick="abrirMapaMesas('ordenes')">
                            <i class="fas fa-map"></i> Mapa por mesa
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-block btn-sm mt-1" onclick="abrirMapaMesas('generales')">
                            <i class="fas fa-list"></i> Órdenes generales
                        </button>
                    </div>
                    <div id="contAbrirCaja" class="pos-barra-caja-cerrada" style="display:none;">
                        <button type="button" class="btn btn-success btn-block btn-sm" onclick="abrirCaja()">Abrir caja</button>
                    </div>
                </aside>

                {{-- Catálogo --}}
                <main class="pos-barra-panel pos-barra-panel--productos" id="contEscogerProductos">
                    <div class="pos-barra-panel__head pos-barra-panel__head--compact">
                        <ul class="nav nav-pills pos-barra-tipos flex-nowrap" id="nv-tipos"></ul>
                    </div>
                    <div class="pos-barra-buscar">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" id="buscador-productos"
                                placeholder="Buscar o código de barras…"
                                onkeyup="filtrarProductosBarra(this.value)"
                                onkeydown="keydownBuscadorBarra(event)">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusquedaProductos()" id="btn-limpiar-busqueda" style="display:none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="pos-barra-categorias-wrap">
                        <ul id="scrl-categorias" class="nav nav-pills pos-barra-categorias flex-nowrap"></ul>
                    </div>
                    <div id="scrl-productos" class="pos-barra-panel__body pos-barra-grid-wrap">
                        <div id="pos-barra-productos-grid" class="pos-barra-productos-grid"></div>
                        <table class="d-none"><tbody id="tbody-productos"></tbody></table>
                    </div>
                </main>

                {{-- Orden / cobro --}}
                <aside class="pos-barra-panel pos-barra-panel--orden" id="contDetalles">
                    <div class="pos-barra-panel__head">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="min-w-0 flex-grow-1 pr-2">
                                <h6 class="mb-0 font-weight-bold text-truncate" id="infoHeaderOrden">Sin cuenta</h6>
                                <small class="text-muted d-block text-truncate" id="pos-barra-cuenta-label">Abra o seleccione una cuenta</small>
                            </div>
                            <div class="btn-group btn-group-sm flex-shrink-0">
                                <button type="button" class="btn btn-light" onclick="recargarOrdenesBarra()" title="Órdenes de caja">
                                    <i class="fas fa-list"></i>
                                </button>
                                <button type="button" class="btn btn-light text-danger" onclick="abrirModalCerrarCaja()" id="contCerrarCaja" title="Cerrar caja">
                                    <i class="fas fa-cash-register"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row no-gutters mt-2">
                            <div class="col-7 pr-1">
                                <input type="text" class="form-control form-control-sm" id="txt-cliente" placeholder="Etiqueta / cliente"
                                    onkeyup="changeNombreCliente(this.value,true)">
                            </div>
                            <div class="col-5 pl-1">
                                <select class="form-control form-control-sm" id="select_mesa" name="select_mesa" onchange="cambiarMesa()">
                                    <option value="-1">Sin mesa</option>
                                    @foreach ($data['mesas'] as $i)
                                    <option value="{{ $i->id ?? '' }}">Mesa {{ $i->numero_mesa ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pos-barra-total-strip">
                        <span class="pos-barra-total-label">Total</span>
                        <span class="pos-barra-total" id="txt-total-pagar">₡ 0,00</span>
                    </div>

                    <div class="pos-barra-panel__body pos-barra-orden-lines">
                        <table class="table table-sm table-hover mb-0 pos-barra-tabla-orden">
                            <thead>
                                <tr><th>Producto</th><th class="text-center" style="width:4rem">Cant.</th><th style="width:2rem"></th></tr>
                            </thead>
                            <tbody id="tbody-orden"></tbody>
                        </table>
                    </div>

                    <div class="pos-barra-panel__foot pos-barra-acciones">
                        <div class="row no-gutters">
                            <div class="col-6 pr-1">
                                <button type="button" class="btn btn-warning btn-block" id="btnIniciarOrden" onclick="iniciarOrdenBarra()">
                                    <i class="fas fa-paper-plane"></i> Enviar
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-block btn-sm mt-1 d-none" id="btnActualizarOrden" onclick="actualizarOrdenGestion()">
                                    Guardar
                                </button>
                            </div>
                            <div class="col-6 pl-1">
                                <button type="button" class="btn btn-primary btn-block" id="btnPagoBarra" onclick="abrirModalPagoBarra()">
                                    <i class="fas fa-money-bill-wave"></i> Cobrar
                                </button>
                                <button type="button" class="btn btn-success btn-block mt-1" onclick="pagoRapidoEfectivoBarra()">
                                    <i class="fas fa-bolt"></i> Efectivo
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-link btn-sm btn-block mt-2 text-muted" onclick="limpiarOrdenBarra()">
                            <i class="fas fa-broom"></i> Limpiar líneas
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>

<a href="" target="_blank" class="btn btn-primary" id="btn-pdf" style="display:none"></a>

<div class="modal fade" id="mdl-ordenes" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">Órdenes de la caja</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-2" style="max-height:70vh;overflow:auto">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Orden</th><th>Mesa</th><th>Estado</th><th>Cliente</th><th></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-ordenes"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('popup')
@include('facturacion.partials.pos-modales-core', ['data' => $data])
@endsection

@section('script')
<script src="{{ asset('assets/js/facturacion/pos.js') }}"></script>
<script src="{{ asset('assets/js/mobiliario/mesa-plano-utils.js') }}"></script>
<script src="{{ asset('assets/js/facturacion/pos-plano-mesas.js') }}"></script>
<script src="{{ asset('assets/js/facturacion/pos-barra.js') }}"></script>
@endsection
