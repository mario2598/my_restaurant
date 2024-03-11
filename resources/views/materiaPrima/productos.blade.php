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
                <a href="{{url('materiaPrima/productos/nuevo')}}" class="btn btn-success btn-icon form-control" style="cursor: pointer;color:white;"><i class="fas fa-plus"></i> Agregar</a>
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
                  <tr class="space_row_table" style="cursor: pointer;" onclick='clickProducto("{{$g->id}}")'>
                      
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

      bottomMesage += ' Coffee To Go CR. ';
     
     
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
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_coffee'
          }, {
            extend: 'pdf',
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_coffee'
          }, {
            extend: 'print',
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_coffee'
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