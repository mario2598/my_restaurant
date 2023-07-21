@extends('layout.master')

@section('style')

<link rel="stylesheet" href="{{asset("assets/bundles/datatables/datatables.min.css")}}">
  <link rel="stylesheet" href="{{asset("assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css")}}">
@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <div class="section-body">
        
        <div class="card card-warning">
          <div class="card-header">
            <h4>Menú de restaurante RES-{{$data['restaurante']->id ?? "##"}} de la sucursal {{$data['restaurante']->sucursal_descripcion ?? ""}}</h4>
            <form class="card-header-form">
              <div class="input-group">
                <input type="text" name=""  id="btn_buscar_pro" class="form-control" placeholder="Buscar producto">
                <div class="input-group-btn">
                  <a class="btn btn-primary btn-icon" style="cursor: pointer;"><i class="fas fa-search"></i></a>
                </div>
              </div>
            </form>
          </div>
          
          <div class="card-body">
            
            <div class="row" style="width: 100%">

              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Agregar producto menú</label>
                <a class="btn btn-success btn-icon form-control" style="cursor: pointer;color:white;" onclick="$('#mdl_generico').modal('show');"><i class="fas fa-plus"></i> Agregar menú</a>
                </div>

              </div>
              
            </div>
        
            <div id="contenedor_productos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaMenus" >
                  <thead>
                    
                  
                    <tr>
                      <th class="text-center">Código</th>
                    
                      <th class="text-center">Nombre</th>
                      <th class="text-center">
                        Descripción
                      </th>
                      <th class="text-center">
                        Categoría
                      </th>
                      <th class="text-center">Precio</th>
                      <th class="text-center">Acciones</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    @foreach($data['menusRestaurante'] as $g)
                    <tr class="space_row_table" style="cursor: pointer;" onclick='clickProducto("{{$g->id}}")'>
                        
                        <td class="text-center">{{$g->codigo ?? ""}}</td>
                        <td class="text-center">
                          {{$g->nombre}}  
                        </td>
                        <td class="text-center">
                          {{$g->descripcion ?? ''}}
                        </td>
                        <td class="text-center">
                            {{$g->nombre_categoria ?? ''}}
                        </td>
                        
                        <td class="text-center">
                          CRC {{number_format($g->precio ?? '0.00',2,".",",")}}
                        </td>

                        <td class="text-center">
                            <a  style="cursor: pointer; color: white;" class="btn btn-primary" onclick="eliminarProdcutoDeMenu('{{$g->id}}')" >Eliminar del menú</a>
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

  </div>
  <form id="form_eliminar_menu" action="{{URL::to('restaurante/menus/productos/eliminar')}}"  autocomplete="off" method="POST" >
    {{csrf_field()}}
    <input type="hidden" name="restaurante_id_eliminar" value="{{$data['restaurante']->id ?? "-1"}}">
    <input type="hidden" name="producto_menu_eliminar" id="producto_menu_eliminar" value="-1">
  </form>
  <!-- modal modal de agregar menus -->
<div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="{{URL::to('restaurante/menus/productos/agregar')}}"  autocomplete="off" method="POST" >
            {{csrf_field()}}
          
            <div class="modal-header">
              
              <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Productos de menú disponibles</h5>
                <button type="button" id='btnSalirFact' class="close" aria-hidden="true"  onclick="cerrarModalGenerico()">x</button>
            </div>
            <div class="modal-body">
              <div class="row">
                <input type="hidden" name="restaurante_id" value="{{$data['restaurante']->id ?? "-1"}}">
                  <div class="col-xl-12 col-sm-12">
                    <select class="form-control" id="prodcuto_menu" name="prodcuto_menu" required>
                      <option value="-1" selected>Seleccione un producto</option>
                      @foreach ($data['productos_menu'] as $i)
                        <option value="{{$i->id ?? ''}}" title="{{$i->descripcion?? ''}}" 
                          >{{$i->nombre ?? ''}}</option>
                     @endforeach
                  </select>
                  </div>
              </div>

            </div>
            <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
              <a href="#" class="btn btn-secondary" >Volver</a>
              <input type="submit" class="btn btn-primary" value="Agregar"/>
                     
            </div>
          </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -- fin modal de agregar sucursal-->
  
  <script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      var topMesage = 'Reporte de Menús';
      var bottomMesage = 'Reporte de Menús.';
    
      topMesage += '.'+' Solicitud realizada por '+"{{session('usuario')['usuario']}}"+'.';

      bottomMesage += ' Desarrollado por Space Software CR. ';
     
     
      $('#tablaMenus').DataTable({
        dom: 'Bfrtip',
        "searching": false,
        "paging": false,
        'fixedHeader': {
    'header': true,
    'footer': true
  },
        buttons: [
          {
            extend: 'excel',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_menu_el_amanecer'
          }, {
            extend: 'pdf',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_menu_el_amanecer'
          }, {
            extend: 'print',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_menu_el_amanecer'
          }
        ]
      });

    }
    </script>


@endsection



@section('script')
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/restaurante/menus/editar.js")}}"></script>
  

     
@endsection