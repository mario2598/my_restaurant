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

                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Posición Menú</label>
                                    <input type="number" class="form-control" id="posicion_menu" name="posicion_menu"
                                        value="{{ $data['producto']->posicion_menu ?? 0 }}" min="0">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group ">
                                    <label>Foto Producto</label>
                                    <input type="file" id="foto_producto" name="foto_producto"
                                        accept="image/png, image/jpeg, image/jpg">
                                    <small class="form-text text-muted">Seleccione una nueva imagen para actualizar</small>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Imagen</label>
                                    <img src="{{ $data['producto']->url_imagen ?? asset('assets/images/default-logo.png') }}"
                                        id="imgProdExterno" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px; padding: 5px;"
                                        alt="Imagen"
                                        onerror="this.src='{{ asset('assets/images/default-logo.png') }}'; this.onerror=null;">
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
    <script>
        // Preview de imagen cuando se selecciona un archivo nuevo
        $(document).ready(function() {
            $('#foto_producto').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imgProdExterno').attr('src', e.target.result);
                        $('#imgProdExterno').css({
                            'border': '2px solid #28a745',
                            'box-shadow': '0 0 5px rgba(40, 167, 69, 0.5)'
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endsection
