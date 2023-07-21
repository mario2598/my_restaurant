@extends('layout.master')
@section('content')

    @include('layout.sidebar')

    <script>
        var inventarioLotes = [];
        var desechos = [];
        var desechosAplicar = [];
    </script>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">

                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Crear Desechos</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label>Sucursal</label>
                                            <select class="form-control" id="sucursal" name="sucursal" onchange="cambiarInventario(this.value)" required>
                                                <option value="-1" selected>Seleccione una sucursal</option>
                                                @foreach ($data['sucursales'] as $i)
                                                    <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}">{{ $i->descripcion ?? '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-group mb-0">
                                            <label>Detalle</label>
                                            <textarea class="form-control" name="observacion" id="detalle"
                                                maxlength="150">{{ $data['movimiento']->detalle ?? '' }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="form-group mb-0">
                                            <label>Confirmar Desechos</label><br>
                                            <a onclick='aplicarDesechos()'
                                                style="cursor: pointer; color:white;" class="btn btn-warning">Confirmar</a>
                                        </div>
                                    </div>


                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-lg-6">

                        <h5 style="font-size: 14px;">Inventario por lotes</h5>

                        <div class="table-responsive">
                            <table class="table table-striped" id="tablaDetalle">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center"># Lote</th>
                                        <th scope="col" class="text-center">Producto</th>
                                        <th scope="col" class="text-center">Cantidad</th>
                                        <th scope="col" class="text-center">Agregar</th>

                                    </tr>
                                </thead>
                                <tbody id="tbody_inventario">
                                    @foreach ($data['lotes'] as $i)
                                        <tr>
                                            <script>
                                                inventarioLotes.push({
                                                    "id": "{{ $i->id }}",
                                                    "codigo": "{{ $i->lote_codigo }}",
                                                    "nombre": "{{ $i->producto_nombre }}",
                                                    "cantidad": "{{ $i->cantidad }}",
                                                });

                                            </script>
                                            <td class="text-center">
                                                {{ $i->lote_codigo }}
                                            </td>
                                            <td class="text-center">
                                                {{ $i->producto_nombre }}
                                            </td>
                                            <td class="text-center">
                                                {{ $i->cantidad }}
                                            </td>
                                            <td class="text-center">
                                                <a href="#" class="btn btn-icon btn-success"
                                                    onclick='agregarProducto("{{ $i->id }}")'
                                                    style="color: blanchedalmond"><i class="fas fa-plus"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <div class="col-12 col-sm-12 col-lg-6">

                        <h5 style="font-size: 14px;">Desechos</h5>

                        <div class="table-responsive">
                            <table class="table table-striped" id="tablaDetalle">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center"># Lote</th>
                                        <th scope="col" class="text-center">Producto</th>
                                        <th scope="col" class="text-center">Cantidad</th>
                                        <th scope="col" class="text-center">Devolver</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_desechos">
                                    @foreach ($data['lotes'] as $i)
                                        <tr>
                                            <script>
                                                desechos.push({
                                                    "id": "{{ $i->id }}",
                                                    "codigo": "{{ $i->lote_codigo }}",
                                                    "nombre": "{{ $i->producto_nombre }}",
                                                    "cantidad": 0,
                                                });

                                            </script>
                                            <td class="text-center">
                                                {{ $i->lote_codigo }}
                                            </td>
                                            <td class="text-center">
                                                {{ $i->producto_nombre }}
                                            </td>
                                            <td class="text-center">
                                                0
                                            </td>
                                            <td class="text-center">
                                                <a href="#" class="btn btn-icon btn-success"
                                                    onclick='eliminarProducto("{{ $i->id }}")'
                                                    style="color: blanchedalmond"><i class="fas fa-minus"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

@endsection
@section('script')

    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/desechos/agregarDesechos.js') }}"></script>

@endsection
