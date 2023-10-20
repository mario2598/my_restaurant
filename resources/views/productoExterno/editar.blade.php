@extends('layout.master')

@section('style')
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <form method="POST" action="{{ URL::to('productoExterno/producto/guardar') }}" enctype="multipart/form-data"  autocomplete="off">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="{{ $data['producto']->id ?? '' }}">

                <div class="card">
                    <div class="card-header">
                        <h4>Editar Producto Externo</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Código -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>* Código</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo"
                                        value="{{ $data['producto']->codigo_barra ?? '' }}" required maxlength="15">
                                </div>
                            </div>
                            <!-- descripción -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>* Nombre </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                        value="{{ $data['producto']->nombre ?? '' }}" required maxlength="50">
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group mb-0">
                                    <label>Descripción</label>
                                    <textarea class="form-control" name="descripcion" id="detalle_movimiento_generado" maxlength="400">{{ $data['producto']->descripcion ?? '' }}</textarea>
                                </div>
                            </div>

                            <!-- categoria -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <select class="form-control" id="categoria" name="categoria">
                                        @foreach ($data['categorias'] as $i)
                                            <option value="{{ $i->id }}"
                                                @if ($i->id == ($data['producto']->categoria ?? -1)) selected @endif>{{ $i->categoria }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- precio -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>* Precio CRC</label>
                                    <input type="number" class="form-control" id="precio" name="precio" step="any"
                                        value="{{ $data['producto']->precio ?? '' }}" required min="0">
                                </div>
                            </div>

                            <!-- precio -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>* Precio Compra CRC</label>
                                    <input type="number" class="form-control" id="precio_compra" name="precio_compra"
                                        step="any" value="{{ $data['producto']->precio_compra ?? '' }}" required
                                        min="0">
                                </div>
                            </div>


                            <!-- impuesto -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Impuesto</label>
                                    <select class="form-control" id="impuesto" name="impuesto">
                                        @foreach ($data['impuestos'] as $i)
                                            <option value="{{ $i->id }}"
                                                @if ($i->id == ($data['producto']->impuesto ?? -1)) selected @endif>{{ $i->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- impuesto -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control" id="proveedor" name="proveedor">
                                        @foreach ($data['proveedores'] as $i)
                                            <option value="{{ $i->id }}"
                                                @if ($i->id == ($data['producto']->proveedor ?? -1)) selected @endif>{{ $i->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group ">
                                    <label>Foto Producto</label>
                                    <input type="file"id="foto_producto" name="foto_producto"
                                        accept="image/png, image/jpeg, image/jpg">
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Imagen</label>
                                    <img src="{{ asset('storage/' . $data['producto']->url_imagen) }}"
                                        style="max-width: 100%; height: auto;" alt="Imagen">
                                </div>
                            </div>



                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Regresar</label>
                                    <input type="button" onclick="window.history.back();" class="btn btn-secondary form-control" value="Regresar">
                                </div>
                            </div>
                            <!-- enviar -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Guardar producto</label>
                                    <input type="submit" class="btn btn-primary form-control" value="Guardar">
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            </form>

        </section>

    </div>
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/bodega/productos.js') }}"></script>
@endsection
