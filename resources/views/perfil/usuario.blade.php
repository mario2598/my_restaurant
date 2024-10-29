@extends('layout.master')

@section('style')
@endsection


@section('content')
    @include('layout.sidebar')

    <script>
        var idUsuario = {{ $data['usuario'] == null ? 0 : $data['usuario']->id }};
    </script>

    <div class="main-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 id="text-form"> Perfil de Usuario</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- nombre -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>* Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                    maxlength="25">
                            </div>
                        </div>
                        <!-- ape1 -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>* Primer Apellido </label>
                                <input type="text" class="form-control" id="ape1" name="ape1" required
                                    maxlength="25">
                            </div>
                        </div>
                        <!-- ape2 -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>Segundo Apellido (Opcional)</label>
                                <input type="text" class="form-control" id="ape2" name="ape2" maxlength="25">
                            </div>
                        </div>
                        <!-- cedula -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>* Cédula</label>
                                <input type="text" class="form-control" id="cedula" readonly>
                            </div>
                        </div>
                        <!-- nacimiento -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>Fecha Nacimiento (Opcional)</label>
                                <input type="date" id="nacimiento" name="nacimiento" class="form-control">
                            </div>

                        </div>

                        <!-- telefono -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>* Teléfono (+506)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                    </div>
                                    <input type="number" class="form-control phone-number" id="telefono" name="telefono"
                                        required maxlength="8">
                                </div>
                            </div>

                        </div>

                        <!-- usuario -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>Usuario</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control" id="usuario" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- contraseña -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>* Contraseña </label><label style="color: red;float: right;cursor: pointer;" id="lblCambiarPss"
                                    onclick="$('#frm_cambio_contra').trigger('reset'); $('#modal_cambio_contra').modal('show'); ">
                                    Cambiar contraseña?</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                    </div>
                                    <input type="password" class="form-control " id="contra" name="contra"
                                        maxlength="25" minlength="4">
                                </div>

                            </div>
                        </div>

                        <!-- correo -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>Correo (Opcional)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                    </div>
                                    <input type="email" class="form-control " id="correo" readonly>
                                </div>

                            </div>
                        </div>
                       
                        <input type="hidden" name="id" id="id" value="{{ $data['usuario']->id ?? '' }}">
                        <!-- enviar -->
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label>Actualizar información</label>
                                <input type="button" onclick="guardarUsuario()" class="btn btn-primary form-control"
                                    value="Guardar">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
    @include('layout.configbar')
@endsection

@section('popup')
    <!-- basic modal -->
    <div class="modal fade" id="modal_cambio_contra" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-lock"></i> Cambio de contraseña</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label for="input_modal_pedir_contra">Ingresa la nueva contraseña</label>

                        <input type="password" class="form-control space_input_modal" value="" id="nueva_contra"
                            name="nueva_contra" minlength="4" maxlength="25" required>
                        <small id="label_titulo_ingresar_contra" class="form-text text-muted">
                            La contraseña debe ser min 4 caracteres y max 25 caracteres.
                        </small>

                    </div>

                </div>
                <div class="modal-footer bg-whitesmoke br">
                    
                    <input type="button" class="btn btn-primary" value="Cambiar" onclick="cambiarContra()">
                </div>

            </div>
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ asset('assets/bundles/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/perfil/mant_editar_usuarios.js') }}"></script>
@endsection