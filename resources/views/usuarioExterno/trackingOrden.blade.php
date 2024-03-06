@extends('layout.master-tracking')

@section('content')
    <style>
        .icono-efecto {

            background-color: green;
            /* Fondo verde */
            color: white;
            /* Texto blanco */

            border-radius: 50%;
            /* Hace que el icono sea redondeado */
            animation: cambioColor 1s infinite;
        }



        @keyframes cambioColor {
            0% {
                transform: scale(1.1);
            }

            50% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.1);
            }
        }
    </style>
    <section class="section" style="padding: 15px;">
        <div class="section-body">
            <div class="invoice" style="margin-top: 10px;">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title ">

                                <div class="sidebar-brand">
                                    <img title="Nombre empresa" alt="Nombre empresa"
                                        src="{{ asset('assets/images/default-image_small.png') }}"
                                        style="background-color: transparent;border-color: transparent;
                                             height: 150px;"
                                        class="img-thumbnail" />
                                    <span class="logo-name" style="color: transparent;">COFFEE TO GO</span>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Orden: {{ $data['orden']->numero_orden ?? '' }}</strong><br>
                                        @if ($data['orden']->entrega != null)
                                            <strong>{{ $data['orden']->entrega->estadoOrden ?? '' }}</strong><br>
                                        @else
                                            <strong>{{ $data['orden']->estadoOrden ?? '' }}</strong><br>
                                        @endif
                                        <hr>
                                        Cliente : <strong>
                                            {{ $data['orden']->nombre_cliente ?? '' }}</strong><br>
                                        @if ($data['orden']->entrega != null)
                                            Contacto :<strong> {{ $data['orden']->entrega->contacto ?? '' }}</strong><br>
                                            Lugar de entrega : <strong>
                                                {{ $data['orden']->entrega->descripcion_lugar ?? '' }}</strong><br>
                                        @endif
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Fecha de la orden:</strong><br>
                                        {{ $data['orden']->fechaFormat ?? '' }}
                                        <br><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($data['orden']->entrega != null)
                        <div class="row mt-4">
                            <div class="section-body">
                                <h2 class="section-title">Detalles de entrega</h2>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="activities">
                                            @foreach ($data['orden']->entrega->estados as $e)
                                                <div class="activity">
                                                    <div
                                                        class="activity-icon bg-primary text-white {{ $loop->last ? 'icono-efecto' : '' }}">
                                                        <i
                                                            class="fas fa-{{ $e->cod_general == 'ENTREGA_PREPARACION_PEND'
                                                                ? 'utensils'
                                                                : ($e->cod_general == 'ENTREGA_PEND_SALIDA_LOCAL'
                                                                    ? 'box'
                                                                    : ($e->cod_general == 'ENTREGA_EN_RUTA'
                                                                        ? 'shipping-fast'
                                                                        : ($e->cod_general == 'ENTREGA_TERMINADA'
                                                                            ? 'check-circle'
                                                                            : 'cv'))) }} "></i>

                                                    </div>
                                                    <div class="activity-detail">
                                                        <div class="mb-2">
                                                            <span class="text-job text-primary">{{ $e->hora }}</span>
                                                            <span class="bullet" style="color: {{ $loop->last ? 'green' : 'grey' }}"></span>
                                                        </div>
                                                        <p>{{ $e->estadoOrden }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Detalle de orden</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-sm">
                                    <tr>
                                        <th class="text-left">Producto</th>
                                        <th class="text-left">Precio</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                    @foreach ($data['orden']->detalles as $d)
                                        <tr>
                                            <td style="text-align: left">{{ $d->nombre_producto }}</td>
                                            <td style="text-align: left">{{ $d->precio_unidad }}</td>
                                            <td style="text-align: center"> CRC
                                                {{ number_format($d->cantidad ?? '0.00', 2, '.', ',') }} </td>
                                            <td style="text-align: center"> CRC
                                                {{ number_format($d->total ?? '0.00', 2, '.', ',') }} </td>
                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                            <div class="row mt-2">

                                <div class="col-lg-12 text-right">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Subtotal</div>
                                        <div class="invoice-detail-value">CRC
                                            {{ number_format($data['orden']->subtotal ?? '0.00', 2, '.', ',') }}</div>
                                    </div>
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Descuento</div>
                                        <div class="invoice-detail-value">CRC
                                            {{ number_format($data['orden']->descuento ?? '0.00', 2, '.', ',') }}</div>
                                    </div>
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Envío</div>
                                        <div class="invoice-detail-value">CRC
                                            {{ number_format($data['orden']->monto_envio ?? '0.00', 2, '.', ',') }}</div>
                                    </div>
                                    <hr class="mt-2 mb-2">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Total</div>
                                        <div class="invoice-detail-value invoice-detail-value-lg"> CRC
                                            {{ number_format($data['orden']->total_con_descuento ?? '0.00', 2, '.', ',') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Función para recargar la página
            function recargarPagina() {
                location.reload();
            }

            // Configurar intervalo para llamar a la función cada 2 minutos (120,000 milisegundos)
            setInterval(recargarPagina, 40000);
        });
    </script>
@endsection
