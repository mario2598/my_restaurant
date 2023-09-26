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
            <h4>Monitor de movimientos</h4>

          </div>
          <div class="card-body">
            <form action="{{URL::to('bitacora/movimientos/fondos/filtro')}}" method="POST">
            {{csrf_field()}}
              <div class="row">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Sucursal</label>
                  <select class="form-control" id="sucursal" name="sucursal">
                      <option value="T" selected>Todos</option>
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
                    
                      <th class="text-center">Movimiento</th>
                      <th class="text-center">
                          Usuario
                      </th>
                      <th class="text-center">
                          Fecha
                      </th>
                      <th class="text-center">Detalle movimiento</th>
                      <th class="text-center">Monto</th>
                      <th class="text-center">Sucursal</th>
                      
                    </tr>
                  </thead>

                  <tbody id="tbodyBitacoraMovimientos">
                    @foreach ($data['movimientos'] as $i)
                    <tr class="space_row_table" style="cursor: pointer;" 
                      @if ($i->tabla == 'ingreso')
                        onclick='clickIngreso("{{$i->id_entidad}}")'
                      @endif
                      @if ($i->tabla == 'gasto')
                        onclick='clickGasto("{{$i->id_entidad}}")'
                      @endif
                    >
                      <td class="text-center">
                        {{strtoupper($i->tabla ?? '')}}
                      </td>
                      <td class="text-center">
                        {{strtoupper($i->usuario ?? '')}}
                      </td>
                      <td class="text-center">
                        {{$i->fecha ?? ''}}
                      </td>
                      <td class="text-center">
                        {{$i->tipo ?? ''}}
                      </td>
                      <td class="text-center">
                        CRC {{number_format($i->total ?? '0.00',2,".",",")}}
                      </td>
                      <td class="text-center">
                        {{$i->sucDes ?? ''}}
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

  <script>
   
    </script>

@endsection


@section('script')


<script src="{{asset("assets/js/page/datatables.js")}}"></script>
<script src="{{asset("assets/js/bitacora/monitoreoMovimientosFondos.js")}}"></script>
<script>
  window.addEventListener("load", initialice, false);
  function initialice() {
    actualizar("{{$data['parametros_generales']->tiempo_refresco_monitor_movimientos}}");
  }
</script>
     
@endsection