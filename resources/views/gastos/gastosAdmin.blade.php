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
            <h4>Gastos</h4>
            <form class="card-header-form">
              <div class="input-group">
                <input type="text" name="" id="btn_buscar_gasto" class="form-control" placeholder="Buscar gasto">
                <div class="input-group-btn">
                  <a class="btn btn-primary btn-icon" style="cursor: pointer;" ><i class="fas fa-search"></i></a>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body">
            <form action="{{URL::to('gastos/administracion/filtro')}}" method="POST">
            {{csrf_field()}}
            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Proveedor</label>
                  <select class="form-control" id="select_proveedor" name="proveedor">
                      <option value="0" selected>Todos</option>
                      @foreach ($data['proveedores'] as $i)
                      <option value="{{$i->id ?? -1}}" title="{{$i->descripcion ?? ''}}" 
                        @if ($i->id == $data['filtros']['proveedor'] )
                              selected
                          @endif
                        >{{$i->nombre ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Sucursal</label>
                  <select class="form-control" id="select_sucursal" name="sucursal">
                      <option value="T" selected>Todos</option>
                      @foreach ($data['sucursales'] as $i)
                        <option value="{{$i->descripcion ?? ''}}" title="{{$i->descripcion ?? ''}}" 
                          @if ($i->descripcion == $data['filtros']['sucursal'] )
                              selected
                          @endif
                          >{{$i->descripcion ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>

              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Tipo Gasto</label>
                  <select class="form-control" id="select_tipo_gasto" name="tipo_gasto">
                      <option value="T" selected>Todos</option>
                      @foreach ($data['tipos_gasto'] as $i)
                        <option value="{{$i->id }}" title="{{$i->tipo ?? ''}}" 
                          @if ($i->id == $data['filtros']['tipo_gasto'] )
                              selected
                          @endif
                          >{{$i->tipo ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Estado</label>
                  <select class="form-control" name="aprobado">
                    <option value="T" <?php if ($data['filtros']['aprobado'] == 'T'){ echo 'selected';} ?>>Todos</option>
                    <option value="S" <?php if ($data['filtros']['aprobado'] == 'S'){ echo 'selected';} ?>>Aprobados</option>
                    <option value="N" <?php if ($data['filtros']['aprobado'] == 'N'){ echo 'selected';} ?>>Sin Aprobar</option>
                    <option value="R" <?php if ($data['filtros']['aprobado'] == 'R'){ echo 'selected';} ?>>Rechazados</option>
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
                <table class="table table-striped" id="tablaGastos" >
                  <thead>
                    <tr>
                    
                      <th class="text-center">Usuario</th>
                      <th class="text-center">
                        Sucursal 
                      </th>
                      <th class="text-center">
                        Tipo Gasto 
                      </th>
                      <th class="text-center">Fecha</th>
                      <th class="text-center">Monto</th>
                      <th class="text-center">Proveedor</th>
                      <th class="text-center">Estado</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    
                    @foreach($data['gastos'] as $g)
                  <tr class="space_row_table" style="cursor: pointer;" onclick='clickGasto("{{$g->id}}")'>
                      
                      <td class="text-center">{{$g->nombreUsuario ?? ""}}</td>
                      <td class="text-center">
                        {{$g->sucursal}}  
                      </td>
                      <td class="text-center">
                        {{$g->nombre_tipo_gasto ?? ''}}
                      </td>
                      <td class="text-center">
                          {{$g->fecha ?? ''}}
                      </td>
                      
                      <td class="text-center">
                        CRC {{number_format($g->monto ?? '0.00',2,".",",")}}
                      </td>
                      <td class="text-center">{{$g->nombre ?? ""}}</td>
                      <td class="text-center">
                    
                      @if($g->aprobado == "N")
                        <div class="badge badge-warning badge-shadow">
                          Sin Aprobar</div></td>
                      @endif 
                      @if($g->aprobado == "S")
                        <div class="badge badge-success badge-shadow">
                          Aprobado</div></td>
                      @endif 
                      @if($g->aprobado == "R")
                        <div class="badge badge-danger badge-shadow">
                          Rechazado</div></td>
                      @endif 
                      
                     
                     
                    </tr>

                  @endforeach
                  
                 
                </tbody>
                <tfoot>
                    @if(count($data['gastos']) > 0)


                    <tr class="space_row_table" >
                      
                      <td class="text-center" style="background: rgb(226, 196, 196);"><strong>Total General</strong></td>
                      <td class="text-center" style="background: rgb(226, 196, 196);">
                        ***
                      </td>
                      <td class="text-center" style="background: rgb(226, 196, 196);">
                        ***
                      </td>
                      <td class="text-center" style="background: rgb(226, 196, 196);">
                        <strong> ***</strong>
                      </td>
                      
                      <td class="text-center" style="background: rgb(226, 196, 196);">
                      
                      <strong>CRC {{number_format($data['totalGastos'] ?? '0.00',2,".",",")}}</strong>
                      </td>
                      <td class="text-center" style="background: rgb(226, 196, 196);">***</td>
                      <td class="text-center" style="background: rgb(226, 196, 196);">
                      ***
                      </td>
                    
                    </tr>
                  @endif
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
      var tipo_gasto= $("#select_tipo_gasto option[value='" +"{{$data['filtros']['tipo_gasto']}}"+ "']").html();
      var proveedor= $("#select_proveedor option[value='" +"{{$data['filtros']['proveedor']}}"+ "']").html();
      var sucursal= $("#select_sucursal option[value='" +"{{$data['filtros']['sucursal']}}"+ "']").html();

      var topMesage = 'Reporte de Gastos';
      var bottomMesage = 'Reporte general de gastos filtrado por';
      if("{{$data['filtros']['aprobado']}}" == 'S'){
        topMesage += ' APROBADOS';
      }else if("{{$data['filtros']['aprobado']}}" == 'R'){
        topMesage += ' RECHAZADOS';
      }
      else if("{{$data['filtros']['aprobado']}}" == 'N'){
        topMesage += ' SIN APROBAR';
      }
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
        bottomMesage += ' sucursal [ Todas ],';
      }

      if("{{$data['filtros']['tipo_gasto']}}" != 'T'){
        bottomMesage += ' tipo de gasto [ '+tipo_gasto+' ],';
      }else{
        bottomMesage += 'tipo de gasto [ Todas ],';
      }

      if("{{$data['filtros']['proveedor']}}" != '0'){
        bottomMesage += ' proveedor [ '+proveedor+' ].';
      }else{
        bottomMesage += 'proveedor [ Todos ]. ';
      }
      bottomMesage += ' Desarrollado por Space Software CR. ';
     
     
      $('#tablaGastos').DataTable({
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
            footer: true,
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_gastos_coffee_to_go'
          }, {
            extend: 'pdf',
            footer: true,
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_gastos_coffee_to_go'
          }, {
            extend: 'print',
            footer: true,
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_gastos_coffee_to_go'
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