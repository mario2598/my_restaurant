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
          <h4>Nuevo Usuario</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- nombre -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Nombre</label>
              <input type="text" class="form-control" id="nombre" name="nombre" value="{{$data['datos']['nombre'] ??""}}" required maxlength="25">
              </div>
            </div>
            <!-- ape1 -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Primer Apellido </label>
                <input type="text" class="form-control" id="ape1" name="ape1"  value="{{$data['datos']['ape1'] ??""}}" required maxlength="25">
              </div>
            </div>
            <!-- ape2 -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Segundo Apellido (Opcional)</label>
                <input type="text" class="form-control" id="ape2" name="ape2" value="{{$data['datos']['ape2'] ??""}}" maxlength="25">
              </div>
            </div>
            <!-- cedula -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Cédula</label>
                <input type="text" class="form-control" id="cedula" name="cedula" value="{{$data['datos']['cedula'] ??""}}" required maxlength="15">
              </div>
            </div>
            <!-- nacimiento -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Fecha Nacimiento (Opcional)</label>
                <input type="date" id="nacimiento" name="nacimiento" value="{{$data['datos']['nacimiento'] ??""}}" class="form-control">
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
                  <input type="number" class="form-control phone-number" id="telefono" name="telefono" value="{{$data['datos']['telefono'] ??""}}" maxlength="8">
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
                  <input type="text" class="form-control" id="usuario" name="usuario" value="{{$data['datos']['usuario'] ??""}}" onfocus="this.removeAttribute('readonly');" autocomplete="off" required maxlength="25">
                </div>
              </div>
            </div>

            <!-- contraseña -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Contraseña </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="fas fa-lock"></i>
                    </div>
                  </div>
                  <input type="password" class="form-control " id="contra" name="contra" value="{{$data['datos']['contra'] ??""}}" autocomplete="off" required maxlength="25" minlength="4">
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
                  <input type="email" class="form-control " id="correo" name="correo" value="{{$data['datos']['correo'] ??""}}" maxlength="100">
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
                    @if ($i->id == ($data['datos']['rol'] ?? -1))
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
                    @if ($i->id == ($data['datos']['sucursal'] ?? -1))
                        selected
                    @endif
                    >{{$i->descripcion}}</option>
                 @endforeach
                </select>
              </div>
            </div>
          
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

  
@endsection



@section('script')
 
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/mant_clientes.js")}}"></script>
  

     
@endsection