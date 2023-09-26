@extends('layout.master')

@section('content')
    @include('layout.sidebar')
    <!-- Listas de productos -->
    <script>
        var tipos = []; // Se crea la lista  de tipos de productos
        var productosGeneral = [];
        var detalles = [];
        var salonSeleccionado = 0;
        var mobiliarioSeleccionado = 0;
        var clienteSeleccionado = 0;
        var contadorAux = 0;
        var tipoOrden = "{{ $data['orden']->tipo ?? 'LL' }}";
        var idOrden = "{{ $data['orden']->id ?? -1 }}";

    </script>


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
                    auxProducto = {
                        "id": "{{ $producto->id }}",
                        "nombre": "{{ $producto->nombre ?? '' }}",
                        "impuesto": "{{ $producto->impuesto ?? 0 }}",
                        "precio": "{{ $producto->precio ?? 0 }}",
                        "codigo": "{{ $producto->codigo ?? '' }}",
                        "tipoComanda": "{{ $producto->tipo_comanda ?? '' }}",
                        "cantidad": "{{ $producto->cantidad ?? -1 }}",
                        "cantidad_original": "{{ $producto->cantidad ?? -1 }}",
                        "tipoProducto": "{{ $producto->tipoProducto ?? -1 }}"
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

    <script>
        var salones = []; // Se crea la lista  de salones
        let productoEncontrado;
        let totalAux = 0;

    </script>

    @foreach ($data['orden']->detalles as $d)
        <script>
            productosGeneral.forEach(producto => {
                if (producto.codigo == "{{ $d->codigo_producto }}") {
                    productoEncontrado = producto;
                }
            });

            totalAux = parseFloat("{{ $d->precio_unidad }}" * "{{ $d->cantidad }}").toFixed(2);

            if ("{{ $d->servicio_mesa }}" == 'S') {
                let impuestoMesa = 0;
                impuestoMesa =  totalAux -(totalAux / 1.10); 
                totalAux = parseInt(totalAux) + parseInt(impuestoMesa);
            }
            detalles.push({
                "indice": contadorAux,
                "cantidad": "{{ $d->cantidad }}",
                "impuestoServicio": "{{ $d->servicio_mesa }}",
                "impuesto": "{{ $d->porcentaje_impuesto }}",
                "precio_unidad": "{{ $d->precio_unidad }}",
                "total": totalAux,
                "observacion": "{{ $d->observacion }}",
                //"tipo": tipos[tipoSeleccionado].codigo,
                "tipo": "{{ $d->tipo_producto }}",
                "fechaCreacion": "{{ $d->fecha_creacion }}",
                "tipoComanda": "{{ $d->tipo_comanda }}",
                "cantidadPreparada": "{{ $d->cantidad_preparada }}",
                "producto": productoEncontrado
            });
            contadorAux++;

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
                            <!-- <div class="d-flex flex-row">
                                                                     Panel seleccionar productos -->
                            <div class="col-lg-5 col-md-12 pr-25">
                                <!-- Productos -->
                                <ul class="nav nav-pills" id="nv-tipos">
                                    <!-- Lista dinámica de tipos -->
                                </ul>
                                <div class="card">
                                    <!-- Categorías -->
                                    <div class="card-header col-12 mt-1" style="max-height: 450px;">
                                        <ul id="scrl-categorias"
                                            class="nav nav-pills d-flex flex-row justify-content-space-between draggable-scroller"
                                            style="overflow-x: auto; cursor: grab; white-space: nowrap; flex-wrap: nowrap;">
                                            <!-- Lista dinámica categorías -->
                                        </ul>
                                    </div>
                                    <!-- Productos -->
                                    <div id="scrl-productos"
                                        class="col-12 d-flex flex-column justify-content-space-between card-body draggable-scroller"
                                        style="max-height: 450px; overflow-y: auto; cursor:grab;">
                                        <table class="table table-borderless" style="background-color: white">
                                            <thead>
                                                <th>Producto</th>
                                                <th class="text-center">Precio</th>
                                                <th class="text-center">Acciones</th>
                                            </thead>
                                            <tbody id="tbody-productos">
                                                <!-- Lista dinámica de productos -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel orden -->
                            <div class="col-lg-7 col-md-12 pl-0">
                                <!-- Acciones -->
                                <div style="padding: 0 5% 1.3% 0">
                                    <ul class="nav nav-pills d-flex flex-row justify-content-end" id="nv-acciones">
                                        <a class="btn btn-info px-2 mr-1" href="{{ url('facturacion/facturar') }}"
                                            style=" color: white;cursor: pointer;"> Nueva Factura <i class="fas fa-broom"
                                                aria-hidden="true"></i></a>

                                        <a class="btn btn-info px-2 mr-1"
                                            onclick='goDividirFactura("{{ $data['orden']->id }}")'
                                            style=" color: white;cursor: pointer;"> Dividir Factura <i class="fas fa-half"
                                                aria-hidden="true"></i></a>
                                        <a class="btn btn-info px-2 mr-1"
                                            onclick='preTickete("{{ $data["orden"]->id }}")'
                                               style="color: white;cursor: pointer;"> Pre tiquete <i class="fas fa-print"
                                                aria-hidden="true"></i></a>
                                        <!-- <button type="button" class="btn btn-info px-2 mr-1"
                                                                                    onclick="confirmarOrden()">Caja Rápida <i class="fas fa-money-bill"
                                                                                        aria-hidden="true"></i></button>-->
                                    </ul>
                                </div>
                                <!-- Orden -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-block">
                                            <h5 class="card-title">Orden ORD-{{ $data['orden']->numero_orden ?? '###' }}
                                            </h5>
                                            <div class="d-flex flex-row mt-10">
                                                <div class="col-4 d-flex flex-row mt-3 mb-2">
                                                    <h3 class="text-muted">Total: </h3>
                                                    <h3 id="txt-total-pagar" class="text-muted" style="margin-left: 3%">
                                                        0,00</h3>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="d-flex flex-row mt-10">
                                                        <div class="form-group col-12 mb-0">
                                                            <div class="input-group">
                                                                <h6>Estimado :
                                                                    {{ $data['orden']->nombre_cliente ?? 'CLIENTE' }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    @if ($data['mesa'] != null)
                                                        <div class="form-group col-12 mt-2 mb-0">
                                                            <h6>Mesa No.{{ $data['mesa']->numero_mesa }}</h6>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="form-group col-6 mt-2 mb-0" style="text-align: center">
                                                    <button type="button" class="btn btn-info "
                                                        onclick='guardarFactura(true)'>Facturar <i
                                                            class="fas fa-file-invoice-dollar"
                                                            aria-hidden="true"></i></button>
                                                </div>
                                                <div class="form-group col-6 mt-2 mb-0" style="text-align: center">
                                                    <button type="button" class="btn btn-info "
                                                        onclick='guardarFactura(false)'>Guardar orden <i
                                                            class="fas fa-file-save" aria-hidden="true"></i></button>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="card-body">
                                            <table class="table" id="scrl-orden" class="draggable-scroller"
                                                style="max-height: 100%;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col">Producto</th>
                                                        <th scope="col" style="text-align: center">Cantidad</th>
                                                        <th scope="col" style="text-align: center">Precio</th>
                                                        <th scope="col" style="text-align: center">Total</th>
                                                        <th scope="col" style="text-align: center">Acciones</th>
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
        </section>
    </div>

    
    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>

    <input type="text" style="width: 3px;" id="scanner" placeholder="scanner">
    <form id="frm-dividirFactura" action="{{ URL::to('facturacion/dividirFactura') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="ipt_id_orden_dividir" id="ipt_id_orden_dividir">
    </form>

@endsection
@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

    <script src="{{ asset('assets/js/facturacion/factura.js') }}"></script>

@endsection
