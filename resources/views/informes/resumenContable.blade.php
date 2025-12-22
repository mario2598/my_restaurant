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
            <form action="{{URL::to('informes/resumencontable/filtro')}}" method="POST" id="formResumenContable">
            {{csrf_field()}}
              <div class="row">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label><i class="fas fa-building"></i> Sucursal</label>
                  <select class="form-control" id="select_sucursal" name="sucursal">
                      <option value="T" @if($data['filtros']['sucursal'] == 'T' || empty($data['filtros']['sucursal'])) selected @endif>Todos</option>
                      @foreach ($data['sucursales'] as $i)
                        <option value="{{$i->id ?? ''}}" title="{{$i->descripcion ?? ''}}" 
                          @if (isset($data['filtros']['sucursal']) && $i->id == $data['filtros']['sucursal'])
                              selected
                          @endif
                          >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>

              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label><i class="fas fa-calendar-alt"></i> Desde</label>
                  <input type="date" class="form-control" name="desde" id="fecha_desde" value="{{$data['filtros']['desde']  ?? ''}}" max="{{date('Y-m-d')}}"/>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label><i class="fas fa-calendar-alt"></i> Hasta</label>
                  <input type="date" class="form-control" name="hasta" id="fecha_hasta" value="{{$data['filtros']['hasta']  ?? ''}}" max="{{date('Y-m-d')}}"/>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-icon form-control" style="cursor: pointer;" title="Buscar resumen">
                      <i class="fas fa-search"></i> Buscar
                    </button>
                    <button type="button" class="btn btn-secondary btn-icon" style="cursor: pointer;" onclick="limpiarFiltros()" title="Limpiar filtros">
                      <i class="fas fa-redo"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="button" class="btn btn-success btn-icon form-control" style="cursor: pointer;" onclick="generarReportePDF()" title="Generar reporte PDF completo">
                    <i class="fas fa-file-pdf"></i> Generar Reporte PDF
                  </button>
                </div>
              </div>
           
            </div>
          </form>
          
          @if(isset($data['resumen']) && !empty($data['filtros']['desde']) && !empty($data['filtros']['hasta']))
            <div class="alert alert-info mt-3">
              <i class="fas fa-info-circle"></i> 
              <strong>Período consultado:</strong> 
              Desde {{date('d/m/Y', strtotime($data['filtros']['desde']))}} 
              hasta {{date('d/m/Y', strtotime($data['filtros']['hasta']))}}
              @if($data['filtros']['sucursal'] != 'T')
                | <strong>Sucursal:</strong> {{$data['sucursales']->where('id', $data['filtros']['sucursal'])->first()->descripcion ?? 'N/A'}}
              @endif
            </div>
          @elseif(isset($data['resumen']) && (empty($data['filtros']['desde']) || empty($data['filtros']['hasta'])))
            <div class="alert alert-warning mt-3">
              <i class="fas fa-exclamation-triangle"></i> 
              <strong>Nota:</strong> Para obtener un resumen preciso, por favor seleccione un rango de fechas (Desde y Hasta).
            </div>
          @endif
            <div id="contenedor_gastos" class="row mt-4">
              <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaFondos" >
                  <thead class="thead-dark">
                    <tr>
                      <th class="text-center" style="width: 60%;"><i class="fas fa-list"></i> Descripción</th>
                      <th class="text-center" style="width: 40%;"><i class="fas fa-money-bill-wave"></i> MONTO</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr class="space_row_table">
                      <td class="text-left">
                        <i class="fas fa-mobile-alt text-primary"></i> <strong>Ingresos SINPE</strong>
                      </td>
                      <td class="text-right">
                        <strong class="text-success">CRC {{number_format($data['resumen']['totalIngresosSinpeGeneral'] ?? 0, 2, ".", ",")}}</strong>
                      </td>
                    </tr>
                 
                    <tr class="space_row_table">
                      <td class="text-left">
                        <i class="fas fa-credit-card text-info"></i> <strong>Ingresos Tarjeta</strong>
                      </td>
                      <td class="text-right">
                        <strong class="text-success">CRC {{number_format($data['resumen']['totalIngresosTarjetaGeneral'] ?? 0, 2, ".", ",")}}</strong>
                      </td>
                    </tr>
                 
                    <tr class="space_row_table">
                      <td class="text-left">
                        <i class="fas fa-money-bill text-success"></i> <strong>Ingresos Efectivo</strong>
                      </td>
                      <td class="text-right">
                        <strong class="text-success">CRC {{number_format($data['resumen']['totalIngresosEfectivoGeneral'] ?? 0, 2, ".", ",")}}</strong>
                      </td>
                    </tr>

                    <tr class="space_row_table" style="background-color: #f8f9fa;">
                      <td class="text-left">
                        <i class="fas fa-calculator text-primary"></i> <strong>SubTotal Fondos</strong>
                      </td>
                      <td class="text-right">
                        <strong class="text-primary">CRC {{number_format($data['resumen']['subTotalFondosGeneral'] ?? 0, 2, ".", ",")}}</strong>
                      </td>
                    </tr>
                
                    <tr class="space_row_table">
                      <td class="text-left">
                        <i class="fas fa-shopping-cart text-danger"></i> <strong>Total Gastos</strong>
                      </td>
                      <td class="text-right">
                        <strong class="text-danger">CRC {{number_format($data['resumen']['gastosGeneral'] ?? 0, 2, ".", ",")}}</strong>
                      </td>
                    </tr>
                  
                  </tbody>
                  <tfoot class="thead-dark"> 
                    <tr class="space_row_table" style="background-color: #343a40; color: white;">
                      <td class="text-left">
                        <i class="fas fa-coins"></i> <strong>Total General Fondos</strong>
                      </td>
                      <td class="text-right">
                        <strong style="font-size: 1.2em;">CRC {{number_format($data['resumen']['totalFondosGeneral'] ?? 0, 2, ".", ",")}}</strong>
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
      var sucursal = $("#select_sucursal option[value='" + "{{$data['filtros']['sucursal'] ?? 'T'}}" + "']").html();
  
      var topMesage = 'Resumen General Contable';
      var bottomMesage = 'Resumen general contable filtrado por';
     
      if("{{$data['filtros']['desde'] ?? ''}}" != ''){
        topMesage += ' desde el ' + "{{$data['filtros']['desde']}}";
      }
      if("{{$data['filtros']['hasta'] ?? ''}}" != ''){
        topMesage += ' hasta el ' + "{{$data['filtros']['hasta']}}";
      }
      topMesage += '.' + ' Solicitud realizada por ' + "{{session('usuario')['usuario'] ?? 'Usuario'}}" + '.';
  
      if("{{$data['filtros']['sucursal'] ?? 'T'}}" != 'T'){
        bottomMesage += ' sucursal [ ' + sucursal + ' ],';
      }else{
        bottomMesage += ' sucursal [ Todas ].';
      }

      bottomMesage += ' Desarrollado por Space Software CR. ';
    
      // Validar fechas antes de enviar
      $('#formResumenContable').on('submit', function(e) {
        var desde = $('#fecha_desde').val();
        var hasta = $('#fecha_hasta').val();
        
        if (desde && hasta && new Date(desde) > new Date(hasta)) {
          e.preventDefault();
          alert('La fecha "Desde" no puede ser mayor que la fecha "Hasta".');
          return false;
        }
      });
      
      // Validar que hasta no sea mayor que hoy
      $('#fecha_hasta').on('change', function() {
        var hasta = $(this).val();
        var desde = $('#fecha_desde').val();
        var hoy = new Date().toISOString().split('T')[0];
        
        if (hasta > hoy) {
          alert('La fecha "Hasta" no puede ser mayor que la fecha actual.');
          $(this).val(hoy);
        }
        
        if (desde && hasta && new Date(desde) > new Date(hasta)) {
          alert('La fecha "Desde" no puede ser mayor que la fecha "Hasta".');
          $('#fecha_desde').val('');
        }
      });
      
      $('#fecha_desde').on('change', function() {
        var desde = $(this).val();
        var hasta = $('#fecha_hasta').val();
        var hoy = new Date().toISOString().split('T')[0];
        
        if (desde > hoy) {
          alert('La fecha "Desde" no puede ser mayor que la fecha actual.');
          $(this).val(hoy);
        }
        
        if (desde && hasta && new Date(desde) > new Date(hasta)) {
          alert('La fecha "Desde" no puede ser mayor que la fecha "Hasta".');
          $('#fecha_hasta').val('');
        }
      });
     
      $('#tablaFondos').DataTable({
        dom: 'Bfrtip',
        "searching": false,
        "paging": false,
        "ordering": false,
        "info": false,
        buttons: [
          {
            extend: 'excel',
            title: 'SPACE REST',
            messageTop: topMesage,
            footer: true,
            messageBottom: bottomMesage,
            filename: 'resumen_contable_el_amanecer'
          }, {
            extend: 'pdf',
            title: 'SPACE REST',
            messageTop: topMesage,
            footer: true,
            messageBottom: bottomMesage,
            filename: 'resumen_contable_el_amanecer'
          }, {
            extend: 'print',
            title: 'SPACE REST',
            messageTop: topMesage,
            footer: true,
            messageBottom: bottomMesage,
            filename: 'resumen_contable_el_amanecer'
          }
        ]
      });
    }
    
    function limpiarFiltros() {
      $('#select_sucursal').val('T');
      $('#fecha_desde').val('');
      $('#fecha_hasta').val('');
    }
    
    function generarReportePDF() {
      // Obtener valores del formulario
      var sucursal = $('#select_sucursal').val();
      var desde = $('#fecha_desde').val();
      var hasta = $('#fecha_hasta').val();
      
      // Crear formulario temporal para enviar POST
      var form = $('<form>', {
        'method': 'POST',
        'action': '{{ URL::to("informes/resumencontable/generar-pdf") }}',
        'target': '_blank'
      });
      
      form.append($('<input>', {
        'type': 'hidden',
        'name': '_token',
        'value': '{{ csrf_token() }}'
      }));
      
      form.append($('<input>', {
        'type': 'hidden',
        'name': 'sucursal',
        'value': sucursal
      }));
      
      form.append($('<input>', {
        'type': 'hidden',
        'name': 'desde',
        'value': desde
      }));
      
      form.append($('<input>', {
        'type': 'hidden',
        'name': 'hasta',
        'value': hasta
      }));
      
      $('body').append(form);
      form.submit();
      form.remove();
    }
    </script>

@endsection


@section('script')

<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/gastos_admin.js")}}"></script>
  

     
@endsection