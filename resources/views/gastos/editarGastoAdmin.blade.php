@extends('layout.master')

@section('content')  

@include('layout.sidebar')

 <!-- Main Content -->
 <div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 col-md-6 col-lg-6">
            <div class="card">
              <form  action="{{URL::to('gastos/guardar')}}"  method="POST">
                {{csrf_field()}}
              <input type="hidden" name="id" value="{{$data['gasto']->id}}">
                <div class="card-header">
                  <h4>Editar gasto</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Proveedor</label>
                                <select class="form-control" name="proveedor">
                                    @foreach ($data['proveedores'] as $i)
                                    <option value="{{$i->id ?? -1}}" title="{{$i->descripcion ?? ''}}"
                                        @if ($i->id == ($data['gasto']->proveedor ?? -1))
                                            selected
                                        @endif
                                        >{{$i->nombre ?? ''}}</option>
                                   @endforeach
                                </select>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6"> 
                          <div class="form-group">
                              <label>Tipo de pago</label>
                              <select class="form-control" name="tipo_pago">
                                  @foreach ($data['tipos_pago'] as $i)
                                    <option value="{{$i->id ?? -1}}" title="{{$i->tipo ?? ''}}" >{{$i->tipo ?? ''}}</option>
                                  @endforeach
                              </select>
                            </div>
                      </div>
                      <div class="col-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label>Tipo de gasto</label>
                            <select class="form-control" name="tipo_gasto">
                              @foreach ($data['tipos_gasto'] as $i)
                                <option value="{{$i->id ?? -1}}" title="{{$i->tipo ?? ''}}" >{{$i->tipo ?? ''}}</option>
                              @endforeach
                                
                            </select>
                          </div>
                    </div>


                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-group">
                              <label>Tipo de documento</label>
                              <select class="form-control" name="tipo_documento">
                                  <option value="F" 
                                  @if ($data['gasto']->tipo_documento == "F")
                                      selected
                                  @endif
                                  >Factura</option>
                                  <option value="O" title="Debera definir en observación" 
                                  @if ($data['gasto']->tipo_documento == "O")
                                    selected
                                  @endif
                                  >Otro</option>
                              </select>
                            </div>
                      </div>
                      
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Número comprobante</label>
                                <input type="text" class="form-control" name="num_comprobante" value="{{$data['gasto']->num_factura ??""}}" maxlength="50" >
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group">
                                <label>Total CRC</label>
                                <input type="number" step="any" class="form-control" name="total" value="{{$data['gasto']->monto ??""}}" placeholder="0.00" min="10" required>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group mb-0">
                                <label>Descripción del gasto</label>
                                <textarea class="form-control" name="descripcion" required>{{$data['gasto']->descripcion ??""}}</textarea>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-group mb-0">
                                <label>Observación</label>
                                <textarea class="form-control" name="observacion">{{$data['gasto']->observacion ??""}}</textarea>
                              </div>
                        </div>
                        
                          
                    </div>   
                </div>      
                 
               
                <div class="card-footer text-right">
                  <input type="submit" class="btn btn-primary" value="Guardar"/>
                </div>
              </form>
            </div>
            </div>

    </div>
</div>
</section>
</div>

@endsection