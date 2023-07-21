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
            <h4>Tipos de ingreso</h4>
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
                  <a class="btn btn-primary" title="Agregar Tipo Ingreso" style="color:white;" onclick="nuevoGenerico()">+ Agregar</a>
                </div>
              </div>
              
           
            </div>
            <div id="contenedor_gastos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="">
                  <thead>
                    <tr>
                      
                      <th class="space-align-center">#Id</th>
                      <th class="space-align-center">Tipo Ingreso</th>
                      <th class="space-align-center">Acciones</th>
                     
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    @foreach($data['tipos_ingreso'] as $g)
                  <tr>
                    
                    <td class="space-align-center">
                      {{$g->id ?? "###"}}
                    </td>

                    <td class="space-align-center">
                      {{$g->tipo ?? ""}}
                    </td>
                    
                    <td class="space-align-center" >
                      <a onclick='editarGenerico("{{$g->id}}","{{$g->tipo ?? ""}}")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
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
      
      </div>
    </section>
    <form id="frmEliminarGenerico" action="{{URL::to('eliminartipoingreso')}}"  style="display: none" method="POST" >
      {{csrf_field()}}
      <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
    </form>
    
  </div>

 <!-- modal modal de agregar proveedor -->
 <div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
 aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
           <form action="{{URL::to('guardartipoingreso')}}"  autocomplete="off" method="POST" >
             {{csrf_field()}}
             <input type="hidden" id="mdl_generico_ipt_id" name="mdl_generico_ipt_id" value="-1">
             <div class="modal-header">
               
               <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                 <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Tipo Ingreso</h5>
                 <button type="button" id='btnSalirFact' class="close" aria-hidden="true"  onclick="cerrarModalGenerico()">x</button>
             </div>
             <div class="modal-body">
               <div class="row">
                   <div class="col-xl-12 col-sm-12">
                     <div class="form-group form-float">
                       <div class="form-line">
                         <label class="form-label">Tipo Ingreso</label>
                         <input type="text" class="form-control space_input_modal" id="mdl_generico_ipt_tipo" name="mdl_generico_ipt_tipo" required maxlength="50">
                      
                       </div>
                   </div>
                   </div>

               </div>
 
             </div>
             <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
               <a href="#" class="btn btn-secondary" onclick="cerrarModalGenerico()">Volver</a>
               <input type="submit" class="btn btn-primary" value="Guardar"/>
                      
             </div>
           </form>
         </div><!-- /.modal-content -->
     </div><!-- /.modal-dialog -->
 </div><!-- /.modal -- fin modal de agregar sucursal-->
@endsection



@section('script')

  <script src="{{asset("assets/js/tipos_ingreso.js")}}"></script>
  

     
@endsection