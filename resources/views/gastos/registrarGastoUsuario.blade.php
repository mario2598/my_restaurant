@extends('layout.master')

@section('content')  

@include('layout.sidebar')

 <!-- Main Content -->
 <div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
              <form  action="{{URL::to('gastos/guardar')}}"  method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                <input type="hidden" name="id" value="-1">
                <div class="card-header">
                  <h4>Ingresar gasto</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Proveedor</label>
                                <select class="form-control" name="proveedor">
                                    @foreach ($data['proveedores'] as $i)
                                    <option value="{{$i->id ?? -1}}" title="{{$i->descripcion ?? ''}}"
                                        @if ($i->id == ($data['datos']['proveedor'] ?? -1))
                                            selected
                                        @endif
                                        >{{$i->nombre ?? ''}}</option>
                                   @endforeach
                                </select>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4"> 
                            <div class="form-group">
                                <label>Tipo de pago</label>
                                <select class="form-control" name="tipo_pago">
                                  @foreach ($data['tipos_pago'] as $i)
                                    <option value="{{$i->id ?? -1}}" title="{{$i->tipo ?? ''}}" >{{$i->tipo ?? ''}}</option>
                                  @endforeach
                                </select>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Tipo de documento</label>
                                <select class="form-control" name="tipo_documento">
                                    <option value="F" >Factura</option>
                                    <option value="O" title="Debera definir en observación">Otro</option>
                                </select>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Número comprobante</label>
                                <input type="text" class="form-control" name="num_comprobante" value="{{$data['datos']['num_comprobante'] ??""}}" maxlength="50" >
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Total CRC</label>
                                <input type="number" step="any" class="form-control" name="total" value="{{$data['datos']['total'] ??""}}" placeholder="0.00" min="10" required>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label>Descripción del gasto</label>
                                <textarea class="form-control" name="descripcion" required>{{$data['datos']['descripcion'] ??""}}</textarea>
                              </div>
                        </div>
                       
                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group ">
                              <label>Foto comprobante</label>
                                <input type="file" class="form-control" id="foto_comprobante" name="foto_comprobante" accept="image/png, image/jpeg, image/jpg" onchange="fileValidation()" >
								                <input type="text" id="foto_comprobante_b64" style='display:none;' name="foto_comprobante_b64">

                            </div>
                        </div>
                          
                    </div>   
                </div>      
                 
               
                <div class="card-footer text-right">
                  <input type="submit" class="btn btn-primary" value="Registrar"/>
                </div>
              </form>
            </div>
            </div>

    </div>
</div>

</section>
</div>

@endsection
@section('script')

  <script src="{{asset("assets/js/gastos/gasto.js")}}"></script>
   
@endsection