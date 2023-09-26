@extends('layout.master')

@section('styles')
<link rel="stylesheet" href='{{asset("assets/bundles/pretty-checkbox/pretty-checkbox.min.css")}}'>
@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="card card-warning">
          <div class="card-header">
            <h4>Movimientos pendientes de recibir</h4>

          </div>
          <div class="card-body">
            <form action="{{URL::to('bitacora/movimientos/inventario/filtro')}}" method="POST">
            {{csrf_field()}}
              <div class="row">
                
              <div class="col-sm-12 col-md-3">
                 
                  <div class="pretty p-switch p-slim" >
                    <input type="checkbox" class="form-control"  id="actualizarAutomatico" />
                    <div class="state p-success">
                      <label>Actualizar automatico</label>
                    </div>
                  </div>
              </div>
             
           
            </div>
          </form>
          <div id="contenedor_gastos" class="row">
            <div id="table_monitor_cont" class="table-responsive">
                <table class="table table-striped" id="tablaMovs">
                    <thead>

                        <tr>

                            <th class="text-center">Tipo Movimiento</th>
                            <th class="text-center">
                                Fecha Solicitud
                            </th>
                            <th class="text-center">
                                Despacho
                            </th>
                            <th class="text-center">
                                Encargado
                            </th>

                        </tr>
                    </thead>

                    <tbody id="tbodyMovimientos">

                        
                    </tbody>

                </table>
            </div>
        </div>
           
          </div>
        </div>
      
      </div>
    </section>
    
  </div>
  <form id="formMovPend" action="{{URL::to('inventario/movimientos/pendiente')}}" style="display: none"  method="POST">
    {{csrf_field()}}
    <input type="hidden" name="idMov" id="idMov" value="-1">
  </form>

  <script>
   
    </script>

@endsection


@section('script')


<script src="{{asset("assets/js/page/datatables.js")}}"></script>
<script src="{{asset("assets/js/inventario/movPendSucursal.js")}}"></script>
<script>
  window.addEventListener("load", initialice, false);
  function initialice() {
    actualizar("{{$data['parametros_generales']->tiempo_refresco_monitor_movimientos}}");
  }
</script>
     
     
@endsection