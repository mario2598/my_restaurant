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
        "pagado": false,
        "idCliente": -1
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

    /* Estilos para los paneles de información del cliente */
    #cliente-info-panel,
    #cliente-info-panel-2 {
        animation: slideInDown 0.3s ease-out;
        margin-top: 0.5rem;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #cliente-info-panel .alert,
    #cliente-info-panel-2 .alert {
        border-radius: 0.5rem;
        font-size: 0.85rem;
        padding: 0.75rem 1rem;
        margin-bottom: 0;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 2px solid #28a745;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
        position: relative;
        overflow: hidden;
    }

    #cliente-info-panel .alert::before,
    #cliente-info-panel-2 .alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
    }

    #cliente-info-panel .btn-outline-danger,
    #cliente-info-panel-2 .btn-outline-danger {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
        border-width: 1.5px;
    }

    #cliente-info-panel .btn-outline-danger:hover,
    #cliente-info-panel-2 .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }

    #cliente-info-panel .badge,
    #cliente-info-panel-2 .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 0.5rem;
        font-weight: 500;
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: 1px solid #117a8b;
    }

    /* Estilos para los iconos */
    #cliente-info-panel .fas,
    #cliente-info-panel-2 .fas {
        width: 16px;
        text-align: center;
        margin-right: 0.5rem;
    }

    /* Estilos para el texto de información */
    #cliente-info-panel .text-muted,
    #cliente-info-panel-2 .text-muted {
        color: #6c757d !important;
        font-weight: 500;
    }

    #cliente-info-panel strong,
    #cliente-info-panel-2 strong {
        color: #155724;
        font-weight: 600;
    }

    /* Estilos para los separadores */
    #cliente-info-panel .mx-2,
    #cliente-info-panel-2 .mx-2 {
        color: #adb5bd;
        font-weight: bold;
    }

    /* Estilos para los detalles extra */
    #cliente-info-panel #cliente-detalles-extra,
    #cliente-info-panel-2 #cliente-detalles-extra {
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 0.375rem;
        padding: 0.5rem;
        margin-top: 0.5rem;
        border: 1px solid rgba(40, 167, 69, 0.2);
    }

    /* Estilos para la información de FE */
    #cliente-info-panel #cliente-fe-info,
    #cliente-info-panel-2 #cliente-fe-info {
        margin-top: 0.5rem;
    }

    #cliente-info-panel #cliente-fe-info .badge,
    #cliente-info-panel-2 #cliente-fe-info .badge {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: 1px solid #117a8b;
        box-shadow: 0 1px 3px rgba(23, 162, 184, 0.3);
    }

    /* Estilos para el botón de FE clickeable */
    #cliente-fe-info-2 {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        border: 1px solid #117a8b !important;
        box-shadow: 0 1px 3px rgba(23, 162, 184, 0.3) !important;
        color: white !important;
        transition: all 0.2s ease !important;
        border-radius: 0.5rem !important;
        font-weight: 500 !important;
    }

    #cliente-fe-info-2:hover {
        background: linear-gradient(135deg, #138496 0%, #117a8b 100%) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 6px rgba(23, 162, 184, 0.4) !important;
        color: white !important;
    }

    #cliente-fe-info-2:focus {
        outline: none !important;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25) !important;
    }

    /* Estilos para el botón de FE cuando está configurado (verde) */
    #cliente-fe-info-2.badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        border: 1px solid #1e7e34 !important;
        box-shadow: 0 1px 3px rgba(40, 167, 69, 0.3) !important;
    }

    #cliente-fe-info-2.badge-success:hover {
        background: linear-gradient(135deg, #1e7e34 0%, #155724 100%) !important;
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.4) !important;
    }

    /* Estilos para el botón de FE cuando no está configurado (amarillo) */
    #cliente-fe-info-2.badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
        border: 1px solid #d39e00 !important;
        box-shadow: 0 1px 3px rgba(255, 193, 7, 0.3) !important;
        color: #212529 !important;
    }

    #cliente-fe-info-2.badge-warning:hover {
        background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%) !important;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4) !important;
        color: #212529 !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {

        #cliente-info-panel .d-flex,
        #cliente-info-panel-2 .d-flex {
            flex-direction: column;
            align-items: flex-start;
        }

        #cliente-info-panel .d-flex>div:first-child,
        #cliente-info-panel-2 .d-flex>div:first-child {
            margin-bottom: 0.5rem;
        }

        #cliente-info-panel .alert,
        #cliente-info-panel-2 .alert {
            padding: 0.6rem 0.8rem;
            font-size: 0.8rem;
        }
    }

    /* Efecto hover para el panel completo */
    #cliente-info-panel:hover,
    #cliente-info-panel-2:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }

    /* Estilos para el input deshabilitado cuando hay cliente seleccionado */
    #txt-cliente:disabled {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        cursor: not-allowed !important;
        border-color: #dee2e6 !important;
        opacity: 0.8;
    }

    #txt-cliente:disabled::placeholder {
        color: #adb5bd !important;
    }

    /* Estilos para el input habilitado */
    #txt-cliente:not(:disabled) {
        background-color: #fff !important;
        color: #495057 !important;
        cursor: text !important;
        border-color: #ced4da !important;
        opacity: 1;
    }

    /* Estilos para el input del modal de pago deshabilitado */
    #nombreCliente:disabled {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        cursor: not-allowed !important;
        border-color: #dee2e6 !important;
        opacity: 0.8;
    }

    #nombreCliente:disabled::placeholder {
        color: #adb5bd !important;
    }

    /* Estilos para el input del modal de pago habilitado */
    #nombreCliente:not(:disabled) {
        background-color: #fff !important;
        color: #495057 !important;
        cursor: text !important;
        border-color: #ced4da !important;
        opacity: 1;
    }

    /* Estilos para campos deshabilitados del modal de FE (excluyendo el checkbox principal) */
    #mdl_fe input:disabled:not(#incluyeFE) {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        cursor: not-allowed !important;
        border-color: #dee2e6 !important;
        opacity: 0.7 !important;
    }

    /* Estilos específicos para el checkbox de FE cuando está habilitado */
    #incluyeFE {
        cursor: pointer !important;
        opacity: 1 !important;
    }

    #mdl_fe input:disabled::placeholder {
        color: #adb5bd !important;
    }

    /* Estilos para el mensaje de FE bloqueado */
    #mensaje-fe-bloqueado {
        border-left: 4px solid #ffc107;
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    #mensaje-fe-bloqueado .fas {
        color: #ffc107;
    }

    /* Estilos para el título del modal bloqueado */
    #edit_cliente_text {
        transition: color 0.3s ease;
    }

    /* Estilos para el botón de FE deshabilitado */
    #btn_fe:disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        pointer-events: none;
    }

    #btn_fe:disabled:hover {
        transform: none !important;
        box-shadow: none !important;
    }

    /* Estilos para el botón de nuevo cliente en el modal de búsqueda */
    .btn-nuevo-cliente {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: 1px solid #28a745;
        color: white;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-nuevo-cliente:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        border-color: #1e7e34;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .btn-nuevo-cliente:focus {
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    /* Estilos para el buscador de productos */
    #buscador-productos {
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    #buscador-productos:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }

    #buscador-productos::placeholder {
        color: #adb5bd;
        font-style: italic;
    }

    #btn-limpiar-busqueda {
        transition: all 0.2s ease;
    }

    #btn-limpiar-busqueda:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    /* Animación suave para el buscador */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-header:has(#buscador-productos) {
        animation: fadeIn 0.3s ease-out;
    }

    /* Responsive para el buscador */
    @media (max-width: 768px) {
        #buscador-productos {
            font-size: 0.9rem;
        }

        #buscador-productos::placeholder {
            font-size: 0.85rem;
        }
    }

    /* Estilo para cuando hay búsqueda activa */
    .busqueda-activa {
        background-color: #fff3cd !important;
        border-color: #ffc107 !important;
    }

    /* Estilos para el componente flotante de gestión de mesas */
    #mesas-flotante-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        transition: all 0.3s ease;
    }

    #mesas-flotante-container.colapsado {
        bottom: 20px;
        right: 20px;
    }

    #btn-toggle-mesas {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-size: 24px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #btn-toggle-mesas:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    }

    #panel-mesas {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 350px;
        max-height: 500px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }

    #panel-mesas.mostrar {
        display: flex;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #panel-mesas .panel-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 10px 10px 0 0;
    }

    #panel-mesas .panel-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    #panel-mesas .panel-body {
        padding: 15px;
        overflow-y: auto;
        max-height: 400px;
        flex: 1;
    }

    #panel-mesas .mesa-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        margin-bottom: 10px;
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.2s ease;
        background: #f8f9fa;
    }

    #panel-mesas .mesa-item:hover {
        border-color: #667eea;
        background: #f0f4ff;
        transform: translateX(-5px);
    }

    #panel-mesas .mesa-item.disponible {
        border-left: 4px solid #28a745;
    }

    #panel-mesas .mesa-item.ocupada {
        border-left: 4px solid #dc3545;
    }

    #panel-mesas .mesa-info {
        flex: 1;
    }

    #panel-mesas .mesa-info .mesa-numero {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin-bottom: 4px;
    }

    #panel-mesas .mesa-info .mesa-details {
        font-size: 12px;
        color: #6c757d;
    }

    #panel-mesas .mesa-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    #panel-mesas .mesa-badge.disponible {
        background-color: #d4edda;
        color: #155724;
    }

    #panel-mesas .mesa-badge.ocupada {
        background-color: #f8d7da;
        color: #721c24;
    }

    #panel-mesas .mesa-acciones {
        display: flex;
        gap: 8px;
    }

    #panel-mesas .btn-mesa {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #panel-mesas .btn-mesa:hover {
        transform: scale(1.05);
    }

    #panel-mesas .btn-disponible {
        background-color: #28a745;
        color: white;
    }

    #panel-mesas .btn-disponible:hover {
        background-color: #218838;
    }

    #panel-mesas .btn-ocupada {
        background-color: #dc3545;
        color: white;
    }

    #panel-mesas .btn-ocupada:hover {
        background-color: #c82333;
    }

    #panel-mesas .btn-recargar {
        width: 100%;
        margin-top: 10px;
        padding: 8px;
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    #panel-mesas .btn-recargar:hover {
        background-color: #5a6268;
    }

    #panel-mesas .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    #panel-mesas .empty-state i {
        font-size: 48px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        #panel-mesas {
            width: calc(100vw - 40px);
            max-width: 350px;
            right: -10px;
        }

        #mesas-flotante-container {
            bottom: 15px;
            right: 15px;
        }
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
                                    <!-- Buscador de Productos -->
                                    <div class="card-header col-12" style="padding: 10px !important; border-bottom: 1px solid #dee2e6;">
                                        <div class="input-group">
                                        
                                            <input type="text" 
                                                class="form-control" 
                                                id="buscador-productos" 
                                                placeholder="Buscar producto por nombre, código o categoría..."
                                                style="border-left: none;"
                                                onkeyup="filtrarProductos(this.value)"
                                                onfocus="this.select()">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" 
                                                    type="button" 
                                                    onclick="limpiarBusquedaProductos()"
                                                    id="btn-limpiar-busqueda"
                                                    style="display: none;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
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
                                                            <input type="text" class="form-control h-75" onkeyup="changeNombreCliente(this.value,true)"
                                                                name="txt-cliente" id="txt-cliente"
                                                                placeholder="Nombre cliente...">
                                                            <div class="input-group-append">
                                                                <button class="btn btn-outline-primary" type="button"
                                                                    onclick="abrirModalBuscarCliente()" title="Buscar Cliente">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
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
                                                    <div class="col-12">
                                                        <!-- Panel compacto de información del cliente seleccionado -->
                                                        <div id="cliente-info-panel-2" class="mt-1" style="display: none;">
                                                            <div class="alert alert-success py-2 mb-0">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fas fa-user-check text-success mr-2"></i>
                                                                        <small class="text-muted">Cliente:</small>
                                                                        <strong class="ml-1" id="cliente-nombre-info-2">-</strong>
                                                                        <span class="mx-2 text-muted">|</span>
                                                                        <small class="text-muted">Tel:</small>
                                                                        <span class="ml-1" id="cliente-telefono-info-2">-</span>
                                                                    </div>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                                        onclick="limpiarClienteSeleccionado()" title="Limpiar Cliente">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>

                                                            </div>
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
                                                                onclick="actualizarOrdenGestion()">Guardar Modificaciones<i class="fas fa-update"
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
                        <!-- Panel compacto de información del cliente seleccionado -->
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
                                        <i class="fas fa-file-invoice mr-1"></i> Factura Electrónica Disponible
                                    </button>
                                </div>
                            </div>
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
                            <button class="btn btn-success btn-block" id="btn_fe" onclick="changeFacturacionElectronica()">
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
                                <th scope="col" style="text-align: center;">Estado Pago</th>
                                <th scope="col" style="text-align: center;">Fecha</th>
                                <th scope="col" style="text-align: center;">Cliente</th>
                                <th scope="col" style="text-align: center;">Estado</th>
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
                                <label class="form-label">Código de Actividad</label>
                                <input type="text" class="form-control space_input_modal" id="codigo_actividad"
                                    name="codigo_actividad" maxlength="10" placeholder="Ej: 722003">
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12 col-sm-12">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <label class="form-label">Tipo de Identificación</label>
                                <select class="form-control space_input_modal" id="tipo_identificacion" name="tipo_identificacion">
                                    <option value="01">Cédula Física</option>
                                    <option value="02">Cédula Jurídica</option>
                                    <option value="03">DIMEX</option>
                                    <option value="04">NITE</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12 col-sm-12">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <label class="form-label">Número de Identificación</label>
                                <input type="text" class="form-control space_input_modal" id="numero_identificacion"
                                    name="numero_identificacion" maxlength="25">
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12 col-sm-12">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <label class="form-label">Nombre Comercial</label>
                                <input type="text" class="form-control space_input_modal" id="nombre_comercial"
                                    name="nombre_comercial" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12 col-sm-12">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <label class="form-label">Dirección</label>
                                <textarea class="form-control space_input_modal" id="direccion"
                                    name="direccion" maxlength="200" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12 col-sm-12">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control space_input_modal" id="correo_electronico"
                                    name="correo_electronico" maxlength="250">
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

