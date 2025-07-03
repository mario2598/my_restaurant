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
                            <div class="card-header" style="padding: 5px !important;">
                                <h4>
                                    {{ $p->numero_orden }} : {{ $p->nombre_cliente }} 

                                </h4>
                                <div class="card-header-action">
                                    <a class="btn btn-icon btn-success" style="cursor: pointer"
                                        onclick='terminarEntrega({{ "$p->id" }})' title="Teminar preparación orden"><i
                                            class="fas fa-check"></i></a>
                                    <a data-collapse="#mycard-collapse{{ $p->id }}" title="Esconder"
                                        class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                                </div>
                            </div>
                            <div class="collapse show" id="mycard-collapse{{ $p->id }}">
                                <div class="card-body" style="padding: 5px !important;">
                                    <div class="row">
                                        <div class="col-12">
                                           
                                                <h6 style="cursor: pointer">
                                                    Estado : {{ $p->descEstado ?? '' }} </h6>
                                                    <h6 style="cursor: pointer"> Mesa : {{ $p->mesaDsc ?? 'PARA LLEVAR' }} </h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer" style="padding: 5px !important;">
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
                                                    <tr style="border-top:1px solid black ">
                                                        <td><i class="fas fa-box text-secondary" aria-hidden="true"></i>
                                                            - {{ $d->nombre_producto ?? '' }}</td>
                                                        <td>{{ $d->cantidad ?? '0' }} </td>
                                                        <td>{{ $d->observacion ?? '' }}</td>
                                                    </tr>
                                                    @if($d->tieneExtras)
                                                    <tr>
                                                        <td>
                                                        <table class="table table-hover mb-0" >
                                                            <thead>
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Extras </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($d->extras as $e)
                                                                    <tr>
                                                                        <td></td>
                                                                        <td><i class="fas fa-box text-secondary" aria-hidden="true"></i>
                                                                            - {{ $e->descripcion_extra ?? '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table></td>
                                                    </tr>

                                                    @endif
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
    <script src="{{ asset('assets/js/facturacion/ordenesEntrega.js') }}"></script>
@endsection
