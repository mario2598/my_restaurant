@extends('layout.master')

@section('style')
  <link rel="stylesheet" href="{{asset("assets/bundles/datatables/datatables.min.css")}}">
  <link rel="stylesheet" href="{{asset("assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css")}}">
  <link rel="stylesheet" href="{{asset("assets/bundles/izitoast/css/iziToast.min.css")}}">

@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
  <section class="section">
    <div class="section-body">
      <div class="card card-warning">
        <div class="card-header">
          <h4>Usuarios</h4>
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
                <a class="btn btn-primary" title="Agregar Tipo Gasto" style="color:white;cursor:pointer;" href="{{url('usuario/nuevo')}}">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                <thead>
                
                  <tr>
                  
                    <th>Usuario</th>
                    <th class="text-center">
                      Nombre 
                    </th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Sucursal</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tbody_generico">
                    @foreach($data['usuarios'] as $g)
                  <tr>
                    
                    <td>{{$g->usuario ?? ""}}</td>
                    <td>
                      {{$g->nombre}} {{$g->ape1 ?? ""}} {{$g->ape2 ?? ""}}  
                    </td>
                    <td class="align-middle">
                        {{$g->correo ?? ''}}
                    </td>
                    <td>
                      +506  {{$g->telefono ?? ""}}
                    </td>
                    <td>{{$g->sucursal_nombre ?? ""}}</td>
                    <td>
                  
                    @if($g->rol == "1")
                      <div class="badge badge-info badge-shadow">
                    @endif 
                    @if($g->rol == "2")
                      <div class="badge badge-warning badge-shadow">
                    @endif 
                    @if($g->rol == "3")
                      <div class="badge badge-success badge-shadow">
                    @endif 
                    @if($g->rol == "4")
                      <div class="badge badge-secondary badge-shadow">
                    @endif 
                    @if($g->rol == "5")
                      <div class="badge badge-danger badge-shadow">
                    @endif 
                    @if($g->rol > "5")
                      <div class="badge badge-primary badge-shadow">
                    @endif 
                    {{$g->rol_nombre}}</div></td>
                    <td class="space-align-center" >
                    <a onclick="editarUsuario({{$g->id}})" title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
                      <a onclick="eliminarGenerico({{$g->id}})" title="Eliminar" class="btn btn-danger" style="color:white"> <i class="fa fa-trash"></i></a>
                    </td>
                  </tr>

                  @endforeach
                
                </tbody>
              </table>
          </div>
          </div>
         
        </div>
      </div>
      <form id="frmEditarUsuario" action="{{URL::to('usuario/editar')}}"  style="display: none" method="POST" >
        {{csrf_field()}}
        <input type="hidden" name="idUsuarioEditar" id="idUsuarioEditar" value="">
      </form>
    
      <form id="frmEliminarGenerico" action="{{URL::to('eliminarusuario')}}"  style="display: none" method="POST" >
        {{csrf_field()}}
        <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
      </form>
    </div>
  </section>

  </div>


@endsection



@section('script')
  <script src="{{asset("assets/bundles/sweetalert/sweetalert.min.js")}}"></script>
  
  <script src="{{asset("assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js")}}"></script>
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/mant_usuarios.js")}}"></script>
  

     
@endsection