@extends('layout.master')

@section('style')

@endsection


@section('content')

    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="row" id="contenedor_comandas">
                @foreach ($data['ordenes_listas'] as $p)
                    @if (count($p->detalles) > 0)
                        <div class="col-md-6 col-xs-12 col-sm-12 col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4>
                                        @if ($p->tipo == 'CA' || $p->tipo == 'M')
                                            Mesa No.{{ $p->numero_mesa }}
                                        @else
                                            Orden No.{{ $p->numero_orden }}
                                        @endif
                                    </h4>
                                    <div class="card-header-action">
                                        <a class="btn btn-icon btn-success" style="cursor: pointer"
                                            onclick='entregarOrdenComida({{ "$p->id" }})' title="Entregar orden"><i
                                                class="fas fa-check"></i></a>
                                        <a data-collapse="#mycard-collapse{{ $p->id }}" title="Esconder"
                                            class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                                    </div>
                                </div>
                                <div class="collapse show" id="mycard-collapse{{ $p->id }}">
                                    <div class="card-body">
                                        <div class="row">
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

                                                    @endswitch</small></strong><br>
                                            </div>
                                            @if ($p->tipo == 'CA' || $p->tipo == 'M')
                                                <div class="col-12">
                                                    <h6 style="cursor: pointer" title="{{ $p->descripcion_mobiliario }}">
                                                        Orden No.{{ $p->numero_orden }}</h6><br>
                                                </div>
                                            @endif
                                            @if ($p->nombre_cliente != null && $p->nombre_cliente != '')
                                                <div class="col-12">
                                                    <h6 style="cursor: pointer" title="{{ $p->descripcion_mobiliario }}">
                                                        Cliente : {{ $p->nombre_cliente }} </h6><br>
                                                </div>
                                            @endif
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
                                                            <td><i class="{{ $d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary' }}" aria-hidden="true" 
                                                                style="{{ $d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;' }}"></i> - {{ $d->nombre_producto ?? '' }}</td>
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
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/cocina/ordenesListas/comanda.js') }}"></script>
@endsection
