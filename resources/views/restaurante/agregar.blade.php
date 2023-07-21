@extends('layout.master')

@section('style')

@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <form method="POST" action="{{URL::to('restaurante/restaurante/guardar')}}"   autocomplete="off">
        {{csrf_field()}}
      <div class="card">
        <div class="card-header">
          <h4>Agregar Restaurante</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Nombre Mobiliario -->
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                    <label>Sucursal</label>
                    <select class="form-control" id="sucursal" name="sucursal" required>
                        <option value="-1" selected>Seleccione una sucursal</option>
                        @foreach ($data['sucursales'] as $s)
                            <option value="{{ $s->id ?? '-1' }}" title="{{ $s->descripcion ?? '' }}">{{ $s->descripcion ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
             <!-- enviar -->
             <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>Guardar</label>
                  <input type="submit" class="btn btn-primary form-control" value="Guardar">
                </div>
              </div>

            </div>
            <input type="hidden" value="-1" name="id_restaurante">
          </div>
          
        </div>
      </div>
    </form>
    </section>
  </div>
  @include('layout.configbar')
  
@endsection

@section('script')
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
@endsection