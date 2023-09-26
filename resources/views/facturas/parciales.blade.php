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
            <h4>Facturas parciales</h4>
            <form class="card-header-form">
              <div class="input-group">
                <input type="text" name=""  id="btn_buscar" class="form-control" placeholder="Buscar orden">
                <div class="input-group-btn">
                  <a class="btn btn-primary btn-icon" style="cursor: pointer;"><i class="fas fa-search"></i></a>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body">
            <form action="{{URL::to('facturas/parciales/filtro')}}" method="POST">
            {{csrf_field()}}
            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Cliente</label>
                  <select class="form-control" id="select_cliente" name="cliente">
                      <option value="0" selected>Todos</option>
                      @foreach ($data['clientes'] as $i)
                      <option value="{{$i->id ?? -1}}" title="{{$i->nombre ?? ''}}" 
                        @if ($i->id == $data['filtros']['cliente'] )
                              selected
                          @endif
                        >{{$i->nombre ?? ''}}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Estado</label>
                  <select class="form-control" name="estado">
                    <option value="T" <?php if ($data['filtros']['estado'] == 'T'){ echo 'selected';} ?>>Todos</option>
                    <option value="PENDIENTE" <?php if ($data['filtros']['estado'] == 'PENDIENTE'){ echo 'selected';} ?>>Pendientes</option>
                    <option value="COMPLETO" <?php if ($data['filtros']['estado'] == 'COMPLETO'){ echo 'selected';} ?>>Completos</option>
                    
                  </select>
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
                <table class="table table-striped" id="tablaIngresos" >
                  <thead>
                  
                    <tr>
                    
                      <th class="text-center">No.Orden</th>
                      <th class="text-center">
                        Cliente  
                      </th>
                      <th class="text-center">
                        Estado
                      </th>
                      <th class="text-center">Creador</th>
                      <th class="text-center">Fecha</th>
                      <th class="text-center">Monto Total</th>
                      <th class="text-center">Monto Cancelado</th>
                      <th class="text-center">Pagos</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    @foreach($data['ordenes'] as $g)
                      <tr class="space_row_table" >
                        
                        
                        <td class="text-center">
                          <a style="color: red; cursor:pointer;" class="btn" 
                          onclick='ticketeParcial("{{$g->id}}")'
                          >  
                            ORD-{{$g->numero_orden ?? ""}} </a>
                        </td>
                        <td class="text-center">
                          {{$g->nombre_cliente ?? ''}}
                        </td>
                        <td class="text-center">
                          {{$g->estado ?? ''}}
                        </td>
                        <td class="text-center">
                          {{$g->creador ?? ''}}
                        </td>
                        <td class="text-center">{{$g->fecha_inicio ?? ""}}</td>
                        <td class="text-center">
                          CRC {{number_format($g->total ?? '0.00',2,".",",")}}
                        </td>
                        <td class="text-center">
                          CRC {{number_format($g->totalPagado ?? '0.00',2,".",",")}}
                        </td>
                        <td class="space-align-center" >
                          <a onclick='abrirPagos("{{$g->id}}")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-eye"></i></a> 
                          
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
    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    
  </div>

  <div class="modal fade bs-example-modal-center" id='mdl_pagos' tabindex="-1" role="dialog"
  aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div >
             
              <div class="modal-header">
                
                <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                  <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Pagos Parciales</h5>
                  <button type="button" class="close" aria-hidden="true"  onclick="cerrarModal()">x</button>
              </div>
              <div class="modal-body">
                <div class="row">
                    <div class="col-xl-6 col-sm-12">
                        <div class="form-group form-float">
                            <div class="form-line">
                            <label class="form-label">Monto Efectivo</label>
                            <input type="number" class="form-control space_input_modal" required min="1" id="monto_efectivo" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-sm-12">
                      <div class="form-group form-float">
                          <div class="form-line">
                          <label class="form-label">Monto Tarjeta</label>
                          <input type="number" class="form-control space_input_modal" required min="1"  id="monto_tarjeta" value="0">
                          </div>
                      </div>
                    </div>
                    <div class="col-xl-6 col-sm-12">
                      <div class="form-group form-float">
                          <div class="form-line">
                          <label class="form-label">Monto Sinpe</label>
                          <input type="number" class="form-control space_input_modal" required min="1"  id="monto_sinpe" value="0">
                          </div>
                      </div>
                    </div>
                    <div class="col-xl-12 col-sm-12">
                      <input onclick="crearPago()" style="width: 100%; cursor:pointer;" class="btn btn-primary" value="Crear Pago"/>
                    </div>
                  </div>
                  <h5 style="text-align: center; margin-top:10px;">TABLA DE PAGOS</h5>
                  <div class="table-responsive">
                    <table class="table table-striped" >
                      <thead>
                        <tr>
                          <th class="text-center">Fecha</th>
                          <th class="text-center">
                            Monto   
                          </th>
                          <th class="text-center">
                            Cobrador
                          </th>
                        </tr>
                      </thead>
                      <tbody id="tbody_pagos">
                        
                      </tbody>
                       
                    </table>
                  </div> 
                    
                </div>
  
              </div>
              <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                <input onclick="cerrarModal()" class="btn btn-secondary" style="cursor:pointer;" value="Cerrar"/>
              </div>
            </div>
          </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
  </div>
@endsection


@section('script')

<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/facturas/parciales.js")}}"></script>
  

     
@endsection