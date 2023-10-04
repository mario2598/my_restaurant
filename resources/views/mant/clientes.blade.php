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
          <h4>Clientes</h4>
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
                <a class="btn btn-primary" title="Agregar Cliente" style="color:white;" onclick="nuevoGenerico()">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                <thead>
                  <tr>
                    
                    <th class="space-align-center">Nombre</th>
                    <th class="space-align-center">Télefono</th>
                    <th class="space-align-center">Correo</th>
                    <th class="space-align-center">Ubicación</th>
                    <th class="space-align-center">Acciones</th>
                   
                  </tr>
                </thead>
                <tbody id="tbody_generico">
                  @foreach($data['clientes'] as $g)
                <tr>
                  
                  <td class="space-align-center">
                    {{$g->nombre ?? ""}}
                  </td>
                  <td class="space-align-center">
                    {{$g->telefono ?? ""}} 
                  </td>
                  <td class="space-align-center">
                    {{$g->correo ?? ""}} 
                  </td>
                  <td class="space-align-center">
                    {{$g->ubicacion ?? ""}} 
                  </td>
                  <td class="space-align-center" >
                    <a onclick='editarGenerico("{{$g->id}}","{{$g->nombre ?? ""}}","{{$g->telefono ?? ""}}","{{$g->correo ?? ""}}","{{$g->ubicacion ?? ""}}")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
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
      <form id="frmEliminarGenerico" action="{{URL::to('eliminarcliente')}}"  style="display: none" method="POST" >
        {{csrf_field()}}
        <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
      </form>    
    </div>
  </section>
    
  </div>

  
   <!-- modal modal de agregar proveedor -->
<div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="{{URL::to('guardarcliente')}}"  autocomplete="off" method="POST" >
            {{csrf_field()}}
            <input type="hidden" id="mdl_generico_ipt_id" name="mdl_generico_ipt_id" value="-1">
            <div class="modal-header">
              
              <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Cliente</h5>
                <button type="button" id='btnSalirFact' class="close" aria-hidden="true"  onclick="cerrarModalGenerico()">x</button>
            </div>
            <div class="modal-body">
              <div class="row">
                  <div class="col-xl-12 col-sm-12">
                    <div class="form-group form-float">
                      <div class="form-line">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control space_input_modal" id="mdl_generico_ipt_nombre" name="mdl_generico_ipt_nombre" required maxlength="50">
                      </div>
                    </div>
                  </div>
                  <div class="col-xl-12 col-sm-12">
                      <div class="form-group form-float">
                          <div class="form-line">
                          <label class="form-label">Teléfono (+506)</label>
                          <input type="number" class="form-control space_input_modal" id="mdl_generico_ipt_tel" name="mdl_generico_ipt_tel"  maxlength="8">
                        </div>
                      </div>
                  </div>
                  <div class="col-xl-12 col-sm-12">
                    <div class="form-group form-float">
                        <div class="form-line">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control space_input_modal" id="mdl_generico_ipt_correo" name="mdl_generico_ipt_correo"  maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 col-sm-12">
                  <div class="form-group form-float" style="display: block;">
                      
                      <label for="mdl_generico_ipt_ubicacion" class="form-label">Ubicación</label>
                      <textarea id="mdl_generico_ipt_ubicacion"  class="space_input_modal" name="mdl_generico_ipt_ubicacion" style="width:100%;" maxlength="300"></textarea>
                    
                  </div>
              </div>
               
                  
              </div>

            </div>
            <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
              <a href="#" class="btn btn-secondary" onclick="cerrarModalGenerico()">Volver</a>
              <input type="submit" class="btn btn-primary" value="Guardar"/>
              <input type="reset" class="btn btn-primary">
            </div>
          </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -- fin modal de agregar sucursal-->
@endsection



@section('script')
  <script src="{{asset("assets/bundles/sweetalert/sweetalert.min.js")}}"></script>
  
  <script src="{{asset("assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js")}}"></script>
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/mant_clientes.js")}}"></script>
  

     
@endsection