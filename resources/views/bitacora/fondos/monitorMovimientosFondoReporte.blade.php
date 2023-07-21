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
            <div class="row" >
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Sucursal</label>
                  <select class="form-control" readonly id="sucursal" name="sucursal">
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
                  <input type="date" readonly class="form-control" id="desdeFecha" name="desde" value="{{$data['filtros']['desde']  ?? date('Y-m-d')}}"/>
                   
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Hasta</label>
                  <input type="date" readonly class="form-control"  id="hastaFecha" name="hasta"  value="{{$data['filtros']['hasta']  ?? date("Y-m-d")}}"/>
                </div>
              </div>
           
            </div>
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
    window.addEventListener("load", initialice, false);
    function initialice() {
      updateTable();
  
    }

    function updateTable(){

      var sucursal= $("#sucursal option[value='" +"{{$data['filtros']['sucursal']}}"+ "']").html();
  
      var topMesage = 'Monitoreo de movimientos';
      var bottomMesage = 'Monitoreo de movimientos filtrado por';
    
      if("{{$data['filtros']['desde']}}" != ''){
        topMesage += ' desde el '+"{{$data['filtros']['desde']}}";
      }
      if("{{$data['filtros']['hasta']}}" != ''){
        topMesage += ' hasta el '+"{{$data['filtros']['hasta']}}";
      }
      topMesage += '.'+' Solicitud realizada por '+"{{session('usuario')['usuario']}}"+'.';

      if("{{$data['filtros']['sucursal']}}" != 'T'){
        bottomMesage += ' sucursal [ '+sucursal+' ],';
      }else{
        bottomMesage += ' sucursal [ Todas ].';
      }

      bottomMesage += ' Desarrollado por Space Software CR. ';

       $('#tablaMonitorMov').DataTable({
        dom: 'Bfrtip',
        "searching": false,
        "paging": false,
        buttons: [
          {
            extend: 'excel',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'monitor_movimientos_el_amanecer'
          }, {
            extend: 'pdf',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'monitor_movimientos_el_amanecer'
          }, {
            extend: 'print',
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'monitor_movimientos_el_amanecer'
          }
        ]
      });
    }
    </script>

@endsection


@section('script')
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
 
  

     
@endsection