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
            <h4>Productos</h4>
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
            <form action="{{URL::to('bodega/productos/filtro')}}" method="POST">
            {{csrf_field()}}
            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-4">
                <div class="form-group">
                  <label>Categoría</label>
                  <select class="form-control" id="select_categoria" name="categoria">
                      <option  value="T" selected>Todos</option>
                      @foreach ($data['categorias'] as $i)
                      <option value="{{$i->id ?? -1}}" title="{{$i->categoria ?? ''}}" 
                        @if ($i->id == $data['filtros']['categoria'] )
                              selected
                          @endif
                        >{{$i->categoria ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-4">
                <div class="form-group">
                  <label>Tipo Impuesto</label>
                  <select class="form-control" id="select_impuesto" name="impuesto">
                      <option value="T" selected>Todos</option>
                      @foreach ($data['impuestos'] as $i)
                        <option value="{{$i->id }}" title="{{$i->descripcion ?? ''}}" 
                          @if ($i->id == $data['filtros']['impuesto'] )
                              selected
                          @endif
                          >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Buscar</label>
                  <button type="submit" class="btn btn-primary btn-icon form-control" style="cursor: pointer;"><i class="fas fa-search"></i></button>
                </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Nuevo</label>
                <a href="{{url('bodega/producto/nuevo')}}" class="btn btn-success btn-icon form-control" style="cursor: pointer;color:white;"><i class="fas fa-plus"></i> Agregar</a>
                </div>

              </div>
              
            </div>
          </form>
            <div id="contenedor_productos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaProductos" >
                  <thead>
                    
                  
                    <tr>
                      <th class="text-center">Código</th>
                    
                      <th class="text-center">Descripción</th>
                      <th class="text-center">
                        Categoría 
                      </th>
                      <th class="text-center">
                        Impuestos % 
                      </th>
                      <th class="text-center">Precio</th>
                      <th class="text-center">Precio Mayoreo</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    
                  @foreach($data['productos'] as $g)
                  <tr class="space_row_table" style="cursor: pointer;" onclick='clickProducto("{{$g->id}}")'>
                      
                      <td class="text-center">{{$g->codigo_barra ?? ""}}</td>
                      <td class="text-center">
                        {{$g->nombre}}  
                      </td>
                      <td class="text-center">
                        {{$g->nombre_categoria ?? ''}}
                      </td>
                      <td class="text-center">
                          {{$g->porcentaje_impuesto ?? '0'}} %
                      </td>
                      
                      <td class="text-center">
                        CRC {{number_format($g->precio ?? '0.00',2,".",",")}}
                      </td>
                      <td class="text-center">
                        CRC {{number_format($g->precio_mayoreo ?? '0.00',2,".",",")}}
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
    <form id="formEditarProducto" action="{{URL::to('bodega/producto/editar')}}" style="display: none"  method="POST">
      {{csrf_field()}}
      <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
    </form>
  </div>
  
  <script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      var categoria= $("#select_categoria option[value='" +"{{$data['filtros']['categoria']}}"+ "']").html();
      var impuesto= $("#select_impuesto option[value='" +"{{$data['filtros']['impuesto']}}"+ "']").html();

      var topMesage = 'Reporte de Productos';
      var bottomMesage = 'Reporte de productos filtrado por';
    
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

      bottomMesage += ' Desarrollado por Space Software CR. ';
     
     
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
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_el_amanecer'
          }, {
            extend: 'pdf',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_el_amanecer'
          }, {
            extend: 'print',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_el_amanecer'
          }
        ]
      });

    }
    </script>


@endsection



@section('script')
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/bodega/productos.js")}}"></script>
  

     
@endsection