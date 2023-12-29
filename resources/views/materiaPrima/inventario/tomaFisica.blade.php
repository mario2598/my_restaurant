@extends('layout.master')

@section('style')

    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection


@section('content')

    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">

                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Crear Toma Física</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="btn_buscar_pro" class="form-control"
                                    placeholder="Buscar producto">
                               
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="row" style="width: 100%">
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Sucursal</label>
                                    <select class="form-control" id="sucursal" name="sucursal" required>
                                        <option value="-1" selected>Seleccione una sucursal</option>
                                        @foreach ($data['sucursales'] as $i)
                                            <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}" >
                                                {{ $i->descripcion ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Buscar</label>
                                    <button onclick="filtrar()" class="btn btn-primary btn-icon form-control"
                                        style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                </div>
                            </div>

                        </div>
                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaInventarios">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">
                                                Unidad Medida
                                            </th>
                                            <th class="text-center">
                                                Cantidad Toma
                                            </th>
                                            <th class="text-center">
                                                Crear Toma
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">

                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>



@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/materiaPrima/inventario/tomaFisica.js') }}"></script>
@endsection
