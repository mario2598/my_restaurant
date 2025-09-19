@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/izitoast/css/iziToast.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Usuarios Administrativos</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="input_buscar_generico" class="form-control"
                                    placeholder="Buscar..">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"
                                        onclick="$('#input_buscar_generico').trigger('change');"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">

                        <div class="row" style="width: 100%">
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <a class="btn btn-primary" title="Agregar Usuario" style="color:white;cursor:pointer;"
                                        onclick="goNuevoUsuario()">+ Agregar</a>
                                </div>
                            </div>


                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="">
                                    <thead>

                                        <tr>

                                            <th class="text-center">Usuario</th>
                                            <th class="text-center">Identificación</th>
                                            <th class="text-center">
                                                Nombre
                                            </th>
                                            <th class="text-center">Correo</th>
                                            <th class="text-center">Teléfono</th>
                                            <th class="text-center">Sucursal</th>
                                            <th class="text-center">Rol</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">

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
    <script src="{{ asset('assets/bundles/sweetalert/sweetalert.min.js') }}"></script>

    <script src="{{ asset('assets/js/mantenimiento/mant_usuarios.js') }}"></script>
@endsection
