@extends('layout.master')

@section('style')
  
@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <form method="POST" action="{{URL::to('bodega/lote/guardar')}}" autocomplete="off">
        {{csrf_field()}}
        <input type="hidden"  name="id" value="-1" >

      <div class="card">
        <div class="card-header">
          <h4>Ingresar producto a bodega</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- codigo lote -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Código de lote (Autogenerado)</label>
                 <input type="text" readonly class="form-control" id="codigo_lote" name="codigo_lote" placeholder="###" value="{{$data['datos']['codigo_lote'] ??""}}"  >
              </div>
            </div>
             <!-- Producto -->
             <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Producto</label>
                <select class="form-control" id="producto" name="producto">
                 @foreach ($data['productos'] as $i)
                  <option value="{{$i->id}}" 
                   
                    >{{$i->nombre}}</option>
                 @endforeach
                </select>
              </div>
            </div>
            <!-- cantidad -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Cantidad</label>
                 <input type="number" class="form-control" id="cantidad" name="cantidad" value="{{$data['datos']['cantidad'] ??""}}" required  min="1">
              </div>
            </div>
            <!-- Fecha Vencimiento  -->
           
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Fecha Vencimiento </label>
              <input type="date" id="vencimiento" name="vencimiento" min='{{date('Y-m-d')}}' class="form-control" required>
              </div>
            </div>
             <!-- bodega -->
             <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Bodega</label>
                <select class="form-control" id="bodega" name="bodega">
                 @foreach ($data['bodegas'] as $i)
                  <option value="{{$i->id}}" 
                   
                    >{{$i->descripcion}}</option>
                 @endforeach
                </select>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
              <div class="form-group mb-0">
                  <label>Detalle</label>
                  <textarea class="form-control" name="detalle" maxlength="300">{{$data['datos']['detalle'] ??""}}</textarea>
                </div>
            </div>
            <!-- generar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Ingresar inventario</label>
                <input type="submit" class="btn btn-primary form-control" value="Ingresar">
              </div>
            </div>

          </div>
          
         
        </div>
      </div>
    </form>
        
    </section>
    
  </div>

  
@endsection



@section('script')
 
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/bodega/productos.js")}}"></script>

     
@endsection