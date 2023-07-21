@extends('layout.master')

@section('style')


@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="card card-warning">
          <div class="card-header">
            <h4>Gastos Pendientes de aprobar</h4>
            <form class="card-header-form">
              <div class="input-group">
                <input type="text" name="" onkeyup="filtrarGastosPendientesUsuario(this.value)" id="btn_buscar_gasto" class="form-control" placeholder="Buscar gasto">
                <div class="input-group-btn">
                  <a class="btn btn-primary btn-icon"><i class="fas fa-search"></i></a>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body">
            <div id="contenedor_gastos_sin_aprobar" class="row">
              @foreach ($data['gastosSinAprobar'] as $g)
                <div class="col-12 col-md-6 col-lg-6">

                  <div class="card card-primary">
                    <div class="card-header">
                      <h4>CRC {{number_format($g->monto,2,".",",")}}  <small>- {{$g->nombre ?? ''}} </small></h4>
                      <div class="card-header-action">
                        @if ($g->caja_cerrada == 'N')
                          <a  onclick="editarGastoUsuario('{{$g->id}}')" style="cursor: pointer; color:white;" class="btn btn-primary">Editar</a>
                          <a href="#" class="btn btn-primary" onclick="eliminarGastoUsuario('{{$g->id}}')" >Eliminar</a>
                        @endif
                       
                      </div>
                    </div>
                    <div class="card-body">
                      <p><strong> {{$g->fecha ?? ''}}</strong> <br> 
                        <small>{{($g->descripcion ?? '')}} </small><br>
                        @if ($g->observacion != null && $g->observacion != "")
                          <small><strong>Observación : </strong> {{$g->observacion ?? ''}} </small><br>
                        @endif
                      </p> 
                      
                    </div>
                    
                  </div>
                </div>
              @endforeach
            </div>
           
          </div>
        </div>
      
      </div>
    </section>
    
  </div>


@endsection



@section('script')

  <script src="{{asset("assets/js/gastos_pendientes.js")}}"></script>
  

     
@endsection