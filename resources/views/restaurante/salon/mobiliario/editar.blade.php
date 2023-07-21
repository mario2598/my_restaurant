@extends('layout.master')

@section('style')

@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <form method="POST" action="{{URL::to('restaurante/restaurante/salon/mobiliario/guardar')}}"   autocomplete="off">
        {{csrf_field()}}
      <div class="card">
        <div class="card-header">
          <h4>Editar Mobiliario</h4>
        </div>
        <div class="card-body">
          <div class="row">

             <!-- Descripción mobiliario -->
             <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Estado</label>
                  <input type="text" class="form-control" value="
                  @switch($data['mobiliario']->estado)
                  @case("D")
                      Disponible
                      @break
                  @case("I")
                      Inactivo
                      @break
                  @case("O")
                      Ocupada
                      @break
              @endswitch" readonly>
                  
              </div>
            </div>
            
            <!-- Nombre Mobiliario -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Mobiliario</label>
                <select class="form-control" id="mobiliario" name="mobiliario" onchange="changeMobiliario(this.)" required>
                    @foreach ($data['mobiliario_disponible'] as $m)
                        <option value="{{ $m->id ?? '' }}" title="{{ $m->descripcion ?? '' }}" 
                          @if ($m->id == $data['mobiliario']->mobiliario->id )
                              selected
                          @endif
                          >{{ $m->nombre ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            </div>
            <input type="hidden" value="{{$data['mobiliario']->id ??""}}" name="id_mxs">
            
            <!-- Descripción mobiliario -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Descripción mobiliario</label>
                <textarea  class="form-control"  readonly>{{$data['mobiliario']->mobiliario->descripcion??""}}</textarea>
              </div>
            </div>

            <!-- Ubicación -->
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>Cantidad de personas </label>
                  <input type="number" class="form-control" value="{{$data['mobiliario']->mobiliario->cantidad_personas ??""}}" readonly>
                </div>
            </div>
          
            <!-- Ubicación -->
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>* Número de mesa </label>
                  <input type="number" class="form-control" name="numero_mesa" value="{{$data['mobiliario']->numero_mesa ??""}}" required max="50">
                </div>
            </div>

            <!-- enviar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Guardar mobiliario</label>
                <input type="submit" class="btn btn-primary form-control" value="Guardar">
              </div>
            </div>

             <!-- enviar -->
             <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>Inactivar mobiliario</label>
                  <input type="submit" class="btn btn-warning form-control" value="
                  @switch($data['mobiliario']->estado)
                    @case("D") Inactivar @break
                    @case("I") Activar @break
                    @case("O") Ocupada  @break
              @endswitch" 
              @switch($data['mobiliario']->estado)
                  @case("O")
                      disabled
                      @break
              @endswitch>
                  
                </div>
              </div>

          </div>
          
        </div>
      </div>
    </form>

    <form method="POST" action="{{URL::to('restaurante/restaurante/salon/mobiliario/inactivar')}}"   autocomplete="off">
      {{csrf_field()}}
      <input type="hidden" value="{{$data['mobiliario']->id ?? 0}}" name="id_mxs_inactivar">
    </form> 
    </section>
    
  </div>
  @include('layout.configbar')
  
@endsection

@section('script')
  <script src="{{asset("assets/bundles/sweetalert/sweetalert.min.js")}}"></script>
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/mant_clientes.js")}}"></script>
  

     
@endsection