<!-- Modal de búsqueda de clientes -->
<div class="modal fade" id="mdl-buscar-cliente" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="width: 100%">
                <h4 class="modal-title">Buscar Cliente</h4>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-nuevo-cliente btn-sm mr-2" onclick="abrirModalNuevoClienteDesdeBusqueda()" title="Crear Nuevo Cliente">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </button>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body" style="width: 100%;">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="input-group">
                            <input type="text" class="form-control" id="txt-buscar-cliente"
                                placeholder="Buscar por nombre, apellidos, teléfono, correo o ubicación..."
                                onkeyup="buscarClientesConDebounce(this.value)">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" onclick="buscarClientesConDebounce(document.getElementById('txt-buscar-cliente').value)">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="tbl-clientes">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Ubicación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-clientes">
                            <!-- Los resultados se cargarán aquí -->
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div id="pagination-container" class="d-flex justify-content-between align-items-center mt-3" style="display: none;">
                    <div class="pagination-info">
                        <small class="text-muted" id="pagination-info">Mostrando 0 de 0 clientes</small>
                    </div>
                    <div class="pagination-controls">
                        <button class="btn btn-sm btn-outline-primary" id="btn-prev" onclick="cambiarPagina(-1)" disabled>
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        <span class="mx-2" id="page-info">Página 1 de 1</span>
                        <button class="btn btn-sm btn-outline-primary" id="btn-next" onclick="cambiarPagina(1)" disabled>
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div id="no-resultados" class="text-center text-muted" style="display: none;">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>No se encontraron clientes</p>
                </div>

                <!-- Loading spinner -->
                <div id="loading-clientes" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2">Buscando clientes...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                                    <div class="row"
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
                                <label for="telefono_cliente">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correo_cliente">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo_cliente" name="correo_cliente">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="ubicacion_cliente">Ubicación</label>
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

