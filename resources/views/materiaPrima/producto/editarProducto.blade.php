@extends('layout.master')

@section('style')
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <form method="POST" action="{{ URL::to('materiaPrima/producto/guardar') }}" autocomplete="off">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="{{ $data['producto']->id }}">
                <div class="card">
                    <div class="card-header">
                        <h4>Editar Producto Menú</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <!-- descripción -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Nombre </label>
                                    <textarea class="form-control" name="nombre" id="nombre" maxlength="2000">{{ $data['producto']->nombre ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group mb-0">
                                    <label>* Unidad de medida</label>
                                    <input type="text" class="form-control" id="unidad_medida" name="unidad_medida"
                                        value="{{ $data['producto']->unidad_medida }}" required maxlength="100">
                                </div>
                            </div>

                            <!-- categoria -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control" id="proveedor" name="proveedor">
                                        @foreach ($data['proveedores'] as $i)
                                            <option value="{{ $i->id ?? -1 }}" title="{{ $i->descripcion ?? '' }}"
                                                @if ($i->id == ($data['producto']->proveedor ?? 0)) selected @endif>{{ $i->nombre ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- precio -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Precio CRC</label>
                                    <input type="number" class="form-control" id="precio" name="precio" step=any
                                        value="{{ $data['producto']->precio ?? '' }}" required min="0">
                                </div>
                            </div>

                            <!-- enviar -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Guardar producto</label>
                                    <input type="submit" class="btn btn-primary form-control" value="Guardar">
                                </div>
                            </div>
                            <!-- eliminar -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Eliminar producto</label>
                                    <a class="btn btn-danger form-control"
                                        onclick='eliminarProducto("{{ $data['producto']->id }}")'
                                        style="color: white;cursor: pointer;">Eliminar </a>
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            </form>

        </section>

    </div>

    <form id="formEliminarProducto" action="{{ URL::to('materiaPrima/producto/eliminar') }}" style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idProductoEliminar" id="idProductoEliminar" value="-1">
    </form>
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/materiaPrima/productos.js') }}"></script>
@endsection
