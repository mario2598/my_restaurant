@extends('layout.master')

@section('styles')

  <link rel="stylesheet" href="{{asset("assets/bundles/pretty-checkbox/pretty-checkbox.min.css")}}">

@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
  <section class="section">
    <div class="section-body">
      <div class="card card-warning">
        <div class="card-header">
          <h4>Restaurantes activos</h4>
          <form class="card-header-form">
            <div class="input-group">
              <input type="text" name="" id="input_buscar_generico" class="form-control" placeholder="Buscar..">
              <div class="input-group-btn">
                <a class="btn btn-primary btn-icon" style="cursor: pointer;" onclick="$('#input_buscar_generico').trigger('change');"><i class="fas fa-search"></i></a>
              </div>
            </div>
          </form>
        </div>
        <div class="card-body">
        
          <div class="row" style="width: 100%">
            <div class="col-sm-12 col-md-2">
              <div class="form-group">
                <a class="btn btn-primary" title="Agregar Restaurante" style="color:white;" href="{{url('restaurante/agregar')}}">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                <thead>
                  <tr>
                    
                    <th class="space-align-center">Código Restaurante</th>
                    <th class="space-align-center">Sucursal</th>
                    <th class="space-align-center">Acciones</th>
                   
                  </tr>
                </thead>
                <tbody id="tbody_generico">
                  @foreach($data['restaurantes'] as $g)
                <tr>
                  
                  <td class="space-align-center">
                    RES-{{$g->id ?? "00"}}
                  </td>
                  <td class="space-align-center">
                    {{$g->sucursal_nombre ?? ""}} 
                  </td>
                 
                  <td class="space-align-center" >
                    <a onclick='editarRestaurante("{{$g->id}}")'  title="Editar Restaurante" class="btn btn-secondary" style="color:white"><i class="fas fa-cog"></i></a> 
                    <a onclick='editarMobiliario("{{$g->id}}")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
                    <a onclick="inactivar({{$g->id}})" title="Inactivar" class="btn btn-danger" style="color:white"> <i class="fa fa-times-circle"></i></a>
                  </td>
                </tr>
    
                @endforeach
              
              </tbody>
            </table>
          </div>
          </div>
          <form id="frmEliminarGenerico" action="{{URL::to('eliminarrol')}}"  style="display: none" method="POST" >
            {{csrf_field()}}
            <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
          </form>

          <form id="frmEditarRestaurante" action="{{URL::to('restaurante/editar')}}"  style="display: none" method="POST" >
            {{csrf_field()}}
            <input type="hidden" name="idEditarRestaurante" id="idEditarRestaurante" value="-1">
          </form>

          <form id="frmEditarRestauranteMobiliario" action="{{URL::to('restaurante/restaurante')}}"  style="display: none" method="POST" >
            {{csrf_field()}}
            <input type="hidden" name="idEditarRestauranteMobiliario" id="idEditarRestauranteMobiliario" value="-1">
          </form>
        </div>
      </div>
    
    </div>
  </section>

  </div>

@endsection



@section('script')
  <script src="{{asset("assets/bundles/sweetalert/sweetalert.min.js")}}"></script>
  
  <script src="{{asset("assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js")}}"></script>
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/restaurante/restaurantes.js")}}"></script>
  

     
@endsection