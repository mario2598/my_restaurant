@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <script>
        var inventarioProductos = [];
        var pedido = [];

    </script>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-5">

                        <div class="col-12 col-sm-12 col-lg-12">

                            <h5 style="font-size: 14px;">Productos existentes</h5>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaDetalle">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center"># Código</th>
                                            <th scope="col" class="text-center">Categoría</th>
                                            <th scope="col" class="text-center">Producto</th>
                                            <th scope="col" class="text-center">Agregar</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_inventario">
                                        @foreach ($data['productos'] ?? [] as $i)
                                            <tr>
                                                <script>
                                                    inventarioProductos.push({
                                                        "id": "{{ $i->id }}",
                                                        "codigo_barra": "{{ $i->codigo_barra }}",
                                                        "nombre": "{{ $i->nombre }}",
                                                        "nombre_categoria": "{{ $i->nombre_categoria }}",
                                                    });

                                                </script>
                                                <td class="text-center">
                                                    {{ $i->codigo_barra }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre_categoria }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre }}
                                                </td>

                                                <td class="text-center">
                                                    <button  class="btn btn-icon btn-success"
                                                        onclick='agregarProductoPedido("{{ $i->id }}")'
                                                        style="color: blanchedalmond"><i class="fas fa-plus"></i></button>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-12 col-md-12 col-lg-7">

                        <div class="col-12 col-sm-12 col-lg-12">

                            <h5 style="font-size: 14px;">Detalle Pedido</h5>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaDetalle">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center"># Código</th>
                                            <th scope="col" class="text-center">Categoría</th>
                                            <th scope="col" class="text-center">Producto</th>
                                            <th scope="col" class="text-center">Cantidad</th>
                                            <th scope="col" class="text-center">Devolver</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_pedido">
                                        @foreach ($data['productos'] ?? [] as $i)
                                            <tr>
                                                <script>
                                                    pedido.push({
                                                        "id": "{{ $i->id }}",
                                                        "codigo_barra": "{{ $i->codigo_barra }}",
                                                        "nombre": "{{ $i->nombre }}",
                                                        "cantidad": "{{ $i->cantidad ?? 0 }}",
                                                        "nombre_categoria": "{{ $i->nombre_categoria }}",
                                                    });

                                                </script>
                                                <td class="text-center">
                                                    {{ $i->codigo_barra }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre_categoria }}

                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->cantidad ?? 0 }}
                                                </td>

                                                <td class="text-center">
                                                    <button  class="btn btn-icon btn-success"
                                                        onclick='eliminarProductoPedido("{{ $i->id }}")'
                                                        style="color: blanchedalmond"><i class="fas fa-minus"></i></button>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-12 col-md-3">
                        <div class="form-group">
                            <label>Bodega Solicitud</label>
                            <select class="form-control" id="sucursal" name="sucursal" required>
                                <option value="-1" selected>Seleccione una bodega</option>
                                @foreach ($data['sucursales'] as $i)
                                    <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}" @if ($data['pedido'] ?? null != null)

                                        @if ($i->id == $data['pedido']->id ?? -1)
                                            selected
                                        @endif
                                        selected
                                @endif
                                >{{ $i->descripcion ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if ($data['pedido']->usuarioReceptor ?? null != null)
                        <div class="col-12 col-lg-3">
                            <div class="form-group mb-0">
                                <label>Usuario receptor</label>
                                <input type="text" class="form-control" value="{{ $data['pedido']->usuarioReceptor ?? '' }}"
                                    readonly>

                            </div>
                        </div>
                    @endif
                    <div class="col-12 col-lg-3">
                        <div class="form-group mb-0">
                            <label>Detalle de pedido</label>
                            <textarea class="form-control" name="observacion" id="detalle_pedido"
                                maxlength="150">{{ $data['pedido']->detalle ?? '' }}</textarea>
                        </div>
                    </div>
                    @if ($data['pedido'] ?? null == null)

                         @if ($data['pedido']->estado == 'P')
                            <div class="col-12 col-md-4 col-lg-3">
                                <div class="form-group mb-0">
                                    <label>Realizar Pedido</label><br>
                                        <a onclick='aplicarPedidoSucursal("{{ $data['pedido']->id ?? -1 }}")'
                                            style="cursor: pointer; color:white;" class="btn btn-warning">Guardar Pedido</a>
                                </div>
                            </div>
                        @endif
                    @else
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="form-group mb-0">
                            <label>Realizar Pedido</label><br>
                                <a onclick='aplicarPedidoSucursal(-1)'
                                    style="cursor: pointer; color:white;" class="btn btn-warning">Guardar Pedido</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </section>
    </div>



@endsection
@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

    <script src="{{ asset('assets/js/inventario/pedidoSucursal.js') }}"></script>

@endsection
