@extends('layout.master')

@section('style')

@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <form method="POST" action="{{URL::to('usuario/guardarusuario')}}"   autocomplete="off">
        {{csrf_field()}}
      <div class="card">
        <div class="card-header">
          <h4>Editar Usuario</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- nombre -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Nombre</label>
              <input type="text" class="form-control" id="nombre" name="nombre" value="{{$data['usuario']->nombre ??""}}" required maxlength="25">
              </div>
            </div>
            <!-- ape1 -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Primer Apellido </label>
                <input type="text" class="form-control" id="ape1" name="ape1"  value="{{$data['usuario']->ape1 ??""}}" required maxlength="25">
              </div>
            </div>
            <!-- ape2 -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Segundo Apellido (Opcional)</label>
                <input type="text" class="form-control" id="ape2" name="ape2" value="{{$data['usuario']->ape2 ??""}}" maxlength="25">
              </div>
            </div>
            <!-- cedula -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Cédula</label>
                <input type="text" class="form-control" id="cedula" name="cedula" value="{{$data['usuario']->cedula ??""}}" required maxlength="15">
              </div>
            </div>
            <!-- nacimiento -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Fecha Nacimiento (Opcional)</label>
                <input type="date" id="nacimiento" name="nacimiento" value="{{$data['usuario']->fecha_nacimiento ??""}}" class="form-control">
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
                  <input type="number" class="form-control phone-number" id="telefono" name="telefono" value="{{$data['usuario']->telefono ??""}}" required maxlength="8">
                </div>
              </div>

            </div>

            <!-- usuario -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Usuario</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="fas fa-user"></i>
                    </div>
                  </div>
                  <input type="text" class="form-control" id="usuario" name="usuario" value="{{$data['usuario']->usuario ??""}}" onfocus="this.removeAttribute('readonly');" required maxlength="25">
                </div>
              </div>
            </div>

            <!-- contraseña -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Contraseña </label><label style="color: red;float: right;cursor: pointer;" onclick="$('#frm_cambio_contra').trigger('reset'); $('#modal_cambio_contra').modal('show'); " > Cambiar contraseña?</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="fas fa-lock"></i>
                    </div>
                  </div>
                  <input type="password" class="form-control " id="contra" name="contra" value="{{$data['usuario']->contra ??""}}" readonly maxlength="25" minlength="4">
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
                  <input type="email" class="form-control " id="correo" name="correo" value="{{$data['usuario']->correo ??""}}" maxlength="100">
                </div>
                
              </div>
            </div>

            <!-- rol -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Rol</label>
                <select class="form-control" id="rol" name="rol">
                  @foreach ($data['roles'] as $i)
                  <option value="{{$i->id}}"
                    @if ($i->id == $data['usuario']->rol)
                        selected
                    @endif
                    >{{$i->rol}}</option>
                 @endforeach
                </select>
              </div>
            </div>

            <!-- sucursal -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Sucursal</label>
                <select class="form-control" id="sucursal" name="sucursal">
                 @foreach ($data['sucursales'] as $i)
                  <option value="{{$i->id}}" 
                    @if ($i->id == $data['usuario']->sucursal)
                        selected
                    @endif
                    >{{$i->descripcion}}</option>
                 @endforeach
                </select>
              </div>
            </div>
            <input type="hidden" name="id" id="id" value="{{$data['usuario']->id ??""}}">
            <!-- enviar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Guardar usuario</label>
                <input type="submit" class="btn btn-primary form-control" value="Guardar">
              </div>
            </div>

          </div>
          
         
        </div>
      </div>
    </form>
        
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
    <form id="frm_cambio_contra" method="POST" action="{{URL::to('usuario/editar/cambiarcontra')}}"   autocomplete="off">
      {{csrf_field()}}
    <div class="modal-body">
     
      <div class="form-group">
        <label for="input_modal_pedir_contra" >Ingresa la nueva contraseña</label>
        
          <input type="hidden" name="idUsuarioEditar" id="idUsuarioEditar" value="{{$data['usuario']->id ??""}}">
          <input type="password" class="form-control space_input_modal"  value="" id="nueva_contra" name="nueva_contra" minlength="4" maxlength="25" required>
          <small id="label_titulo_ingresar_contra" class="form-text text-muted">
            La contraseña debe ser min 4 caracteres y max 25 caracteres.
          </small>
          <br>
          <small id="label_titulo_ingresar_contra" class="form-text text-muted">
            La contraseña por defecto es "elAmanecer".
        </small>
        
      </div>
      
    </div>
    <div class="modal-footer bg-whitesmoke br">
      <button type="buttom" class="btn btn-info"onclick="$('#nueva_contra').val('elAmanecer');$('#frm_cambio_contra').submit(); ">Contraseña Defecto</button>
      <input type="submit" class="btn btn-primary" value="Cambiar">
    </div>
  </form>
  </div>
</div>
</div>
     
@endsection


@section('script')
  <script src="{{asset("assets/bundles/sweetalert/sweetalert.min.js")}}"></script>
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/mant_clientes.js")}}"></script>
  

     
@endsection