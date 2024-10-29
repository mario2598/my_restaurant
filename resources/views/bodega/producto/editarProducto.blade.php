@extends('layout.master')

@section('style')
  
@endsection


@section('content')  

@include('layout.sidebar')

<div class="main-content">
    <section class="section">
      <form method="POST" action="{{URL::to('bodega/producto/guardar')}}"   autocomplete="off">
        {{csrf_field()}}
        <input type="hidden"  name="id" value="{{$data['producto']->id }}" >
      <div class="card">
        <div class="card-header">
          <h4>Editar Producto</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Código -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Código</label>
              <input type="text" class="form-control" id="codigo" name="codigo" value="{{$data['producto']->codigo_barra ??""}}" required maxlength="15">
              </div>
            </div>
            <!-- descripción -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Nombre </label>
                <input type="text" class="form-control" id="nombre" name="nombre"  value="{{$data['producto']->nombre ??""}}" required maxlength="25">
              </div>
            </div>
            
            <!-- categoria -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Categoría</label>
                <select class="form-control" id="categoria" name="categoria">
                 @foreach ($data['categorias'] as $i)
                  <option value="{{$i->id}}" 
                    @if ($i->id == ($data['producto']->categoria ??-1))
                        selected
                    @endif
                    >{{$i->categoria}}</option>
                 @endforeach
                </select>
              </div>
            </div>
            <!-- precio -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Precio CRC</label>
                <input type="number" class="form-control" id="precio" name="precio" step=any value="{{$data['producto']->precio ??""}}" required min="0">
              </div>
            </div>
            <!-- cedula -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Precio Mayoreo CRC</label>
                <input type="number" class="form-control" id="precio_mayoreo" step=any name="precio_mayoreo" value="{{$data['producto']->precio_mayoreo ??""}}" required min="0">
              </div>
            </div>

            <!-- impuesto -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Impuesto</label>
                <select class="form-control" id="impuesto" name="impuesto">
                  @foreach ($data['impuestos'] as $i)
                  <option value="{{$i->id}}"
                    @if ($i->id == ($data['producto']->impuesto ?? -1))
                        selected
                    @endif
                    >{{$i->descripcion}}</option>
                 @endforeach
                </select>
              </div>
            </div>

          
            <!-- enviar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Guardar producto</label>
                <input type="submit" class="btn btn-primary form-control" value="Guardar">
              </div>
            </div>
            <!-- eliminar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Eliminar producto</label>
              <a  class="btn btn-danger form-control" onclick='eliminarProducto("{{$data["producto"]->id}}")' style="color: white;cursor: pointer;" >Eliminar </a>
              </div>
            </div>

          </div>
          
         
        </div>
      </div>
    </form>
        
    </section>
    
  </div>

  <form id="formEliminarProducto" action="{{URL::to('bodega/producto/eliminar')}}" style="display: none"  method="POST">
    {{csrf_field()}}
    <input type="hidden" name="idProductoEliminar" id="idProductoEliminar" value="-1">
  </form>
@endsection



@section('script')
 
  <script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <script src="{{asset("assets/js/bodega/productos.js")}}"></script>

     
@endsection