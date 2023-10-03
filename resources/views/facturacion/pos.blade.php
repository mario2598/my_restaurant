@extends('layout.master')

@section('content')
    @include('layout.sidebar')
    <!-- Listas de productos -->
    <script>
        var tipos = []; // Se crea la lista  de tipos de productos
        var productosGeneral = [];
        var ordenGestion = {
            "id": null,
            "cliente": "",
            "nueva": true,
            "total": 0,
            "subTotal": 0,
            "codigoPromocion": "",
            "codigo_descuento": null
        };
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

    @foreach ($data['tipos'] as $tipo)
        <script>
            var categorias = [];
            var auxProducto;
        </script>

        @foreach ($tipo['categorias'] as $categoria)
            <script>
                var productos = [];
            </script>

            @foreach ($categoria->productos as $producto)
                <script>
                    var extrasAux = [];
                </script>

                @foreach ($producto->extras ?? [] as $extra)
                    <script>
                        var extraAuxL = [];
                    </script>

                    @foreach ($extra['extras'] ?? [] as $extra1)
                        <script>
                            var extraAux = {
                                "id": "{{ $extra1->id }}",
                                "descripcion": "{{ $extra1->descripcion ?? '' }}",
                                "precio": "{{ $extra1->precio ?? 0 }}",
                                "grupo": "{{ $extra1->dsc_grupo ?? '' }}",
                                "requerido": "{{ $extra1->es_requerido ?? '' }}",
                                "seleccionado": false
                            };
                            extraAuxL.push(extraAux);
                        </script>
                    @endforeach

                    <script>
                        var extraAux1 = {
                            "dsc_grupo": "{{ $extra['grupo'] ?? '' }}",
                            "requerido": "{{ $extra['requerido'] ?? 0 }}",
                            "multiple": "{{ $extra['multiple'] ?? 0 }}",
                            "extras": extraAuxL
                        };
                        extrasAux.push(extraAux1);
                    </script>
                @endforeach
                <script>
                    auxProducto = {
                        "id": "{{ $producto->id }}",
                        "nombre": "{{ $producto->nombre ?? '' }}",
                        "impuesto": "{{ $producto->impuesto ?? 0 }}",
                        "precio": "{{ $producto->precio ?? 0 }}",
                        "codigo": "{{ $producto->codigo ?? '' }}",
                        "tipoComanda": "{{ $producto->tipo_comanda ?? '' }}",
                        "cantidad": "{{ $producto->cantidad ?? -1 }}",
                        "cantidad_original": "{{ $producto->cantidad ?? -1 }}",
                        "tipoProducto": "{{ $producto->tipoProducto ?? -1 }}",
                        "extras": extrasAux
                    };
                    productos.push(auxProducto);
                    productosGeneral.push(auxProducto);
                </script>
            @endforeach

            <script>
                categorias.push({
                    "id": "{{ $categoria->id }}",
                    "categoria": "{{ $categoria->categoria }}",
                    "productos": productos
                });
            </script>
        @endforeach

        <script>
            tipos.push({
                "nombre": "{{ $tipo['nombre'] }}",
                "codigo": "{{ $tipo['codigo'] }}",
                "color": "{{ $tipo['color'] }}",
                "categorias": categorias
            });
        </script>
    @endforeach

    <!-- #endregion -->

    <!-- Main Content -->

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="row">

                            <div class="col-sm-12 col-md-4 col-lg-3" id="contEscogerProductos" 
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
                            <div class="col-sm-12 col-md-4 col-lg-5" style="padding-right: 0px !important;padding-left: 0px !important;">
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
                                            <li id="contCerrarCaja">
                                                <button type="button" class="btn btn-danger px-2 mr-1"
                                                    onclick="abrirModalCerrarCaja()">Cerrar Caja <i class="fas fa-list"
                                                        aria-hidden="true"></i></button>
                                            </li>
                                            <li id="contOrdenesCaja">
                                                <button type="button" class="btn btn-info px-2 mr-1"
                                                    onclick="recargarOrdenes()">Ordenes <i class="fas fa-list"
                                                        aria-hidden="true"></i></button>
                                            </li>

                                            <li id="contLimiarCaja">
                                                <button type="button" class="btn btn-info px-2 mr-1"
                                                    onclick="limpiarOrden()">Limpiar <i class="fas fa-broom"
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
                                                            <div class="input-group">
                                                                <input type="text" class="form-control h-75"
                                                                    name="txt-cliente" id="txt-cliente"
                                                                    placeholder="Nombre cliente..."
                                                                    onkeyup="enterCampoPago(event)"
                                                                    onchange="ordenGestion.cliente = $('#txt-cliente').val()">
                                                            </div>
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

                            <div class="col-sm-12 col-md-4 col-lg-4" id="contFacturar" 
                            style="padding-right: 0px !important;padding-left: 0px !important;">
                                <div class="col-lg-12 col-md-12 pl-0">

                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header d-block" style="padding: 10px !important;">
                                                <div class="card-title">
                                                    <div class="row">


                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <h5 id="txt-subtotal-pagar" class="text-muted"
                                                                style="margin-left: 3%">
                                                                SubTotal: 0,00</h5>
                                                        </div>

                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <h5 id="txt-descuento-pagar" class="text-muted"
                                                                style="margin-left: 3%">
                                                                Descuento: 0,00</h5>
                                                        </div>

                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <h5 id="txt-total-pagar" class="text-muted"
                                                                style="margin-left: 3%">
                                                                Total: 0,00</h5>
                                                        </div>

                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-9">
                                                                    <input type="text" class="form-control h-75"
                                                                        name="txt_codigo_descuento"
                                                                        onkeyup="enterDescuento(event)"
                                                                        id="txt_codigo_descuento"
                                                                        placeholder="Código de Descuento">
                                                                </div>
                                                                <div class="col-sm-12 col-md-6 col-lg-3"
                                                                    style="padding-left:0px; ">
                                                                    <a class="btn btn-success " style="color: white"
                                                                        onclick="validarCodDescuento()"><i
                                                                            class="fas fa-check"
                                                                            aria-hidden="true"></i></a>
                                                                    <a class="btn btn-danger " style="color: white"
                                                                        onclick="eliminarCodDescuento()"><i
                                                                            class="fas fa-trash"
                                                                            aria-hidden="true"></i></a>
                                                                </div>
                                                                <div class="col-sm-12 col-md-12 col-lg-12"
                                                                    id="cont-dsc_promo" style="display: none">
                                                                    <strong id="txt-dsc_promo"> </strong>
                                                                </div>

                                                            </div>
                                                        </div>

                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <button type="button" class="btn btn-info " id="btnPago"
                                                                style="width: 100% !important; margin-bottom:20px;"
                                                                onclick="verificarAbrirModalPago()">Pagar <i
                                                                    class="fas fa-payment"
                                                                    aria-hidden="true"></i></button>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <div class="card-body" style="padding: 15px !important;">
                                                <div class="row">

                                                    <div class="col-12 col-md-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label>Monto Tarjeta (₡)</label>
                                                            <input type="number" class="form-control" step=any
                                                            onkeyup="enterCampoPago(event)"
                                                                id="monto_tarjeta" name="monto_tarjeta" value=""
                                                                placeholder="0.00" min="0">
                                                                <button type="button" class="btn btn-info " id="btnPagoTarjeta"
                                                                style="width: 100% !important; margin-bottom:20px;"
                                                                onclick="verificarAbrirModalPagoTarjeta()">Pagar con tarjeta<i
                                                                    class="fas fa-payment"
                                                                    aria-hidden="true"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label>Monto Efectivo (₡)</label>
                                                            <input type="number" class="form-control" step=any
                                                            onkeyup="enterCampoPago(event)"
                                                                id="monto_efectivo" name="monto_efectivo" value=""
                                                                placeholder="0.00" min="0">
                                                                <button type="button" class="btn btn-info " id="btnPagoEfectivo"
                                                                style="width: 100% !important; margin-bottom:20px;"
                                                                onclick="verificarAbrirModalPagoEfectivo()">Pagar con efectivo<i
                                                                    class="fas fa-payment"
                                                                    aria-hidden="true"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label>Monto Sinpe (₡)</label>
                                                            <input type="number" class="form-control" step=any
                                                            onkeyup="enterCampoPago(event)"
                                                                id="monto_sinpe" name="monto_sinpe" value=""
                                                                placeholder="0.00" min="0">
                                                                <button type="button" class="btn btn-info " id="btnPagoSinpe"
                                                                style="width: 100% !important; margin-bottom:20px;"
                                                                onclick="verificarAbrirModalPagoSinpe()">Pagar con sinpe<i
                                                                    class="fas fa-payment"
                                                                    aria-hidden="true"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
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

    <input type="text" style="width: 3px;" id="scanner" placeholder="scanner">

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
                        <a class="btn btn-primary" title="Guardar Composición" onclick="seleccionarExtrasProd()"
                            style="color:white;cursor:pointer;">Agregar</a>
                        <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarExtras()'
                            style="cursor: pointer;">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id='mdl-ordenes' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered  modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="" id="input_buscar_generico" class="form-control"
                                placeholder="Buscar..">
                        </div>
                    </form>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="width: 100%">

                    <table class="table" id="tbl-ordenes" style="max-height: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" style="text-align: center">No.Orden</th>
                                <th scope="col" style="text-align: center">Fecha</th>
                                <th scope="col" style="text-align: center">Cliente</th>
                                <th scope="col" style="text-align: center">Total pagado</th>
                                <th scope="col" style="text-align: center">Estado</th>
                                <th scope="col" style="text-align: center"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-ordenes">

                        </tbody>
                    </table>

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

    <div class="modal fade bd-example-modal-lg" id='mdl-detallesAnular' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered  modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="" id="input_buscar_generico" class="form-control"
                                placeholder="Buscar..">
                        </div>
                    </form>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="width: 100%">

                    <table class="table" id="tbl-detallesAnular" style="max-height: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" style="text-align: center">PRODUCTO</th>
                                <th scope="col" style="text-align: center">CANTIDAD</th>
                                <th scope="col" style="text-align: center">Total pagado</th>
                                <th scope="col" style="text-align: center">Devolver a inventario?</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detallesAnular">

                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <a class="btn btn-primary" title="Anular Orden" onclick="anularOrden()"
                            style="color:white;cursor:pointer;">Agregar</a>
                        <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarMdlOrdenes()'
                            style="cursor: pointer;">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id='mdl-cerrar-caja' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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

                <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>

                <!--
                                                                                                                                                    <div class="modal fade bs-example-modal-center" id='mdl-cliente' tabindex="-1" role="dialog"
                                                                                                                                                        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                                                                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                                                                                                            <div class="modal-content">
                                                                                                                                                                <div class="modal-header">
                                                                                                                                                                    <h5 class="modal-title">Buscar clientes</h5>
                                                                                                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                                                                                    </button>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="modal-body">
                                                                                                                                                                    <table class="table" id="tbl-clientes" style="max-height: 100%;">
                                                                                                                                                                        <thead class="thead-light">
                                                                                                                                                                            <tr>
                                                                                                                                                                                <th scope="col">Nombre</th>
                                                                                                                                                                                <th scope="col" style="text-align: center">Teléfono</th>
                                                                                                                                                                                <th scope="col" style="text-align: center">Correo</th>
                                                                                                                                                                                <th scope="col" style="text-align: center">Ubicación</th>
                                                                                                                                                                                <th scope="col" style="text-align: center">Seleccionar</th>
                                                                                                                                                                            </tr>
                                                                                                                                                                        </thead>
                                                                                                                                                                        <tbody id="tbody-clientes">
                                                                                                                                                                         foreach ($data['clientes'] as $cliente)
                                                                                                                                                                                <tr>
                                                                                                                                                                                    <td> $cliente->nombre </td>
                                                                                                                                                                                    <td class="text-center"> $cliente->telefono }}</td>
                                                                                                                                                                                    <td class="text-center">$cliente->correo }}</td>
                                                                                                                                                                                    <td class="text-center"> $cliente->ubicacion }}</td>
                                                                                                                                                                                    <td class="text-center"><button type="button" class="btn btn-info"
                                                                                                                                                                                            onclick="seleccionarCliente(' $cliente->id }}',' $cliente->nombre }}')"
                                                                                                                                                                                            data-dismiss="modal">
                                                                                                                                                                                            <i class="fas fa-check" aria-hidden="true"></i>
                                                                                                                                                                                        </button></td>
                                                                                                                                                                                </tr>
                                                                                                                                                                            endforeach
                                                                                                                                                                        </tbody>
                                                                                                                                                                    </table>
                                                                                                                                                                </div>
                                                                                                                                                            </div>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                -->
            @endsection
            @section('script')
                <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
                <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

                <script src="{{ asset('assets/js/facturacion/pos.js') }}"></script>
            @endsection
