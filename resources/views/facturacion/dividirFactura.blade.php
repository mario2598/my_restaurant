@extends('layout.master')

@section('content')
    @include('layout.sidebar')


    <!-- Orden -->
    <script>
        var orden = {
            "id": "{{ $data['orden']->id }}",
            "numero_orden": "{{ $data['orden']->numero_orden }}",
            "nombre_cliente": "{{ $data['orden']->nombre_cliente }}",
            "total": "{{ $data['orden']->total }}",
            "cajero": "{{ $data['orden']->cajero }}",
            "numero_mesa": "{{ $data['orden']->numero_mesa }}"
        };

        //console.log(orden);

        var detalles = [];
        let producto;

    </script>

    @foreach ($data['orden']->detalles as $detalle)
        <script>
            producto = {
                "nombre": "{{ $detalle->nombre_producto }}",
                "codigo": "{{ $detalle->codigo_producto }}",
                "impuesto": "{{ $detalle->porcentaje_impuesto }}"
            }
            detalles.push({
                "id": "{{ $detalle->id }}",
                "cantidad": parseInt("{{ $detalle->cantidad }}"),
                "impuesto": "{{ $detalle->porcentaje_impuesto }}",
                "impuestoServicio": "{{ $detalle->servicio_mesa }}",
                "indice": parseInt("0"),
                "observacion": "{{ $detalle->observacion }}",
                "precio_unidad": "{{ $detalle->precio_unidad }}",
                "tipo": "{{ $detalle->tipo_producto }}",
                "tipoComanda": "{{ $detalle->tipo_comanda }}",
                "fechaCreacion": "{{ $detalle->fecha_creacion }}",
                "cantidadPreparada": "{{ $detalle->cantidad_preparada }}",
                "total": "{{ $detalle->cantidad * $detalle->precio_unidad }}",
                "producto": producto,
                "orden": "{{ $detalle->orden }}"
            });

            orden["detalles"] = detalles;

        </script>

    @endforeach

    <!-- #endregion -->

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <!-- Panel encabezado -->
                        <div class="col-12 card">
                            <div class="d-flex flex-row">
                                <div class="col-8 d-flex flex-row mt-3 mb-2">
                                    <div class="col-4 ml-1">
                                        <h6 class="text-muted">Salón: {{ $data['orden']->nombre_salon ?? '' }}</h6>
                                        <h6 class="text-muted">Mesa # {{ $data['orden']->numero_mesa ?? '' }}</h6>
                                    </div>
                                    <div class="col-5 ml-1">
                                        <h6 class="text-muted">Cajero: {{ $data['orden']->nombre_cajero ?? 'Usuario' }}
                                        </h6>
                                        <h6 class="text-muted">Cliente:
                                            {{ $data['orden']->nombre_cliente ?? 'Estimado cliente' }}</h6>
                                    </div>
                                </div>
                                <div class="col-4" style="padding: 1.3% 5% 1.3% 0">
                                    <ul class="nav nav-pills d-flex flex-row justify-content-end" id="nv-acciones">
                                        <button type="button" class="btn btn-info px-2 mr-1" style="margin-top: 5px;"
                                            onclick='goFacturaOrden("{{ $data["orden"]->id }}")' >Volver a orden <i class="fas fa-cog"
                                                aria-hidden="true"></i></button>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <!-- Órdenes -->
                            <div class="row">
                                <!-- Panel orden pendiente -->
                                <div class="col-12 col-md-12 col-lg-6 pl-0">

                                    <!-- Orden -->
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header d-block">
                                                <h5 class="card-title">Orden Principal</h5>
                                                <div class="d-flex flex-row">
                                                    <div class="col-4 d-flex flex-row mt-3 mb-2">
                                                        <h6 class="text-muted">Total: </h6>
                                                        <h6 id="txt-total-pendiente" class="text-muted"
                                                            style="margin-left: 3%">
                                                            0,00</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <table class="table" id="scrl-orden-pendiente" class="draggable-scroller"
                                                    style="max-height: 100%;">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th scope="col">Producto</th>
                                                            <th scope="col" style="text-align: center">Cantidad</th>
                                                            <th scope="col" style="text-align: center">Precio</th>
                                                            <th scope="col" style="text-align: center">Total</th>
                                                            <th scope="col" style="text-align: center">Agregar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody-orden-pendiente">

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Panel orden a pagar -->
                                <div class="col-12 col-md-12 col-lg-6 pl-0">
                                    <!-- Orden -->
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header d-block">
                                                <h5 class="card-title">Nueva Orden</h5>
                                                <div class="d-flex flex-row">
                                                    <div class="col-4 d-flex flex-row mt-3 mb-2">
                                                        <h6 class="text-muted">Total:</h6>
                                                        <h6 id="txt-total-facturar" class="text-muted"
                                                            style="margin-left: 3%">
                                                            0,00</h6>
                                                    </div>
                                                    <div class="col-8" style="padding: 1.3% 5% 1.3% 0">
                                                        <ul class="nav nav-pills d-flex flex-row justify-content-end"
                                                            id="nv-acciones">
                                                            <button type="button" class="btn btn-info px-2 mr-1"
                                                                style="border-radius:2px !important"
                                                                onclick="confirmarGenerarFactura()">Dividir Orden <i
                                                                    class="fas fa-file-invoice-dollar"
                                                                    aria-hidden="true"></i></button>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <table class="table" id="scrl-orden-facturar" class="draggable-scroller"
                                                    style="max-height: 100%;">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th scope="col">Producto</th>
                                                            <th scope="col" style="text-align: center">Cantidad</th>
                                                            <th scope="col" style="text-align: center">Precio</th>
                                                            <th scope="col" style="text-align: center">Total</th>
                                                            <th scope="col" style="text-align: center">Eliminar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody-orden-facturar">

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

    <input type="text" style="float: right;display:none" id="scanner" placeholder="scanner">

    <form id="frm-pagar" action="{{ URL::to('facturacion/cobro') }}" method="POST">
        {{ csrf_field() }}
    </form>

    <form id="frm-factrar-orden" action="{{ URL::to('facturacion/pagar') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="ipt_id_orden" id="ipt_id_orden">
    </form>

@endsection
@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

    <script src="{{ asset('assets/js/facturacion/cobro.js') }}"></script>

@endsection
