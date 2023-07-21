@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-8">
                        <div class="card">
                            <form action="{{ URL::to('ingresos/guardar') }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="id" value="{{ $data['ingreso']->id }}">
                                <div class="card-header">
                                    <h4>Ingreso - {{ $data['ingreso']->nombreUsuario }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['ingreso']->fecha }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Sucursal</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['ingreso']->nombreSucursal }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Monto Efectivo (CRC)
                                                    @if ($data['tieneReporteCajero'])
                                                        <strong>
                                                            - Reportado
                                                            {{ number_format($data['reporte_cajero']->monto_efectivo ?? '0.00', 2, '.', ',') }}
                                                        </strong>
                                                    @endif
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_efectivo"
                                                    name="monto_efectivo"
                                                    value="{{ $data['ingreso']->monto_efectivo ?? '' }}"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Monto Tarjeta (CRC)
                                                    @if ($data['tieneReporteCajero'])
                                                        <strong>
                                                            - Reportado
                                                            {{ number_format($data['reporte_cajero']->monto_tarjeta ?? '0.00', 2, '.', ',') }}
                                                        </strong>
                                                    @endif

                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_tarjeta"
                                                    name="monto_tarjeta"
                                                    value="{{ $data['ingreso']->monto_tarjeta ?? '' }}" placeholder="0.00"
                                                    min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Monto SINPE (CRC)
                                                    @if ($data['tieneReporteCajero'])
                                                        <strong>
                                                            - Reportado
                                                            {{ number_format($data['reporte_cajero']->monto_sinpe ?? '0.00', 2, '.', ',') }}
                                                        </strong>
                                                    @endif
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_sinpe"
                                                    name="monto_sinpe" value="{{ $data['ingreso']->monto_sinpe ?? '' }}"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-6">
                                            <div class="form-group">
                                                <label>Tipo ingreso</label>
                                                <select class="form-control space_disabled" name="tipo_ingreso">
                                                    @foreach ($data['tipos_ingreso'] as $i)
                                                        <option value="{{ $i->id }}" title="{{ $i->tipo ?? '' }}"
                                                            @if ($i->id == ($data['ingreso']->tipo_ingreso ?? -1)) selected @endif>{{ $i->tipo }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @if ($data['ingreso']->cliente != null)
                                            <div class="col-12 col-md-4 col-lg-6">
                                                <div class="form-group">
                                                    <label>Cliente</label>
                                                    <select class="form-control" name="cliente">
                                                        @foreach ($data['clientes'] as $i)
                                                            <option value="{{ $i->id }}"
                                                                title="{{ $i->nombre ?? '' }}" @if ($i->id == ($data['ingreso']->cliente ?? -1)) selected @endif>{{ $i->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group mb-0">
                                                <label>Descripción del ingreso</label>
                                                <textarea class="form-control" required maxlength="300" readonly
                                                    name="descripcion">{{ $data['ingreso']->descripcion ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group mb-0">
                                                <label>Observación</label>
                                                <textarea class="form-control" name="observacion"
                                                    maxlength="150">{{ $data['ingreso']->observacion ?? '' }}</textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                                <div class="card-footer text-right">

                                    @if ($data['ingreso']->aprobado == 'N')
                                        <a onclick='rechazarIngreso("{{ $data['ingreso']->id }}")'
                                            style="cursor: pointer; color:white;" class="btn btn-info">Rechazar</a>
                                        <a onclick='confirmarIngreso("{{ $data['ingreso']->id }}")'
                                            style="cursor: pointer; color:white;" class="btn btn-success">Confirmar</a>
                                    @endif
                                    @if ($data['ingreso']->aprobado != 'E')
                                        <a onclick='eliminarIngresoAdmin("{{ $data['ingreso']->id }}")'
                                            style="cursor: pointer; color:white;" class="btn btn-warning">Eliminar</a>
                                        <input type="submit" class="btn btn-primary" value="Guardar" />
                                    @endif


                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-4">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card l-bg-orange">
                                <div class="card-statistic-3">
                                    <div class="card-icon card-icon-large"><i class="fa fa-money-bill-alt"></i></div>
                                    <div class="card-content">
                                        <h4 class="card-title">Ingreso -
                                            @if ($data['ingreso']->aprobado == 'N')

                                                Sin Aprobar
                                            @endif
                                            @if ($data['ingreso']->aprobado == 'S')

                                                Aprobado
                                            @endif
                                            @if ($data['ingreso']->aprobado == 'R')

                                                Rechazado
                                            @endif
                                            @if ($data['ingreso']->aprobado == 'E')

                                                Eliminado
                                            @endif

                                        </h4>
                                        <span>CRC
                                            {{ number_format($data['ingreso']->subtotal ?? '0.00', 2, '.', ',') }}</span>
                                        <div class="progress mt-1 mb-1" data-height="8">
                                            @if ($data['ingreso']->subtotal >= $data['estadisticas']['promedio'])
                                                <div class="progress-bar l-bg-green" role="progressbar" data-width="75%"
                                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                            @endif
                                            @if ($data['ingreso']->subtotal < $data['estadisticas']['promedio'])
                                                <div class="progress-bar l-bg-red" role="progressbar" data-width="25%"
                                                    aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                            @endif


                                        </div>
                                    </div>
                                    <p class="mb-0 text-sm">

                                        @if ($data['ingreso']->subtotal >= $data['estadisticas']['promedio'])
                                            <span class="mr-2" style="color: green">
                                                <i style="color: green" class="fa fa-arrow-up"></i> Sobre el promedio
                                                mensual
                                        @endif
                                        @if ($data['ingreso']->subtotal < $data['estadisticas']['promedio'])
                                            <span class="mr-2" style="color: red">
                                                <i style="color: red" class="fa fa-arrow-down"></i> Bajo el promedio mensual
                                        @endif
                                        </span>
                                        <span class="text-nowrap">Promedio Mensual CRC
                                            <strong>{{ number_format($data['estadisticas']['promedio'] ?? '0.00', 2, '.', ',') }}</strong></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-12 col-lg-12">
                        <div class="card gradient-bottom">
                            <div class="card-header">
                                <h5 style="font-size: 14px;">Gastos Relacionados</h5>

                            </div>
                            <div class="card-body" id="top-5-scroll" tabindex="2"
                                style="min-height: 210px;max-height: 210px; overflow: hidden; outline: none;">
                                <ul class="list-unstyled list-unstyled-border">
                                    @foreach ($data['gastosCaja'] as $i)
                                        <li class="media" style="border-bottom: solid 1px #F39865;">
                                            <div class="media-body" style="cursor: pointer"
                                                onclick='clickGasto("{{ $i->id }}")'>

                                                <div class="media-title">{{ $i->nombreProveedor }}</div>
                                                <div class="mt-1">
                                                    <div class="budget-price">
                                                        <div class="budget-price-square bg-primary" data-width="10%"
                                                            style="width: 10%;"></div>
                                                        <div class="budget-price-label">{{ $i->descripcion }}</div>
                                                    </div>
                                                    <div class="budget-price">
                                                        <div class="budget-price-square bg-warning" data-width="10%"
                                                            style="width: 10%;"></div>
                                                        <div class="budget-price-label">CRC
                                                            {{ number_format($i->monto ?? '0.00', 2, '.', ',') }}</div>
                                                    </div>

                                                </div>
                                            </div>
                                            <a onclick='rechazarIngresoGasto("{{ $i->id }}","{{ $data['ingreso']->id }}")'
                                                class=" btn-danger space_button_minus" title="Rechazar"><i
                                                    class="fas fa-minus" style="color:white"></i></a>

                                        </li>
                                    @endforeach

                                </ul>
                            </div>
                            <div class="card-footer pt-3 d-flex justify-content-center">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-lg-12">
                                        <div class="budget-price justify-content-center">
                                            <div class="budget-price-label" style="margin-right: 5px;">Sub Total</div>
                                            <div class="budget-price-square bg-success" data-width="20"
                                                style="width: 20px;"></div>
                                            <div class="budget-price-label">CRC
                                                {{ number_format($data['ingreso']->subtotal ?? '0.00', 2, '.', ',') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-12 col-lg-12">
                                        <div class="budget-price justify-content-center">
                                            <div class="budget-price-label" style="margin-right: 5px;">Gastos</div>
                                            <div class="budget-price-square bg-danger" data-width="20" style="width: 20px;">
                                            </div>
                                            <div class="budget-price-label">CRC
                                                {{ number_format($data['ingreso']->totalGastos ?? '0.00', 2, '.', ',') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-12 col-lg-12">
                                        <div class="budget-price justify-content-center">
                                            <div class="budget-price-label" style="margin-right: 5px;">Total</div>
                                            <div class="budget-price-square bg-primary" data-width="20"
                                                style="width: 20px;"></div>
                                            <div class="budget-price-label">CRC
                                                {{ number_format($data['ingreso']->totalGeneral ?? '0.00', 2, '.', ',') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @if ($data['tieneVentas'])
                        <div class="col-12 col-sm-12 col-lg-12">
                            <div>
                                <h4>Ventas</h4>
                            </div>
                            <div id="accordion">
                                @foreach ($data['ventasParciales'] as $i)
                                <div class="card">
                                    <div class="card-header" id="headingOne">
                                        <h5 class="mb-0">
                                            <button class="btn btn-primary" style="width: 100%" onclick='ticketeParcial("{{ $i->ordenObj->id }}")'>IMPRIMIR
                                            </button>
                                            <button class="btn btn-link" data-toggle="collapse"
                                                data-target="#collapse{{ $i->ordenObj->id }}" aria-expanded="false"
                                                aria-controls="collapseOne">
                                                {{ $i->ordenObj->nombre_cliente ?? '*' }} | PAGO PARCIAL | ORD-{{ $i->ordenObj->numero_orden }} - CRC
                                                {{ number_format($i->cancelado  ?? '0.00', 2, '.', ',') }} -
                                                {{ $i->ordenObj->fecha_inicio }}
                                            </button>
                                        </h5>
                                    </div>

                                    <div id="collapse{{ $i->ordenObj->id }}" class="collapse hide"
                                        aria-labelledby="headingOne" data-parent="#accordion">
                                        <div class="card-body">
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
                                                        @foreach ($i->ordenObj->detalles as $d)
                                                            <tr>
                                                                <td><i class="{{ $d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary' }}"
                                                                        aria-hidden="true"
                                                                        style="{{ $d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;' }}"></i>
                                                                    - {{ $d->nombre_producto ?? '' }}
                                                                </td>
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
                            @endforeach
                                @foreach ($data['ventas'] as $i)
                                    <div class="card">
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0">
                                                <button class="btn btn-primary" style="width: 100%" onclick='tickete("{{ $i->id }}")'>IMPRIMIR

                                                <button class="btn btn-link" data-toggle="collapse"
                                                    data-target="#collapse{{ $i->id }}" aria-expanded="false"
                                                    aria-controls="collapseOne">
                                                    {{ $i->nombre_cliente ?? '*' }} | ORD-{{ $i->numero_orden }} - CRC
                                                    {{ number_format($i->total ?? '0.00', 2, '.', ',') }} -
                                                    {{ $i->fecha_inicio }}
                                                </button>
                                            </h5>
                                        </div>

                                        <div id="collapse{{ $i->id }}" class="collapse hide"
                                            aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">
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
                                                            @foreach ($i->detalles as $d)
                                                                <tr>
                                                                    <td><i class="{{ $d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary' }}"
                                                                            aria-hidden="true"
                                                                            style="{{ $d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;' }}"></i>
                                                                        - {{ $d->nombre_producto ?? '' }}
                                                                    </td>
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
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
    </div>
    </section>
    </div>
    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    
    <form id="formIngresoGastoRechazar" action="{{ URL::to('ingresos/gastos/rechazar') }}" style="display: none"
        method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idIngresoGastoRechazar" id="idIngresoGastoRechazar" value="-1">
        <input type="hidden" name="idIngreso" id="idIngreso" value="-1">
    </form>

    <form id="formEliminarIngreso" action="{{ URL::to('ingresos/eliminar') }}" style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idIngresoEliminar" id="idIngresoEliminar" value="-1">

    </form>

    <form id="formAprobarIngreso" action="{{ URL::to('ingresos/aprobar') }}" style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idIngresoAprobar" id="idIngresoAprobar" value="-1">
    </form>

    <form id="formRechazarIngreso" action="{{ URL::to('ingresos/rechazar') }}" style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idIngresoRechazar" id="idIngresoRechazar" value="-1">
    </form>
@endsection
@section('script')

    <script src="{{ asset('assets/js/ingresos.js') }}"></script>



@endsection
