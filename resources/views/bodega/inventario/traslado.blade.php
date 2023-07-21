@extends('layout.master')

@section('style')

  <link rel="stylesheet" href="{{asset("assets/bundles/datatables/datatables.min.css")}}">
  <link rel="stylesheet" href="{{asset("assets/bundles/prism/prism.css")}}">
  <link rel="stylesheet" href="{{asset("assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css")}}">
@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <div class="section-body">
        
        <div class="card card-warning">
          <div class="card-header">
            <h4>Traslado Inventario</h4>
            
          </div>
          
          <div class="card-body">
            <form action="{{URL::to('bodega/inventario/inventarios/filtro')}}" method="POST">
            {{csrf_field()}}
            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Sucursal Despacho</label>
                  <select class="form-control" id="sucursal_despacho" name="sucursal_despacho" onchange="cambiarDespacho()" required>
                      <option value="-1" selected>Seleccione una sucursal</option>
                      @foreach ($data['sucursales'] as $i)
                        <option value="{{$i->id ?? ''}}" title="{{$i->descripcion ?? ''}}" 
                          @if ($i->id == $data['filtros']['sucursal'] )
                              selected
                          @endif
                          >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>

              <div class="col-sm-12 col-xs-12 col-md-3">
                <div class="form-group">
                  <label>Abrir inventario </label>
                  <button type="button" id="btn_abrir_inventario" class="btn btn-primary btn-icon form-control" onclick="abrirInventario(sucursal_despacho.value)"><i class="fas fa-book"></i></button>
                </div>
              </div>
              <div class="col-sm-12 col-xs-12 col-md-3">
                <div class="form-group">
                  <label>Sucursal Entrega</label>
                  <select class="form-control" id="sucursal_entrega" name="sucursal_entrega" required>
                      <option value="-1" selected>Seleccione una sucursal</option>
                      @foreach ($data['sucursales'] as $i)
                        <option value="{{$i->id ?? ''}}" title="{{$i->descripcion ?? ''}}" 
                          @if ($i->id == $data['filtros']['sucursal'] )
                              selected
                          @endif
                          >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-xs-12 col-md-3">
                <div class="form-group">
                  <label>Realizar Traslado </label>
                  <button type="button" class="btn btn-primary btn-icon form-control" onclick="iniciarTraslado()"><i class="fas fa-clipboard-check"></i></button>
                </div>
              </div>

              
            </div>
          </form>
            
           
          </div>
        </div>
      
      </div>
      <h3> Pedido </h3>
      <div id="contenedor_productos" class="row">
        <table class="table">
          <thead>
            <tr>
              <th scope="col"># Código</th>
              <th scope="col">Producto</th>
              <th scope="col">Cantidad</th>
              <th scope="col">Acción</th>
            </tr>
          </thead>
          <tbody id="tbody_pedido">
            
          </tbody>
        </table>
    </div>
    </section>
   
    
  </div>

    <!-- Modal cantidad inventarior -->
    <div class="modal fade" id="modal_cantidad_inventario" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCenterTitle">Ingrese la cantidad</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label id="cantidad_inventario_lbl">Cantidad</label><br>
            <small style="color: #da5757;" id="cantidad_inventario_small"></small>

            <input type="number" autofocus id="cantidad_inventario_input" class="form-control creditcard">
          </div>
           
        </div>
        <div class="modal-footer bg-whitesmoke br">
          <button type="button" class="btn btn-primary" onclick="agregarProductoCantidad()">Agregar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Modal inventario -->
  <div class="modal fade" id="modal_inventario" tabindex="-1" role="dialog"
  aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalCenterTitle">Inventario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
         
            <table class="table">
              <thead>
                <tr>
                  <th scope="col"># Código</th>
                  <th scope="col">Producto</th>
                  <th scope="col">Cantidad</th>
                  

                </tr>
              </thead>
              <tbody id="tbody_inventario">
               
              </tbody>
            </table>

      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-primary">Agregar</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
  <script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      var sucursal= $("#sucursal option[value='" +"{{$data['filtros']['sucursal']}}"+ "']").html();
    
      var topMesage = 'Reporte de Inventario de la sucursal '+sucursal;
      var bottomMesage = 'Reporte de Inventario filtrado por';
    
      topMesage += '.'+' Solicitud realizada por '+"{{session('usuario')['usuario']}}"+'.';

      if("{{$data['filtros']['sucursal']}}" != 'T'){
        bottomMesage += ' sucursal [ '+sucursal+' ],';
      }else{
        bottomMesage += ' sucursal [  ],';
      }

     
      bottomMesage += ' Desarrollado por Space Software CR. ';
     
     
      $('#tablaInventarios').DataTable({
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
            filename: 'inventario_'+sucursal+'_el_amanecer'
          }, {
            extend: 'pdf',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'inventario_'+sucursal+'_el_amanecer'
          }, {
            extend: 'print',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'inventario_'+sucursal+'_el_amanecer'
          }
        ]
      });

    }
    </script>


@endsection



@section('script')
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/bodega/traslado.js")}}"></script>
  
  <script src="{{asset("assets/bundles/prism/prism.js")}}"></script>

@endsection