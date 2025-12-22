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
            <h4>Productos Materia Prima</h4>
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
              <div class="col-sm-12 col-md-4">
                <div class="form-group">
                  <label>Nuevo</label>
                <a class="btn btn-success btn-icon form-control" style="cursor: pointer;color:white;" onclick="abrirModalNuevoProducto()"><i class="fas fa-plus"></i> Agregar</a>
                </div>
              </div>
              
            </div>
         
            <div id="contenedor_productos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaProductos" >
                  <thead>
                    
                  
                    <tr>
                     
                      <th class="text-center">Producto</th>
                      <th class="text-center">
                        Unidad Medida 
                      </th>
                      <th class="text-center">Precio</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    
                  @foreach($data['productos'] as $g)
                  <tr class="space_row_table" style="cursor: pointer;" onclick='editarProducto("{{$g->id}}")'>
                      
                      <td class="text-center">
                        {{$g->nombre}}  
                      </td>
                      <td class="text-center">
                        {{$g->unidad_medida ?? ''}}
                      </td>
                    
                      <td class="text-center">
                        CRC {{number_format($g->precio ?? '0.00',2,".",",")}}
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
    <form id="formEditarProducto" action="{{URL::to('materiaPrima/producto/editar')}}" style="display: none"  method="POST">
      {{csrf_field()}}
      <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
    </form>
  </div>

  <!-- Modal para Nuevo/Editar Producto -->
  <div class="modal fade" id="modalNuevoProducto" tabindex="-1" role="dialog" aria-labelledby="modalNuevoProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title" id="modalNuevoProductoLabel" style="color: white;">
            <i class="fas fa-plus-circle" id="icono_modal"></i> <span id="titulo_modal">Nuevo Producto Materia Prima</span>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formNuevoProducto" autocomplete="off">
          {{ csrf_field() }}
          <input type="hidden" name="id" id="id_producto_mp" value="-1">
          <div class="modal-body">
            <div class="row">
              <!-- Nombre -->
              <div class="col-sm-12 col-md-12">
                <div class="form-group">
                  <label>* Nombre <small class="text-muted">(máximo 5000 caracteres)</small></label>
                  <textarea class="form-control" name="nombre" id="nombre_mp" maxlength="5000" rows="3" required placeholder="Ingrese el nombre del producto"></textarea>
                  <small class="form-text text-muted">Descripción completa del producto</small>
                </div>
              </div>

              <!-- Unidad de medida -->
              <div class="col-sm-12 col-md-6">
                <div class="form-group">
                  <label>* Unidad de medida</label>
                  <input type="text" class="form-control" id="unidad_medida_mp" name="unidad_medida" required maxlength="100" placeholder="Ej: Kg, L, Unid">
                  <small class="form-text text-muted">Unidad de medida del producto</small>
                </div>
              </div>

              <!-- Proveedor -->
              <div class="col-sm-12 col-md-6">
                <div class="form-group">
                  <label>Proveedor <small class="text-muted">(opcional)</small></label>
                  <select class="form-control" id="proveedor_mp" name="proveedor">
                    <option value="">-- Sin proveedor --</option>
                    @if(isset($data['proveedores']))
                      @foreach ($data['proveedores'] as $proveedor)
                        <option value="{{ $proveedor->id ?? -1 }}" title="{{ $proveedor->descripcion ?? '' }}">
                          {{ $proveedor->nombre ?? '' }}
                        </option>
                      @endforeach
                    @endif
                  </select>
                  <small class="form-text text-muted">Seleccione el proveedor del producto (opcional)</small>
                </div>
              </div>

              <!-- Precio -->
              <div class="col-sm-12 col-md-6">
                <div class="form-group">
                  <label>* Precio (CRC)</label>
                  <input type="number" class="form-control" id="precio_mp" name="precio" step="0.01" required min="0" placeholder="0.00">
                  <small class="form-text text-muted">Precio unitario en colones</small>
                </div>
              </div>

              <!-- Cantidad mínima deseada -->
              <div class="col-sm-12 col-md-6">
                <div class="form-group">
                  <label>Cantidad mínima deseada</label>
                  <input type="number" class="form-control" id="cant_min_mp" name="cant_min" step="0.01" min="0" placeholder="0.00">
                  <small class="form-text text-muted">Stock mínimo recomendado</small>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" id="btn_eliminar_producto" onclick="eliminarProductoDesdeModal()" style="display: none;">
              <i class="fas fa-trash"></i> Eliminar
            </button>
            <div class="ml-auto">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="fas fa-times"></i> Cancelar
              </button>
              <button type="button" class="btn btn-warning" onclick="limpiarFormularioProducto()">
                <i class="fas fa-eraser"></i> Limpiar
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <span id="texto_guardar">Guardar Producto</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      var categoria= $("#select_categoria option[value='" +"{{$data['filtros']['categoria']}}"+ "']").html();
      var impuesto= $("#select_impuesto option[value='" +"{{$data['filtros']['impuesto']}}"+ "']").html();

      var topMesage = 'Reporte de Productos del Menú';
      var bottomMesage = 'Reporte de productos del Menú filtrado por';
    
      topMesage += '.'+' Solicitud realizada por '+"{{session('usuario')['usuario']}}"+'.';

      if("{{$data['filtros']['categoria']}}" != 'T'){
        bottomMesage += ' categoria [ '+categoria+' ],';
      }else{
        bottomMesage += ' categoria [ Todas ],';
      }

      if("{{$data['filtros']['impuesto']}}" != 'T'){
        bottomMesage += ' tipo de impuesto [ '+impuesto+' ],';
      }else{
        bottomMesage += 'tipo de impuesto [ Todos ].';
      }

      bottomMesage += ' {{ env('APP_NAME', 'SPACE SOFTWARE CR') }} ';
     
     
      $('#tablaProductos').DataTable({
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
            title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos'
          }, {
            extend: 'pdf',
            title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos'
          }, {
            extend: 'print',
            title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos'
          }
        ]
      });

    }
    </script>


@endsection



@section('script')
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/materiaPrima/productos.js")}}"></script>
  

     
@endsection