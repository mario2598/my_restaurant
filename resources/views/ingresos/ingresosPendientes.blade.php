@extends('layout.master')

@section('style')


@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="card card-warning">
          <div class="card-header">
            <h4>Ingresos Pendientes de aprobar</h4>
            
          </div>
          <div class="card-body">
            <div id="contenedor_ingresos_sin_aprobar" class="row">
              @foreach ($data['ingresosSinAprobar'] as $g)
                <div class="col-12 col-md-6 col-lg-6" onclick='clickIngreso("{{$g->id}}")' style="cursor: pointer">

                  <div class="card card-primary">
                    <div class="card-header">
                      <h4>{{($g->tipoIngreso ?? '')}}</h4>
                      <div class="card-header-action">
                        <small>{{($g->nombreUsuario ?? '')}}</small>
                      </div>
                    </div>
                    <div class="card-body">
                      <p><small>{{$g->fecha ?? ''}}</small> <br> 
                        <div class="card-footer pt-3 d-flex justify-content-center">
                          <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                              <div class="budget-price justify-content-center">
                                <div class="budget-price-label" style="margin-right: 5px;">Sub Total</div>
                                <div class="budget-price-square bg-primary" data-width="20" style="width: 20px;"></div>
                                <div class="budget-price-label">CRC {{number_format($g->subTotal ?? '0.00',2,".",",")}}</div>
                              </div>
                            </div>
                            <div class="col-12 col-sm-12 col-lg-12">
                              <div class="budget-price justify-content-center">
                                <div class="budget-price-label" style="margin-right: 5px;">Gastos</div>
                                <div class="budget-price-square bg-danger" data-width="20" style="width: 20px;"></div>
                                <div class="budget-price-label">CRC {{number_format($g->totalGastos ?? '0.00',2,".",",")}}</div>
                              </div>
                            </div>
                            <div class="col-12 col-sm-12 col-lg-12">
                              <div class="budget-price justify-content-center">
                                <div class="budget-price-label" style="margin-right: 5px;">Total</div>
                                <div class="budget-price-square bg-success" data-width="20" style="width: 20px;"></div>
                                <div class="budget-price-label">CRC {{number_format($g->total ?? '0.00',2,".",",")}}</div>
                              </div>
                            </div>
                          </div>
                         
                        </div>
                        <small><strong>Descripción : </strong> {{$g->descripcion ?? ''}} </small><br>
                       
                      </p> 
                      
                    </div>
                    
                  </div>
                </div>
              @endforeach
            </div>
           
          </div>
        </div>
      
      </div>
    </section>
    
  </div>


@endsection



@section('script')

  <script src="{{asset("assets/js/ingresos_pendientes.js")}}"></script>
  

     
@endsection