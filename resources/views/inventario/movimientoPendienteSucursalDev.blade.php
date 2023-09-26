@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <script>
        var inventarioLotes = [];
        var desechos = [];

    </script>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                                <input type="hidden" name="id" value="{{ $data['movimiento']->id }}">
                                <div class="card-header">
                                    <h4>Movimiento - {{ $data['movimiento']->tipo_movimiento_codigo }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group">
                                                <label>Tipo Movimiento</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['movimiento']->tipo_movimiento_descripcion ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group">
                                                <label>Fecha Solicitud</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['movimiento']->fecha ?? '' }}">
                                            </div>
                                        </div>

                                        @if ($data['movimiento']->fecha_entrega != null)
                                            <div class="col-12 col-md-6 col-lg-3">
                                                <div class="form-group">
                                                    <label>Fecha Entrega</label>
                                                    <input type="text" class="form-control" readonly
                                                        value="{{ $data['movimiento']->fecha_entrega ?? '' }}">
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group">
                                                <label>Sucursal Despacho</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['movimiento']->despacho_descripcion ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group">
                                                <label>Sucursal Destino</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['movimiento']->destino_descripcion ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group">
                                                <label>Usuario encargado</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['movimiento']->entrega_usuario ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group">
                                                <label>Usuario receptor</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['movimiento']->recibe_usuario ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group mb-0">
                                                <label>Detalle</label>
                                                <textarea class="form-control" name="observacion" id="detalle"
                                                    maxlength="150">{{ $data['movimiento']->detalle ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="form-group mb-0">
                                                <label>Confirmar Devolución</label><br>
                                            <a  onclick='aplicarDevoulucionSucursal("{{$data["movimiento"]->id}}")'
                                                style="cursor: pointer; color:white;" class="btn btn-warning">Confirmar</a>
                                            </div>
                                        </div>
                                        

                                    </div>
                                </div>
                                

                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-lg-6">
                            
                            <h5 style="font-size: 14px;">Detalle de movimiento</h5>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaDetalle">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center"># Lote</th>
                                            <th scope="col" class="text-center">Producto</th>
                                            <th scope="col" class="text-center">Cantidad</th>
                                            <th scope="col" class="text-center">Agregar</th>
    
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_inventario">
                                        @foreach ($data['movimiento']->detalles as $i)
                                            <tr>
                                                <script>
                                                    inventarioLotes.push({
                                                        "id": "{{ $i->id }}",
                                                        "codigo":"{{ $i->lote_codigo }}",
                                                        "nombre": "{{ $i->producto_nombre }}",
                                                        "cantidad": "{{ $i->cantidad }}",
                                                    });

                                                </script>
                                                <td class="text-center">
                                                    {{ $i->lote_codigo }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->producto_nombre }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->cantidad }}
                                                </td>
                                                <td class="text-center">
                                                    <button  class="btn btn-icon btn-success" onclick='agregarProducto("{{$i->id}}")'
                                                            style="color: blanchedalmond"><i class="fas fa-plus"></i></button>
                                                    </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                
                    </div>

                    <div class="col-12 col-sm-12 col-lg-6">
                        
                        <h5 style="font-size: 14px;">Desechos</h5>

                        <div class="table-responsive">
                            <table class="table table-striped" id="tablaDetalle">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center"># Lote</th>
                                        <th scope="col" class="text-center">Producto</th>
                                        <th scope="col" class="text-center">Cantidad</th>
                                        <th scope="col" class="text-center">Devolver</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_desechos">
                                    @foreach ($data['movimiento']->detalles as $i)
                                        <tr>
                                            <script>
                                                desechos.push({
                                                    "id": "{{ $i->id }}",
                                                    "codigo":"{{ $i->lote_codigo }}",
                                                    "nombre": "{{ $i->producto_nombre }}",
                                                    "cantidad": 0,
                                                });

                                            </script>
                                            <td class="text-center">
                                                {{ $i->lote_codigo }}
                                            </td>
                                            <td class="text-center">
                                                {{ $i->producto_nombre }}
                                            </td>
                                            <td class="text-center">
                                                0
                                            </td>
                                            <td class="text-center">
                                                <button  class="btn btn-icon btn-success" onclick='eliminarProducto("{{$i->id}}")'
                                                    style="color: blanchedalmond"><i class="fas fa-minus"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
          
                </div>
            </div>
        </section>
    </div>

@endsection
@section('script')


<script src="{{asset("assets/js/page/datatables.js")}}"></script>
<script src="{{ asset('assets/js/inventario/aceptarDevolucionSucursal.js') }}"></script>

@endsection
