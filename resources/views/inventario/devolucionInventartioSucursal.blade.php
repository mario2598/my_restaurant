@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <script>
        var inventarioLotes = [];
        var devolucion = [];

    </script>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-6">

                        <div class="col-12 col-sm-12 col-lg-12">

                            <h5 style="font-size: 14px;">Inventario Disponible</h5>

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
                                        @foreach ($data['inventario_por_lote'] ?? [] as $i)
                                            <tr>
                                                <script>
                                                    inventarioLotes.push({
                                                        "id": "{{ $i->id }}",
                                                        "codigo":"{{ $i->codigo }}",
                                                        "nombre": "{{ $i->nombre }}",
                                                        "cantidad": "{{ $i->cantidad }}",
                                                        "sucursal": "{{ $i->sucursal }}",
                                                    });

                                                </script>
                                                <td class="text-center">
                                                    {{ $i->codigo }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->cantidad }}
                                                </td>

                                                <td class="text-center">
                                                <button  class="btn btn-icon btn-success" onclick='agregarProductoDevolucion("{{$i->id}}")'
                                                        style="color: blanchedalmond"><i class="fas fa-plus"></i></button>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-12 col-md-12 col-lg-6">

                        <div class="col-12 col-sm-12 col-lg-12">

                            <h5 style="font-size: 14px;">Detalle Devolución</h5>

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
                                    <tbody id="tbody_devolucion">
                                        @foreach ($data['inventario_por_lote'] ?? [] as $i)
                                        <tr>
                                            <script>
                                                devolucion.push({
                                                    "id": "{{ $i->id }}",
                                                    "codigo":"{{ $i->codigo }}",
                                                    "nombre": "{{ $i->nombre }}",
                                                    "cantidad": 0,
                                                    "sucursal": "{{ $i->sucursal }}",
                                                });

                                            </script>
                                            <td class="text-center">
                                                {{ $i->codigo }}
                                            </td>
                                            <td class="text-center">
                                                {{ $i->nombre }}
                                            </td>
                                            <td class="text-center">
                                               0
                                            </td>

                                            <td class="text-center">
                                                <button  class="btn btn-icon btn-success" onclick='eliminarProductoDevolucion("{{$i->id}}")'
                                                    style="color: blanchedalmond"><i class="fas fa-minus"></i></button>
                                            </td>

                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Destino</label>
                            <select class="form-control" id="sucursal" name="sucursal" required>
                                <option value="-1" selected>Seleccione una sucursal</option>
                                @foreach ($data['sucursales'] as $i)
                                    <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}"
                                >{{ $i->descripcion ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <div class="form-group mb-0">
                            <label>Detalle de devolución</label>
                            <textarea class="form-control" id="detalle_devolucion" name="observacion"
                                id="detalle_movimiento_generado"
                                maxlength="150">{{ $data['movimiento']->detalle ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <div class="form-group mb-0">
                            <label>Devolver</label><br>
                        <a onclick='aplicarDevoulucionSucursal("{{$data["sucursalAuth"]}}")'
                            style="cursor: pointer; color:white;" class="btn btn-warning">Aplicar Devolución</a>
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

    <script src="{{ asset('assets/js/inventario/devolucionSucursal.js') }}"></script>

@endsection
