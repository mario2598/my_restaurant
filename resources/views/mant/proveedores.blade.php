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
          <h4>Proveedores</h4>
          <form class="card-header-form">
            <div class="input-group">
              <input type="text" name="" id="input_buscar_proveedor" class="form-control" placeholder="Buscar..">
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
                <a class="btn btn-primary" title="Agregar proveedor" style="color:white;" onclick="nuevoProveedor()">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                  
                <thead>
                  <tr>
                    
                    <th class="space-align-center">Proveedor</th>
                    <th class="space-align-center">Descripción</th>
                    <th class="space-align-center">Acciones</th>
                   
                  </tr>
                </thead>
                <tbody id="tbody_proveedor">
                  @foreach($data['proveedores'] as $p)
                <tr>
                  
                  <td class="space-align-center">
                    {{$p->nombre ?? ""}}
                  </td>
                  <td class="space-align-center">
                    {{$p->descripcion ?? ""}}
                  </td>
                  <td class="space-align-center" >
                    <a onclick='editarProveedor("{{$p->id}}","{{$p->nombre ?? ""}}","{{$p->descripcion ?? ""}}")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
                    <a onclick="eliminarProveedor({{$p->id}})" title="Eliminar" class="btn btn-danger" style="color:white"> <i class="fa fa-trash"></i></a>
                  </td>
                </tr>
    
                @endforeach
              
              </tbody>
            </table>
          </div>
          </div>
         
        </div>
      </div>
    
    </div>
  </section>

  <form id="frmEliminarProveedor" action="{{URL::to('eliminarproveedor')}}"  style="display: none" method="POST" >
    {{csrf_field()}}
    <input type="hidden" name="idProveedorEliminar" id="idProveedorEliminar" value="">
  </form>
    
  </div>

  
   <!-- modal modal de agregar proveedor -->
<div class="modal fade bs-example-modal-center" id='mdl_proveedor' tabindex="-1" role="dialog"
aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="{{URL::to('guardarproveedor')}}"  autocomplete="off" method="POST" >
            {{csrf_field()}}
            <input type="hidden" id="mdl_proveedor_ipt_id" name="mdl_proveedor_ipt_id" value="-1">
            <div class="modal-header">
              
              <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Proveedor</h5>
                <button type="button" id='btnSalirFact' class="close" aria-hidden="true"  onclick="cerrarModalProveedor()">x</button>
            </div>
            <div class="modal-body">
              <div class="row">
                  <div class="col-xl-12 col-sm-12">
                    <div class="form-group form-float">
                      <div class="form-line">
                        <label class="form-label">Nombre Proveedor</label>
                        <input type="text" class="form-control space_input_modal" id="mdl_proveedor_ipt_nombre" name="mdl_proveedor_ipt_nombre" required maxlength="50">
                        <span id='mdl_spam_nombre' style='color:red; display:none;'></span>

                      </div>
                  </div>
                  </div>
                  <div class="col-xl-12 col-sm-12">
                      <div class="form-group form-float">
                          <div class="form-line">
                          <label class="form-label">Descripción </label>
                          <input type="text" class="form-control space_input_modal" id="mdl_proveedor_ipt_descripcion" name="mdl_proveedor_ipt_descripcion" maxlength="200">
                          <span id='mdl_spam_descripcion' style='color:red; display:none;'></span>

                          </div>
                      </div>
                  </div>
               
                  
              </div>

            </div>
            <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
              <a href="#" class="btn btn-secondary" onclick="cerrarModalProveedor()">Volver</a>
              <input type="submit" class="btn btn-primary" value="Guardar"/>
                     
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
  <script src="{{asset("assets/js/mant_proveedores.js")}}"></script>
  

     
@endsection