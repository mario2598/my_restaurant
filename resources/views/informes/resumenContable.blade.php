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
            <h4>Resumen Contable</h4>

          </div>
          <div class="card-body">
            <form action="{{URL::to('informes/resumencontable/filtro')}}" method="POST">
            {{csrf_field()}}
              <div class="row">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Sucursal</label>
                  <select class="form-control" id="select_sucursal" name="sucursal">
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
                  <input type="date" class="form-control" name="desde" value="{{$data['filtros']['desde']  ?? ''}}"/>
                   
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Hasta</label>
                  <input type="date" class="form-control" name="hasta"  value="{{$data['filtros']['hasta']  ?? ''}}"/>
                </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Buscar</label>
                  <button type="submit" class="btn btn-primary btn-icon form-control" style="cursor: pointer;"><i class="fas fa-search"></i></button>
                </div>
              </div>
           
            </div>
          </form>
            <div id="contenedor_gastos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaFondos" >
                  <thead>
                  
                    <tr>
                    
                      <th class="text-center">Descripción</th>
                      <th class="text-center">
                          Cafetería
                      </th>
                      <th class="text-center">
                          Panadería
                      </th>
                      <th class="text-center">General</th>
                      
                    </tr>
                  </thead>

                  <tbody>
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-center"><strong>Ingresos SINPE</strong></td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalIngresosSinpeCafeteria'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalIngresosSinpe'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalIngresosSinpeGeneral'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                    </tr>
                 
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-center"><strong>Ingresos Tarjeta</strong></td>
                    
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalIngressosTarjeta'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalIngresosTarjetaGeneral'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                    </tr>
                 
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-center"><strong>Ingresos Efectivo</strong></td>
                     
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalIngresosEfectivo'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalIngresosEfectivoGeneral'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                    </tr>
                   
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-center"><strong>SubTotal Fondos</strong></td>
                     
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['subTotalFondos'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['subTotalFondosGeneral'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                    </tr>
                
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-center"><strong>Total Gastos</strong></td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['gastos'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['gastosGeneral'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                    </tr>

                    

                    
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-left"><strong>Resumen</strong></td>
                      <td class="text-center">
                        
                      </td>
                      <td class="text-center">
                        
                      </td>
                      <td class="text-center">
                        
                      </td>
                    </tr>
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-center"><strong>Rebajo por tarjetas</strong></td>
                      <td class="text-center">
                        <strong>- CRC {{number_format($data['resumen']['totalPagoTarjetaCafeteria'] ?? '0.00',2,".",",")}}</strong>

                      </td>
                      <td class="text-center">
                        <strong>- CRC {{number_format($data['resumen']['totalPagoTarjeta'] ?? '0.00',2,".",",")}}</strong>

                      </td>
                      <td class="text-center">
                        <strong>- CRC {{number_format($data['resumen']['totalPagoTarjetaGeneral'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                    </tr>
                  
                  </tbody>
                  <tfoot> 
                    <tr class="space_row_table" style="cursor: pointer;" >
                      <td class="text-center"><strong>Total General Fondos</strong></td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalFondosCafeteria'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalFondos'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center">
                        <strong>CRC {{number_format($data['resumen']['totalFondosGeneral'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                    </tr>
                  </tfoot>
   
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
      var sucursal= $("#select_sucursal option[value='" +"{{$data['filtros']['sucursal']}}"+ "']").html();
  
      var topMesage = 'Resumen General Contable';
      var bottomMesage = 'Resumen general contable filtrado por';
     
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
    
     
      $('#tablaFondos').DataTable({
        dom: 'Bfrtip',
        "searching": false,
        "paging": false,
         
        buttons: [
          {
            extend: 'excel',
            title: 'COFFEE TO GO',
            messageTop:topMesage,
            footer: true,
            messageBottom:bottomMesage,
            filename: 'resumen_contable_el_amanecer'
          }, {
            extend: 'pdf',
            title: 'COFFEE TO GO',
            messageTop:topMesage,
            footer: true,
            messageBottom:bottomMesage,
            filename: 'resumen_contable_el_amanecer'
          }, {
            extend: 'print',
            title: 'COFFEE TO GO',
            messageTop:topMesage,
            footer: true,
            messageBottom:bottomMesage,
            filename: 'resumen_contable_el_amanecer'
          }
        ]
      });
  
    }
    </script>

@endsection


@section('script')
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/gastos_admin.js")}}"></script>
  

     
@endsection