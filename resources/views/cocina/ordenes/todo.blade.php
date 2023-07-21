@extends('layout.master-facturacion')

@section('style')

@endsection


@section('content')

    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-6 col-xl-12">
                <div class="form-group">
                <label>Estado factura</label>
                <select class="form-control" id="estado_factura" onchange="recargarOrdenes()" name="estado_factura">
                    <option value="T" selected>Todas</option>
                    <option value="F" >Facturadas</option>
                    <option value="PF"  >Pendiente Facturar</option>
                </select>
                </div>
            </div>            
            <div class="row" id="contenedor_comandas">
                @foreach ($data['ordenes'] as $p)
                    @if (count($p->detalles) > 0)
                        <div class="col-md-6 col-xs-12 col-sm-6 col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4>
                                        Orden No.{{ $p->numero_orden }}
                                    </h4>
                                    <div class="card-header-action">
                                        @if ($p->estado == "LF" || $p->estado == "CR" || $p->estado == "EP" || $p->estado == "PT")
                                        <a class="btn btn-icon btn-success" style="cursor: pointer; color:white;"
                                        onclick='redirigirCobro("{{ $p->id }}")' title="Ir a cobrar"><i
                                            class="fas fa-credit-card"></i></a>
                                        <a class="btn btn-icon btn-success" style="cursor: pointer; color:white;"
                                            onclick='goFacturaOrden("{{ $p->id }}")' title="Ir a editar"><i
                                            class="fas fa-cog"></i></a>
                                        @endif
                                       
                                        <a title="Imprimir tiquete" style="color: white" 
                                        @if ($p->estado == "FC" || $p->estado == "EPF" || $p->estado == "PTF")
                                            onclick='tickete("{{ $p->id }}")' 
                                        @else
                                            onclick='preTickete("{{ $p->id }}")' 
                                        @endif
                                        
                                            class="btn btn-icon btn-info" href="#"><i class="fas fa-print"></i></a>
                                    </div>
                                </div>
                                <div class="collapse show" id="mycard-collapse{{ $p->id }}">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <h6>Fecha : {{ $p->fecha_inicio_texto }}</h6><br>

                                            </div>
                                            <div class="col-12">
                                                <strong> <small style="cursor: pointer">
                                                        @switch($p->tipo)
                                                            @case('LL')
                                                                Para Llevar
                                                            @break
                                                            @case('CA')
                                                                Comer aquí
                                                            @break
                                                            @case('M')
                                                                Mixto
                                                            @break
                                                            @default

                                                        @endswitch
                                                    </small></strong><br>
                                            </div>
                                            @if ($p->tipo == 'CA' || $p->tipo == 'M')
                                                <div class="col-12">
                                                    <h6 style="cursor: pointer" title="{{ $p->descripcion_mobiliario }}">
                                                        Mesa No.{{ $p->numero_mesa }}</h6>
                                                </div>
                                            @endif
                                            <div class="col-12">
                                                <h6 style="cursor: pointer" title="{{ $p->descripcion_mobiliario }}">
                                                    Estado : <strong>
                                                        @switch($p->estado)
                                                            @case("EPF")
                                                                En preparación [FACTURADO]
                                                                @break
                                                            @case("PTF")
                                                                En espera de entregar [FACTURADO]
                                                                @break
                                                            @case("FC")
                                                                [FACTURADO]
                                                                @break
                                                            @case("LF")
                                                                Listo para facturar [NO FACTURADO]
                                                                @break
                                                            @case("EP")
                                                                En preparación [NO FACTURADO]
                                                                @break
                                                            @case("PT")
                                                                En espera de entregar [NO FACTURADO]
                                                                @break
                                                            @case("CR")
                                                                Caja rapida [NO FACTURADO]
                                                                @break
                                                            @default

                                                        @endswitch
                                                    </strong>
                                                </h6>
                                            </div>
                                            <div class="col-12">
                                                <h6 style="cursor: pointer" > 
                                                    Total CRC
                                                    <strong>
                                                        {{ number_format($p->total, 2, ".", ",") }}
                                                    </strong></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="table-responsive">
                                            <h4>Detalle de orden</h4>
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Cantidad</th>
                                                        <th>Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($p->detalles as $d)
                                                        <tr>
                                                            <td><i class="{{ $d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary' }}"
                                                                    aria-hidden="true"
                                                                    style="{{ $d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;' }}"></i>
                                                                - {{ $d->nombre_producto ?? '' }}</td>
                                                            <td>{{ $d->cantidad ?? '0' }} </td>
                                                            <td>{{ $d->observacion ?? '' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    </div>


    <!-- modal modal de agregar producto -->
    <div class="modal fade bs-example-modal-center" id='mdl_agregar_producto' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar producto-->

    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    
    <form id="frm-factrar-orden" action="{{ URL::to('facturacion/pagar') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="ipt_id_orden" id="ipt_id_orden">
    </form>

@endsection



@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/cocina/ordenes/todo.js') }}"></script>
@endsection
