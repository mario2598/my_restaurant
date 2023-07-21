@extends('layout.master')

@section('style')

@endsection

@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">

      <form method="POST" action="{{URL::to('restaurante/restaurante/salon/mobiliario/asignar')}}"   autocomplete="off">
        {{csrf_field()}}
        <input type="hidden" id="salon" name="salon" value="{{$data['salon']}}">
        <input type="hidden" id="restaurante" name="restaurante" value="{{$data['restaurante']}}">
      <div class="card">
        <div class="card-header">
          <h4>Asignar Mobiliario</h4>
        </div>
        <div class="card-body">
          <div class="row">
            
            <!-- Nombre Mobiliario -->
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                    <label>* Mobiliario</label>
                    <select class="form-control" id="mobiliario" name="mobiliario" required="true" >
                        <option value="" selected>Seleccione un mobiliario</option>
                        @foreach ($data['mobiliario_disponible'] as $m)
                            <option value="{{ $m->id ?? '-1' }}" title="{{ $m->nombre ?? '' }}">{{ $m->nombre ?? '' }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
            <input type="hidden" value="{{$data['mobiliario']->id ??""}}" name="id_mxs">

            <!-- Cantidad máxima -->
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>* Cantidad de personas </label>
                  <input type="number" class="form-control" name="cantidad_personas" min="1" max="50" required="true" >
                </div>
            </div>
          
            <!-- Número de mesa -->
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>* Número de mesa </label>
                  <input type="number" class="form-control" name="numero_mesa" required min="0" max="50" required="true" >
                </div>
            </div>

            <!-- enviar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <input type="submit" class="btn btn-primary form-control" value="Asignar mobiliario">
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
@endsection