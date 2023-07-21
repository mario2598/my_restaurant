@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <script>
        var salones = []; // Se crea la lista  de salones

    </script>

    @foreach ($data['restaurante']->salones as $s)
        <script>
            var mobiliario_salon = [];

        </script>
        @foreach ($s->mobiliario as $m)
            <script>
                mobiliario_salon.push({
                    "id": "{{ $m->id }}",
                    "cantidad_personas": "{{ $m->cantidad_personas }}",
                    "nombre": "{{ $m->nombre }}",
                    "descripcion": "{{ $m->descripcion }}",
                    "id_mxs": "{{ $m->id_mxs }}",
                    "numero_mesa": "{{ $m->numero_mesa }}",
                    "estado": "{{ $m->estado }}"
                });

            </script>
        @endforeach
        <script>
            salones.push({
                "id": "{{ $s->id }}",
                "nombre": "{{ $s->nombre }}",
                "ubicacion_detallada": "{{ $s->ubicacion_detallada }}",
                "mobiliario": mobiliario_salon
            });

        </script>
    @endforeach


    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">

                        <div class="col-sm-12 d-flex flex-row">
                            <div class="col-10 form-group">
                                <select class="form-control" id="sucursal" onchange="cambiarSalon(this.value)"
                                    name="sucursal" required>
                                    <option value="-1" selected>Seleccione un salón</option>
                                    @foreach ($data['salones'] as $s)
                                        <option value="{{ $s->id ?? '' }}"
                                            title="{{ $s->ubicacion_detallada ?? '' }}">{{ $s->nombre ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2 h-100 d-flex flex-column justify-content-center">
                                <button id="btn-agregar-salon" type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#mdl_salon">Agregar</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-lg-12">
                            <h5 style="font-size: 14px;">Mobiliario asignado</h5>
                        </div>

                        <div class="col-sm-12">
                            <div class="row" id="restaurante_contenedor_mobiliario">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <form id="frmgoEditarMobiliario" action="{{ URL::to('restaurante/restaurante/salon/mobiliario/editar') }}"
        style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idEditarMobiliario" id="idEditarMobiliario" value="-1">
    </form>
    <form id="frmgoAgregarMobiliario" action="{{ URL::to('restaurante/restaurante/salon/mobiliario/agregar') }}"
        style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idSalonAgregarMobiliario" id="idSalonAgregarMobiliario" value="-1">
        <input type="hidden" name="idRestauranteAgregarMobiliario" id="idRestauranteAgregarMobiliario"
            value="{{ $data['restaurante']->id }}">
    </form>
    <form id="frmInactivarMobiliario" action="{{ URL::to('restaurante/restaurante/salon/mobiliario/inactivar') }}"
        style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="id_mxs_inactivar" id="id_mxs_inactivar" value="-1">
    </form>
    <form id="frmEliminarMobiliario" action="{{ URL::to('restaurante/restaurante/salon/mobiliario/eliminar') }}"
        style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="id_mxs_eliminar" id="id_mxs_eliminar" value="-1">
    </form>
    <form id="frmEliminarSalon" action="{{ URL::to('restaurante/salon/eliminar') }}" autocomplete="off" style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="frm_eliminar_salon_id_restaurante" id="frm_eliminar_salon_id_restaurante" value="{{$data["restaurante"]->id}}">
        <input type="hidden" name="frm_eliminar_salon_id_salon" id="frm_eliminar_salon_id_salon" value="-1">
    </form>

    <!-- modal modal de agregar salón -->
    <div class="modal fade bs-example-modal-center" id='mdl_salon' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ URL::to('restaurante/salon/guardar') }}" autocomplete="off" method="POST">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                        </div>
                        <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Salón</h5>
                        <button type="button" class="close" aria-hidden="true" data-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="mdl_salon_ipt_id" id="mdl_salon_ipt_id" value="-1">
                            <input type="hidden" name="mdl_salon_ipt_id_restaurante" id="mdl_salon_ipt_id_restaurante"
                                value="{{ $data['restaurante']->id }}">
                            <div class="col-xl-12 col-sm-10">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" class="form-control space_input_modal" id="mdl_salon_ipt_nombre"
                                            name="mdl_salon_ipt_nombre" required="true">
                                        <span id='mdl_spam_nombre' style='color:red; display:none;'></span>
                                        <input type="hidden" id="mdl_mobiliario_ipt_id" name="mdl_mobiliario_ipt_id"
                                            value="-1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-sm-10">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <label class="form-label">Ubicación detallada</label>
                                        <textarea type="text" class="form-control space_input_modal"
                                            id="mdl_salon_ipt_ubicacion" name="mdl_salon_ipt_ubicacion" required="true"
                                            maxlength="300"></textarea>
                                        <span id='mdl_spam_ubicacion' style='color:red; display:none;'></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                        <a href="javascript:void(0)" class="btn btn-secondary" data-dismiss="modal">Volver</a>
                        <input type="button" id="mdl_salon_btn_eliminar" class="btn btn-danger" value="Eliminar"
                            style="display: none" onclick="eliminarSalon()" />
                        <input type="submit" class="btn btn-primary" value="Guardar" />

                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

@endsection
@section('script')
        <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

        <script src="{{ asset('assets/js/restaurante/restaurante.js') }}"></script>

@endsection
