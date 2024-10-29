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
          <h4>Mobiliarios</h4>
          <form class="card-header-form">
            <div class="input-group">
              <input type="text" name="" id="input_buscar_sucursal" class="form-control" placeholder="Buscar..">
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
                <a class="btn btn-primary" title="Agregar Mobiliario" style="color:white;" onclick="nuevoMobiliario()">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                <thead>
                  <tr>
                    
                    <th class="space-align-center">Nombre</th>
                    <th class="space-align-center">Descripción</th>
                    <th class="space-align-center">Personas</th>
                    <th class="space-align-center">Filas</th>
                    <th class="space-align-center">Columnas</th>
                    <th class="space-align-center">Acciones</th>
                  
                  </tr>
                </thead>
                <tbody id="tbody_sucursal">
                  @foreach($data['mobiliario'] as $m)
                <tr>
                  
                  <td class="space-align-center">{{$m->nombre}}</td>
                  <td class="space-align-center">{{$m->descripcion}}</td>
                  <td class="space-align-center">{{$m->cantidad_personas}}</td>
                  <td class="space-align-center">{{$m->tam_filas}}</td>
                  <td class="space-align-center">{{$m->tam_columnas}}</td>
                  
                  <td class="space-align-center" style="width: 8rem">
                    <a title="Editar" onclick="editarMobiliario('{{$m->id}}','{{$m->nombre}}','{{$m->descripcion}}','{{$m->cantidad_personas}}','{{$m->tam_filas}}','{{$m->tam_columnas}}')" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
                    <a title="Eliminar" onclick="eliminarMobiliario({{$m->id}})" class="btn btn-danger" style="color:white"> <i class="fa fa-trash"></i></a>
                  </td>
                </tr>
    
                @endforeach
              
              </tbody>
            </table>
          </div>
          </div>
         
        </div>
      </div>
      <form id="frmEliminarMobiliario" action="{{URL::to('eliminarmobiliario')}}"  style="display: none" method="POST" >
        {{csrf_field()}}
        <input type="hidden" name="idMobiliarioEliminar" id="idMobiliarioEliminar" value="">
      </form>
    </div>
  </section>

  </div>

  
<!-- modal modal de agregar mobiliario -->
<div class="modal fade bs-example-modal-center" id='mdl_mobiliario' tabindex="-1" role="dialog"
aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="{{URL::to('guardarmobiliario')}}"  autocomplete="off" method="POST" >
            {{csrf_field()}}
            <div class="modal-header">
              
              <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Mobiliario</h5>
                <button type="button" class="close" aria-hidden="true"  onclick="cerrarModalMobiliario()">x</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12 col-sm-10">
                        <div class="form-group form-float">
                            <div class="form-line">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control space_input_modal"  id="mdl_mobiliario_ipt_nombre" name="mdl_mobiliario_ipt_nombre" required="true">
                            <span id='mdl_spam_nombre' style='color:red; display:none;'></span>
                            <input type="hidden" id="mdl_mobiliario_ipt_id" name="mdl_mobiliario_ipt_id" value="-1">
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-10">
                        <div class="form-group form-float">
                            <div class="form-line">
                            <label class="form-label">Personas</label>
                            <input type="number" class="form-control space_input_modal"  id="mdl_mobiliario_ipt_personas" name="mdl_mobiliario_ipt_personas" required="true" min="1">
                            <span id='mdl_spam_personas' style='color:red; display:none;'></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-10">
                        <div class="form-group form-float">
                            <div class="form-line">
                            <label class="form-label">Filas</label>
                            <input type="number" class="form-control space_input_modal"  id="mdl_mobiliario_ipt_filas" name="mdl_mobiliario_ipt_filas" required="true" min="1">
                            <span id='mdl_spam_filas' style='color:red; display:none;'></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-10">
                        <div class="form-group form-float">
                            <div class="form-line">
                            <label class="form-label">Columnas</label>
                            <input type="number" class="form-control space_input_modal"  id="mdl_mobiliario_ipt_columnas" name="mdl_mobiliario_ipt_columnas" required="true" min="1">
                            <span id='mdl_spam_columnas' style='color:red; display:none;'></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-sm-10">
                        <div class="form-group form-float">
                            <div class="form-line">
                            <label class="form-label">Descripción</label>
                            <textarea type="text" class="form-control space_input_modal" id="mdl_mobiliario_ipt_descripcion" name="mdl_mobiliario_ipt_descripcion" required="true" maxlength="240"></textarea>
                            <span id='mdl_spam_descripcion' style='color:red; display:none;'></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
              <a href="javascript:void(0)" class="btn btn-secondary" onclick="cerrarModalMobiliario()">Volver</a>
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
  <script src="{{asset("assets/js/mant_mobiliario.js")}}"></script>
  

     
@endsection