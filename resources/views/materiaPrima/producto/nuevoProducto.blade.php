@extends('layout.master')

@section('style')
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <form method="POST" action="{{ URL::to('materiaPrima/producto/guardar') }}" autocomplete="off">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="-1">

                <div class="card">
                    <div class="card-header">
                        <h4>Ingresar Producto Materia Prima</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Nombre</label>
                                    <textarea class="form-control" name="nombre" id="nombre"
                                     maxlength="5000">{{ $data['datos']['nombre'] ?? '' }}</textarea>

                                </div>
                            </div>
                         

                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group mb-0">
                                    <label>* Unidad de medida</label>
                                    <input type="text" class="form-control" id="unidad_medida" name="unidad_medida"
                                    value="{{ $data['datos']['unidad_medida'] ?? '' }}" required maxlength="100">
                                </div>
                            </div>

                            <!-- categoria -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control" id="proveedor" name="proveedor">
                                        @foreach ($data['proveedores'] as $i)
                                        <option value="{{$i->id ?? -1}}" title="{{$i->descripcion ?? ''}}" 
                                          @if ($i->id == ($data['datos']['proveedor'] ?? 0))
                                                selected
                                            @endif
                                          >{{$i->nombre ?? ''}}</option>
                                       @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- precio -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Precio CRC</label>
                                    <input type="number" class="form-control" id="precio" name="precio" step="any"
                                        value="{{ $data['datos']['precio'] ?? '' }}" required min="0">
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
