@extends('layout.master')

@section('content')
    @include('layout.sidebar')
    <!-- Listas de productos -->
    <script>
        var tipos = []; // Se crea la lista  de tipos de productos
        var productosGeneral = [];
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

    </script>

    @foreach ($data['salones'] as $salon)
        <script>
            salones.push({
                "id": "{{ $salon->id }}",
                "nombre": "{{ $salon->nombre }}"
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
                            <!-- <div class="d-flex flex-row">
                                <!-- Panel seleccionar productos -->
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
                                        <button type="button" class="btn btn-info px-2 mr-1"
                                            onclick="limpiarOrden()">Limpiar <i class="fas fa-broom"
                                                aria-hidden="true"></i></button>
                                        <!-- <button type="button" class="btn btn-info px-2 mr-1"
                                                onclick="confirmarOrden()">Caja Rápida <i class="fas fa-money-bill"
                                                    aria-hidden="true"></i></button>-->
                                    </ul>
                                </div>
                                <!-- Orden -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-block">
                                            <h5 class="card-title">Orden</h5>
                                            <div class="d-flex flex-row mt-10">
                                                <div class="col-4 d-flex flex-row mt-3 mb-2">
                                                    <h3 class="text-muted">Total: </h3>
                                                    <h3 id="txt-total-pagar" class="text-muted"
                                                        style="margin-left: 3%">
                                                        0,00</h3>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row mt-10">
                                                <div class="form-group col-12 mb-0">
                                                    <h6 class="card-subtitle ml-2 text-muted">Cliente</h6>
                                                    <div class="input-group">
                                                        <input type="hidden" name="txt-id-cliente" id="txt-id-cliente"
                                                            value="-1">
                                                        <input type="text" class="form-control h-75" name="txt-cliente"
                                                            id="txt-cliente" onchange="$('#txt-id-cliente').val('-1')">
                                                      <!--  <button type="button" class="btn btn-info ml-2" data-toggle="modal"
                                                            data-target="#mdl-cliente">
                                                            Buscar <i class="fas fa-search" aria-hidden="true"></i>
                                                        </button> -->
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row mt-4">
                                                <!-- Salón -->
                                                <div class="form-group col-4 mt-2 mb-0">
                                                    <h6 class="card-subtitle ml-2 text-muted">Salón</h6>
                                                    <select class="form-control form-control-sm" id="sel-salones"
                                                        onchange="seleccionarSalon()">
                                                        <!-- Lista dinámica de salones -->
                                                    </select>
                                                </div>
                                                <!-- Mobiliario -->
                                                <div class="form-group col-4 mt-2 mb-0">
                                                    <h6 class="card-subtitle ml-2 text-muted">Mesa</h6>
                                                    <select class="form-control form-control-sm" id="sel-mobiliario">
                                                        <option value="-1">Seleccionar</option>
                                                        <!-- Lista dinámica de mobiliarios -->
                                                    </select>
                                                </div>
                                                <div class="form-group col-4 mt-2 mb-0">
                                                    <h6 class="card-subtitle ml-2 text-muted">Finalizar</h6>
                                                    <button type="button" id="btn_facturar_confirmar" class="btn btn-info px-2 mr-1"
                                            onclick="confirmarOrden()">Procesar Orden <i class="fas fa-file-invoice-dollar"
                                                aria-hidden="true"></i></button>
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

    <input type="text" style="width: 3px;" id="scanner" placeholder="scanner">

   
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

    <script src="{{ asset('assets/js/facturacion/facturar.js') }}"></script>

@endsection