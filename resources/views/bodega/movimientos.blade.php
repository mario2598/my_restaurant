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
            <h4>Movimientos de inventario</h4>
   
          </div>
          
          <div class="card-body">
            <form action="{{URL::to('bodega/productos/filtro')}}" method="POST">
            {{csrf_field()}}
            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Tipo de movimiento</label>
                  <select class="form-control" id="tipo_movimiento" name="tipo_movimiento">
                      <option  value="T" selected>Todos</option>
                      @foreach ($data['tipos_movimiento'] as $i)
                      <option value="{{$i->id ?? -1}}" title="{{$i->codigo ?? ''}}" 
                        @if ($i->id == $data['filtros']['tipo_movimiento'] )
                              selected
                          @endif
                        >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Despacho</label>
                  <select class="form-control" id="select_despacho" name="despacho">
                      <option value="T" selected>Todos</option>
                      @foreach ($data['sucursales'] as $i)
                        <option value="{{$i->id }}" title="{{$i->descripcion ?? ''}}" 
                          @if ($i->id == $data['filtros']['despacho'] )
                              selected
                          @endif
                          >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Destino</label>
                  <select class="form-control" id="select_destino" name="destino">
                      <option value="T" selected>Todos</option>
                      @foreach ($data['sucursales'] as $i)
                        <option value="{{$i->id }}" title="{{$i->descripcion ?? ''}}" 
                          @if ($i->id == $data['filtros']['destino'] )
                              selected
                          @endif
                          >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Estado</label>
                  <select class="form-control" id="select_estado" name="estado">
                      <option value="T" selected>Todos</option>
                      <option value="P" title="Pendiente de completar" 
                        @if ($data['filtros']['estado'] == 'P')
                            selected
                        @endif
                        >Pendiente
                      </option>
                      <option value="T" title="Movimiento Terminado" 
                        @if ($data['filtros']['estado'] == 'T')
                            selected
                        @endif
                        >Terminado
                      </option>
                      <option value="C" title="Cancelado" 
                        @if ($data['filtros']['estado'] == 'C')
                            selected
                        @endif
                        >Cancelado
                      </option>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Buscar</label>
                  <button type="submit" class="btn btn-primary btn-icon form-control" style="cursor: pointer;"><i class="fas fa-search"></i></button>
                </div>
              </div>
              
            </div>
          </form>
            <div id="contenedor_productos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaMovs" >
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
      var destino= $("#select_destino option[value='" +"{{$data['filtros']['destino']}}"+ "']").html();
      var despacho= $("#select_despacho option[value='" +"{{$data['filtros']['despacho']}}"+ "']").html();
      var tipo_mov= $("#tipo_movimiento option[value='" +"{{$data['filtros']['tipo_movimiento']}}"+ "']").html();
      var estado= $("#select_estado option[value='" +"{{$data['filtros']['estado']}}"+ "']").html();

      var topMesage = 'Reporte de Movimientos de Inventario';
      var bottomMesage = 'Reporte de movimientos de inventario filtrado por';
    
      topMesage += '.'+' Solicitud realizada por '+"{{session('usuario')['usuario']}}"+'.';

      if("{{$data['filtros']['estado']}}" != 'T'){
        bottomMesage += ' estado [ '+estado+' ],';
      }else{
        bottomMesage += ' estado [ Todos ],';
      }

      if("{{$data['filtros']['tipo_movimiento']}}" != 'T'){
        bottomMesage += ' Tipo de movimiento [ '+tipo_mov+' ],';
      }else{
        bottomMesage += ' Tipo de movimiento [ Todos ],';
      }

      if("{{$data['filtros']['destino']}}" != 'T'){
        bottomMesage += ' destino [ '+destino+' ],';
      }else{
        bottomMesage += ' destino [ Todos ],';
      }

      if("{{$data['filtros']['despacho']}}" != 'T'){
        bottomMesage += ' despacho [ '+despacho+' ].';
      }else{
        bottomMesage += 'despacho [ Todos ].';
      }

      bottomMesage += ' Desarrollado por Space Software CR. ';
     
     
      $('#tablaMovs').DataTable({
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

<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/bodega/productos.js")}}"></script>
  

     
@endsection