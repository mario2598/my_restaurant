@extends('layout.master')

@section('style')
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="row" id="contenedor_comandas">
                @foreach ($data['ordenes_listas'] as $p)
                    <div class="col-md-6 col-xs-12 col-sm-12 col-xl-4 mb-3">
                        <div class="card">
                            <div class="card-header" style="padding: 5px !important;">
                                <h4>{{ $p->numero_orden }} : {{ $p->nombre_cliente ?? '' }}</h4>
                                <div class="card-header-action">
                                    <a class="btn btn-icon btn-success" style="cursor: pointer"
                                        onclick="terminarEntrega({{ $p->id }})" title="Terminar entrega orden">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a data-collapse="#mycard-collapse{{ $p->id }}" title="Esconder"
                                        class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                                </div>
                            </div>
                            <div class="collapse show" id="mycard-collapse{{ $p->id }}">
                                <div class="card-body" style="padding: 5px !important;">
                                    <h6>Estado: {{ $p->descEstado ?? '' }}</h6>
                                    <h6>Mesa: {{ $p->mesaDsc ?? 'PARA LLEVAR' }}</h6>
                                </div>
                                <div class="card-footer" style="padding: 5px !important;">
                                    @foreach ($p->comandas ?? [] as $comanda)
                                        <div class="table-responsive mb-3">
                                            <h5 class="text-primary">{{ $comanda->nombre_comanda ?? 'Comanda' }}</h5>
                                            <p class="small text-muted mb-1">Productos listos para entregar</p>
                                            <table class="table table-hover table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Cant.</th>
                                                        <th>Observación</th>
                                                        <th>Entregado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($comanda->detalles as $d)
                                                        <tr style="border-top:1px solid #dee2e6">
                                                            <td>
                                                                <i class="fas fa-box text-secondary"></i>
                                                                {{ $d->nombre_producto ?? '' }}
                                                            </td>
                                                            <td>{{ $d->cantidad_comanda ?? $d->cantidad ?? '0' }}</td>
                                                            <td>{{ $d->observacion ?? '' }}</td>
                                                            <td>
                                                                @if (!empty($d->fecha_hora_entrega))
                                                                    <span class="text-success">
                                                                        <i class="fas fa-check-circle"></i> Entregado
                                                                    </span>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        onclick="marcarLineaEntregada({{ $d->id_detalle_orden_comanda }})">
                                                                        Entregar
                                                                    </button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @if(!empty($d->tieneExtras) && !empty($d->extras))
                                                            <tr>
                                                                <td colspan="3" class="py-0">
                                                                    <ul class="list-unstyled small mb-0 pl-3">
                                                                        @foreach ($d->extras as $e)
                                                                            <li><i class="fas fa-plus text-muted"></i> {{ $e->descripcion_extra ?? '' }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if (empty($data['ordenes_listas']))
                    <div class="col-12">
                        <div class="alert alert-info">No hay órdenes con productos listos para entregar.</div>
                    </div>
                @endif
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
    <script src="{{ asset('assets/js/facturacion/ordenesEntrega.js') }}"></script>
@endsection
