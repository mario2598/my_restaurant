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
                    <h4>Sucursales</h4>
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="" id="input_buscar_sucursal" class="form-control"
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
                                <a class="btn btn-primary" title="Agregar Sucursal" style="color:white;"
                                    onclick="nuevaSucursal()">+ Agregar</a>
                            </div>
                        </div>


                    </div>
                    <div id="contenedor_gastos" class="row">
                        <div class="table-responsive">
                            <table class="table table-striped" id="">
                                <thead>
                                    <tr>
                                        <th class="space-align-center">Código</th>
                                        <th class="space-align-center">Sucursal</th>
                                        <th class="space-align-center">Correo Factura</th>
                                        <th class="space-align-center">Estado</th>
                                        <th class="space-align-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_sucursal">
                                    @foreach ($data['sucursales'] as $s)
                                    <tr>
                                        <td class="space-align-center">{{ $s->id }}</td>
                                        <td class="space-align-center">{{ $s->descripcion }}</td>
                                        <td class="space-align-center">{{ $s->correo_factura ?? 'Sin Asignar'}}</td>

                                        <!-- Estado como texto: Activa o Inactiva según el valor de 'estado' -->
                                        <td class="space-align-center">
                                            @if ($s->estado === 'A')
                                            <span class="badge badge-success">Activa</span>
                                            @else
                                            <span class="badge badge-danger">Inactiva</span>
                                            @endif
                                        </td>

                                        <!-- Acciones de Editar y Eliminar -->
                                        <td class="space-align-center">
                                            <a onclick='editarSucursal("{{ $s->id }}","{{ $s->descripcion }}")'
                                                title="Editar" class="btn btn-primary" style="color:white"><i
                                                    class="fas fa-cog"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

</div>


<!-- modal modal de agregar sucursal -->
<div class="modal fade bs-example-modal-center" id='mdl_sucursal' tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ URL::to('guardarsucursal') }}" autocomplete="off" method="POST">
                {{ csrf_field() }}
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Sucursal</h5>
                    <button type="button" class="close" aria-hidden="true" onclick="cerrarModalSucursal()">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Descripción (Lugar)</label>
                                    <input type="text" class="form-control space_input_modal" required maxlength="50"
                                        id="mdl_sucursal_ipt_descripcion" name="mdl_sucursal_ipt_descripcion">
                                    <input type="hidden" id="mdl_sucursal_ipt_id" name="mdl_sucursal_ipt_id"
                                        value="-1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nuevos campos agregados -->
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Nombre Factura</label>
                                    <input type="text" class="form-control" maxlength="500"
                                        id="mdl_sucursal_ipt_nombre_factura" name="mdl_sucursal_ipt_nombre_factura"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>Tipo de Identificación del Emisor</label>
                                <select class="form-control" id="tipo_identificacion_emisor" name="tipo_identificacion_emisor">
                                    <option value="">Seleccione tipo de identificación</option>
                                    <option value="01">Cédula Física</option>
                                    <option value="02">Cédula Jurídica</option>
                                   
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cédula Factura</label>
                                    <input type="text" class="form-control" maxlength="50"
                                        id="mdl_sucursal_ipt_cedula_factura" name="mdl_sucursal_ipt_cedula_factura"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Correo Factura</label>
                                    <input type="email" class="form-control" maxlength="500"
                                        id="mdl_sucursal_ipt_correo_factura" name="mdl_sucursal_ipt_correo_factura"
                                        required>
                                </div>
                            </div>
                        </div>

                        <!-- Indicador de si está activa o no -->
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <label class="form-label">Sucursal Activa</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mdl_sucursal_chk_activa"
                                        name="mdl_sucursal_chk_activa">
                                    <label class="form-check-label" for="mdl_sucursal_chk_activa">
                                        Activa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                    <a href="#" class="btn btn-secondary" onclick="cerrarModalSucursal()">Volver</a>
                    <input type="submit" class="btn btn-primary" value="Guardar" />
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -- fin modal de agregar sucursal -->
@endsection



@section('script')
<script src="{{ asset('assets/bundles/sweetalert/sweetalert.min.js') }}"></script>

<script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/page/datatables.js') }}"></script>
<script src="{{ asset('assets/js/mant_sucursales.js') }}"></script>
@endsection