<!-- Componente flotante para gestión de mesas -->
<div id="mesas-flotante-container">
    <!-- Panel de mesas -->
    <div id="panel-mesas">
        <div class="panel-header">
            <h5><i class="fas fa-table"></i> Gestión de Mesas</h5>
            <button type="button" class="btn btn-sm text-white" onclick="togglePanelMesas()" style="background: rgba(255,255,255,0.2); border: none;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="panel-body" id="panel-mesas-body">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando mesas...</p>
            </div>
        </div>
        <div style="padding: 10px 15px; border-top: 1px solid #dee2e6;">
            <button type="button" class="btn-recargar" onclick="cargarMesas()">
                <i class="fas fa-sync-alt"></i> Recargar
            </button>
        </div>
    </div>
    
    <!-- Botón flotante para abrir/cerrar -->
    <button id="btn-toggle-mesas" onclick="togglePanelMesas()" title="Gestión de Mesas">
        <i class="fas fa-table" id="icon-toggle-mesas"></i>
    </button>
</div>

@endsection
@section('script')
<script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/page/datatables.js') }}"></script>

<script src="{{ asset('assets/js/facturacion/pos.js') }}"></script>

<script>
    // Variable para almacenar el estado de búsqueda
    var busquedaActiva = false;
    var productosFiltrados = [];

    /**
     * Filtra los productos según el término de búsqueda
     */
    function filtrarProductos(termino) {
        termino = termino.trim().toLowerCase();
        
        // Mostrar/ocultar botón de limpiar
        if (termino.length > 0) {
            $('#btn-limpiar-busqueda').show();
            busquedaActiva = true;
            $('#buscador-productos').addClass('busqueda-activa');
        } else {
            $('#btn-limpiar-busqueda').hide();
            busquedaActiva = false;
            $('#buscador-productos').removeClass('busqueda-activa');
            // Restaurar vista normal
            generarProductos();
            return;
        }

        // Filtrar productos de todos los tipos y categorías
        productosFiltrados = [];
        
        if (typeof productosGeneral !== 'undefined' && productosGeneral.length > 0) {
            productosFiltrados = productosGeneral.filter(function(producto) {
                var nombre = (producto.nombre || '').toLowerCase();
                var codigo = (producto.codigo || '').toLowerCase();
                var categoria = (producto.categoria || '').toLowerCase();
                return nombre.includes(termino) || codigo.includes(termino) || categoria.includes(termino);
            });
        }

        // Mostrar productos filtrados
        mostrarProductosFiltrados();
    }

    /**
     * Muestra los productos filtrados en la tabla
     */
    function mostrarProductosFiltrados() {
        var html = '';
        
        if (productosFiltrados.length === 0) {
            html = '<tr><td colspan="2" class="text-center text-muted"><i class="fas fa-search"></i> No se encontraron productos</td></tr>';
        } else {
            productosFiltrados.forEach(function(producto) {
                html += generarHTMLProducto(
                    producto.nombre, 
                    producto.codigo, 
                    producto.precio, 
                    producto.cantidad, 
                    producto.tipoProducto
                );
            });
        }

        $('#tbody-productos').html(html);
    }

    /**
     * Limpia la búsqueda y restaura la vista normal
     */
    function limpiarBusquedaProductos() {
        $('#buscador-productos').val('');
        $('#btn-limpiar-busqueda').hide();
        busquedaActiva = false;
        $('#buscador-productos').removeClass('busqueda-activa');
        generarProductos();
    }

    // Atajo de teclado: ESC para limpiar búsqueda
    $(document).ready(function() {
        $('#buscador-productos').on('keydown', function(e) {
            if (e.key === 'Escape') {
                limpiarBusquedaProductos();
                $(this).blur();
            }
        });

        // Ocultar botón de limpiar inicialmente
        $('#btn-limpiar-busqueda').hide();
    });

    // Funciones para el componente flotante de gestión de mesas
    function togglePanelMesas() {
        const panel = document.getElementById('panel-mesas');
        const icon = document.getElementById('icon-toggle-mesas');
        
        if (panel.classList.contains('mostrar')) {
            panel.classList.remove('mostrar');
            icon.className = 'fas fa-table';
        } else {
            panel.classList.add('mostrar');
            icon.className = 'fas fa-chevron-down';
            if (document.getElementById('panel-mesas-body').innerHTML.includes('Cargando mesas')) {
                cargarMesas();
            }
        }
    }

    function cargarMesas() {
        const panelBody = document.getElementById('panel-mesas-body');
        panelBody.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Cargando mesas...</p></div>';

        $.ajax({
            url: `${base_path}/facturacion/mesas/obtener-mesas`,
            type: 'POST',
            dataType: 'json',
            data: {
                _token: CSRF_TOKEN
            },
            success: function(response) {
                if (response.estado && response.datos) {
                    mostrarMesas(response.datos);
                } else {
                    panelBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar las mesas</p></div>';
                }
            },
            error: function() {
                panelBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error de conexión</p></div>';
            }
        });
    }

    function mostrarMesas(mesas) {
        const panelBody = document.getElementById('panel-mesas-body');
        
        if (!mesas || mesas.length === 0) {
            panelBody.innerHTML = '<div class="empty-state"><i class="fas fa-table"></i><p>No hay mesas disponibles</p></div>';
            return;
        }

        let html = '';
        mesas.forEach(function(mesa) {
            const esDisponible = mesa.estado_codigo === 'MESA_DISPONIBLE';
            const estadoClass = esDisponible ? 'disponible' : 'ocupada';
            const estadoTexto = esDisponible ? 'Disponible' : 'Ocupada';
            const btnAccionTexto = esDisponible ? 'Marcar Ocupada' : 'Marcar Disponible';
            const btnAccionClass = esDisponible ? 'btn-ocupada' : 'btn-disponible';
            const nuevoEstado = esDisponible ? 'MESA_OCUPADA' : 'MESA_DISPONIBLE';

            html += `
                <div class="mesa-item ${estadoClass}">
                    <div class="mesa-info">
                        <div class="mesa-numero">Mesa ${mesa.numero_mesa}</div>
                        <div class="mesa-details">Capacidad: ${mesa.capacidad || 'N/A'} personas</div>
                    </div>
                    <div class="mesa-badge ${estadoClass}">${estadoTexto}</div>
                    <div class="mesa-acciones">
                        <button class="btn-mesa ${btnAccionClass}" onclick="cambiarEstadoMesa(${mesa.id}, '${nuevoEstado}')" title="${btnAccionTexto}">
                            <i class="fas ${esDisponible ? 'fa-lock' : 'fa-unlock'}"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        panelBody.innerHTML = html;
    }

    function cambiarEstadoMesa(idMesa, nuevoEstado) {
        if (!confirm(`¿Está seguro de cambiar el estado de esta mesa?`)) {
            return;
        }

        $.ajax({
            url: `${base_path}/facturacion/mesas/cambiar-estado`,
            type: 'POST',
            dataType: 'json',
            data: {
                _token: CSRF_TOKEN,
                id_mesa: idMesa,
                estado: nuevoEstado
            },
            success: function(response) {
                if (response.estado) {
                    showSuccess(response.mensaje || 'Estado actualizado correctamente');
                    cargarMesas(); // Recargar las mesas
                } else {
                    showError(response.mensaje || 'Error al cambiar el estado');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let mensajeError = "Error al cambiar el estado de la mesa";
                if (jqXHR.responseJSON && jqXHR.responseJSON.mensaje) {
                    mensajeError = jqXHR.responseJSON.mensaje;
                }
                showError(mensajeError);
            }
        });
    }

    // Cargar mesas automáticamente cuando se muestra el panel por primera vez
    document.addEventListener('DOMContentLoaded', function() {
        // Opcional: cargar mesas después de 1 segundo de carga la página
        // setTimeout(cargarMesas, 1000);
    });
</script>
@endsection