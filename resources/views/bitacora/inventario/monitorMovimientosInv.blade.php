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
            <h4>Monitor de movimientos de inventario</h4>

          </div>
          <div class="card-body">
            <form action="{{URL::to('bitacora/movimientos/inventario/filtro')}}" method="POST">
            {{csrf_field()}}
              <div class="row">
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
                        <option value="TT" selected>Todos</option>
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
                    <label>Desde</label>
                    <input type="date" class="form-control" id="desdeFecha" name="desde" value="{{$data['filtros']['desde']  ?? date('Y-m-d')}}"/>
                     
                  </div>
                </div>
                <div class="col-sm-12 col-md-3">
                  <div class="form-group">
                    <label>Hasta</label>
                    <input type="date" class="form-control"  id="hastaFecha" name="hasta"  value="{{$data['filtros']['hasta']  ?? date("Y-m-d")}}"/>
                  </div>
                </div>
              <div class="col-sm-12 col-md-3">
                 
                  <div class="pretty p-switch p-slim" >
                    <input type="checkbox" class="form-control"  id="actualizarAutomatico" />
                    <div class="state p-success">
                      <label>Actualizar automatico</label>
                    </div>
                  </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Generar reporte</label>
                  <button type="submit" class="btn btn-primary btn-icon form-control" style="cursor: pointer;"><i class="fas fa-file-alt"></i></button>
                </div>
              </div>
           
            </div>
          </form>
            <div id="contenedor_gastos" class="row">
              <div id="table_monitor_cont" class="table-responsive">
                <table class="table table-striped" id="tablaMonitorMov" >
                  <thead>
                  
                    <tr>
                    
                      <th class="text-center">Tipo Movimiento</th>
                      <th class="text-center">
                        Fecha
                     </th>
                      <th class="text-center">
                          Despacho
                      </th>
                      <th class="text-center">
                          Destino
                      </th>
                      <th class="text-center">
                          Encargado
                      </th>
                      <th class="text-center">Estado</th>
                      
                    </tr>
                  </thead>

                  <tbody id="tbodyBitacoraMovimientos">
                   
                  </tbody>
   
                </table>
              </div> 
            </div>
           
          </div>
        </div>
      
      </div>
    </section>
    
  </div>

  <script>
   
    </script>

@endsection


@section('script')


<script src="{{asset("assets/js/page/datatables.js")}}"></script>
<script src="{{asset("assets/js/bitacora/monitoreoMovimientosInv.js")}}"></script>
<script>
  window.addEventListener("load", initialice, false);
  function initialice() {
    actualizar("{{$data['parametros_generales']->tiempo_refresco_monitor_movimientos}}");
  }
</script>
     
     
@endsection