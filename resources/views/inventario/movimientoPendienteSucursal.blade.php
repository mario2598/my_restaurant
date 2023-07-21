@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-4">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card l-bg-orange">
                                <div class="card-statistic-3">
                                    <div class="card-icon card-icon-large"><i class="fa fa-money-bill-alt"></i></div>
                                    <div class="card-content">
                                        <h4 class="card-title">Movimiento -
                                            @if ($data['movimiento']->estado == 'P')
                                                Pendiente
                                            @endif
                                            @if ($data['movimiento']->estado == 'T')
                                                Realizado
                                            @endif
                                            @if ($data['movimiento']->estado == 'C')
                                                Cancelado
                                            @endif
                                            @if ($data['movimiento']->estado == 'E')
                                                Error
                                            @endif

                                        </h4>


                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-lg-12">


                            <h5 style="font-size: 14px;">Detalle de movimiento</h5>

                            

                            <div class="table-responsive">
                            <table class="table table-striped" id="tablaDetalle">
                                <thead>
                                    <tr>
                                        <th scope="col"># Lote</th>
                                        <th scope="col">Producto</th>
                                        <th scope="col">Cantidad</th>


                                    </tr>
                                </thead>
                                <tbody id="tbody_inventario">
                                    @foreach ($data['movimiento']->detalles as $i)
                                        <tr>
                                            <td class="text-center">
                                                {{ $i->lote_codigo }}
                                            </td>
                                            <td class="text-center">
                                                {{ $i->producto_nombre }}
                                            </td>
                                            <td class="text-center">
                                                {{ $i->cantidad }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        </div>

                    </div>
                    <div class="col-12 col-md-12 col-lg-8">
                        <div class="card">
                            <input type="hidden" name="id" value="{{ $data['movimiento']->id }}">
                            <div class="card-header">
                                <h4>Movimiento - {{ $data['movimiento']->tipo_movimiento_codigo }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-12 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <label>Tipo Movimiento</label>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $data['movimiento']->tipo_movimiento_descripcion ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <label>Fecha Solicitud</label>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $data['movimiento']->fecha ?? '' }}">
                                        </div>
                                    </div>

                                    @if ($data['movimiento']->fecha_entrega != null)
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Fecha Entrega</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['movimiento']->fecha_entrega ?? '' }}">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-12 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <label>Sucursal Despacho</label>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $data['movimiento']->despacho_descripcion ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <label>Sucursal Destino</label>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $data['movimiento']->destino_descripcion ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <label>Usuario encargado</label>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $data['movimiento']->entrega_usuario ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-6">
                                        <div class="form-group">
                                            <label>Usuario receptor</label>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $data['movimiento']->recibe_usuario ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-6">
                                        <div class="form-group mb-0">
                                            <label>Detalle</label>
                                            <textarea class="form-control" name="observacion"
                                                id="detalle_movimiento_generado"
                                                maxlength="150">{{ $data['movimiento']->detalle ?? '' }}</textarea>
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="card-footer text-right">
                                
                                @if ($data['movimiento']->estado == 'P')
                                    <a onclick='aceptarMovimientoSucursal("{{ $data["movimiento"]->id }}")'
                                        style="cursor: pointer; color:white;" class="btn btn-warning">Aceptar</a>
                                @endif

                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
    <form id="formAceptarMovSuc" action="{{URL::to('aceptarMovimientoSucursal')}}" style="display: none"  method="POST">
        {{csrf_field()}}
        <input type="hidden" name="idMovSuc" id="idMovSuc" value="-1">
        <input type="hidden" name="detMovSuc" id="detMovSuc" value="-1">
      </form>
    

@endsection
@section('script')
<script>
    window.addEventListener("load", initialice, false);

    function initialice() {
        var sucursalDestino = "{{ $data['movimiento']->destino_descripcion }}".toUpperCase();;
        var sucursalEntrega = "{{ $data['movimiento']->despacho_descripcion }}".toUpperCase();;
        var solicitante = "{{ $data['movimiento']->entrega_usuario }}".toUpperCase();;
        var fechaSolicitud = "{{$data['movimiento']->fecha ?? ''}}".toUpperCase();;

        var topMesage = 'Detalle de desglosado de pedido. Despacho : ' + sucursalEntrega;
        topMesage += ' , Destino : ' + sucursalDestino;
        topMesage += ' , Encargado de pedido : ' + solicitante;
        topMesage += ' . Solicitado el ' + fechaSolicitud;

        var bottomMesage = ' Solicitud realizada por ' + "{{ session('usuario')['usuario'] }}" + '.';

        bottomMesage += ' Desarrollado por Space Software CR. ';


        $('#tablaDetalle').DataTable({
            dom: 'Bfrtip',
            "searching": false,
            "paging": false,
            'fixedHeader': {
                'header': true,
                'footer': true
            },
            buttons: [{
                extend: 'excel',
                title: 'SPACE REST',
                messageTop: topMesage,
                messageBottom: bottomMesage,
                filename: 'detalle_pedido_' + fechaSolicitud + '_el_amanecer'
            }, {
                extend: 'pdf',
                title: 'SPACE REST',
                messageTop: topMesage,
                messageBottom: bottomMesage,
                filename: 'detalle_pedido_' + fechaSolicitud + '_el_amanecer'
            }, {
                extend: 'print',
                title: 'SPACE REST',
                messageTop: topMesage,
                messageBottom: bottomMesage,
                filename: 'detalle_pedido_' + fechaSolicitud + '_el_amanecer'
            }]
        });

    }

</script>
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
  <script src="{{asset("assets/js/bodega/productos.js")}}"></script>
<script src="{{asset("assets/js/inventario/movPendSucursal.js")}}"></script>

@endsection
