@extends('layout.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/bundles/pretty-checkbox/pretty-checkbox.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Roles</h4>
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
                                    <a class="btn btn-primary" title="Agregar Rol" style="color:white;"
                                        onclick="nuevoGenerico()">+ Agregar</a>
                                </div>
                            </div>


                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="">
                                    <thead>
                                        <tr>

                                            <th class="space-align-center">Código</th>
                                            <th class="space-align-center">Rol</th>
                                            <th class="space-align-center">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">
                                        @foreach ($data['roles'] as $g)
                                            <tr>

                                                <td class="space-align-center">
                                                    {{ $g->codigo ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $g->rol ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    <a onclick='editarGenerico("{{ $g->id }}","{{ $g->codigo ?? '' }}","{{ $g->rol ?? '' }}","{{ $g->tipo_gasto_id ?? '' }}","{{ $g->tipo_ingreso_id ?? '' }}","{{ $g->administrador ?? 'N' }}","{{ $g->cierra_caja ?? 'N' }}")'
                                                        title="Editar" class="btn btn-primary" style="color:white"><i
                                                            class="fas fa-cog"></i></a>
                                                    <a onclick="eliminarGenerico({{ $g->id }})" title="Eliminar"
                                                        class="btn btn-danger" style="color:white"> <i
                                                            class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <form id="frmEliminarGenerico" action="{{ URL::to('eliminarrol') }}" style="display: none"
                            method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
                        </form>
                    </div>
                </div>

            </div>
        </section>
    </div>


    <!-- modal modal de agregar proveedor -->
    <div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formRoles" action="{{ URL::to('guardarrol') }}" autocomplete="off" method="POST">
                    {{ csrf_field() }}
                    <input type="hidden" id="mdl_generico_ipt_id" name="mdl_generico_ipt_id" value="-1">
                    <div class="modal-header">

                        <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                        </div>
                        <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Rol</h5>
                        <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                            onclick="cerrarModalGenerico()">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-12 col-sm-12">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <label class="form-label">Código de Rol</label>
                                        <input type="text" class="form-control space_input_modal"
                                            id="mdl_generico_ipt_codigo" name="mdl_generico_ipt_codigo" required
                                            maxlength="50">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-sm-12">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <label class="form-label">Rol</label>
                                        <input type="text" class="form-control space_input_modal"
                                            id="mdl_generico_ipt_rol" name="mdl_generico_ipt_rol" required
                                            maxlength="15">
                                    </div>
                                </div>
                            </div>


                            <div id="cont_permisos_roles" class="col-12 col-md-6 col-lg-12">

                                <label>Permisos</label>
                                @foreach ($data['vistas'] as $i)
                                    <div class="card-body">
                                        <div class="section-title">{{ $i->titulo ?? '' }}</div>
                                        @foreach ($i->submenus as $m)
                                            <div class="pretty p-default p-curve p-thick">
                                                <input type="checkbox" name="menus[]" value="{{ $m->id }}" />
                                                <div class="state p-warning">
                                                    <label>{{ $m->titulo ?? '' }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                        </div>

                    </div>
                    <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                        <a href="#" class="btn btn-secondary" onclick="cerrarModalGenerico()">Volver</a>
                        <input type="submit" class="btn btn-primary" value="Guardar" />

                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar sucursal-->
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/mant_roles.js') }}"></script>
@endsection