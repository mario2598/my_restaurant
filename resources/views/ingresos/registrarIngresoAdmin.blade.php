@extends('layout.master')

@section('content')
    @include('layout.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-12">
                        <div class="card">
                            <form action="{{ URL::to('ingresos/guardar') }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="id" value="-1">
                                <div class="card-header">
                                    <h4>Registrar ingreso</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-xl-4">
                                            <div class="form-group">
                                                <label>Fecha Ingreso </label>
                                                <input type="date" id="fecha" name="fecha"
                                                    max='{{ date('Y-m-d') }}' class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto Efectivo (CRC)</label>
                                                <input type="number" class="form-control" step=any id="monto_efectivo"
                                                    name="monto_efectivo"
                                                    value="{{ $data['datos']['monto_efectivo'] ?? '' }}" placeholder="0.00"
                                                    min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto Tarjeta (CRC)</label>
                                                <input type="number" class="form-control" step=any id="monto_tarjeta"
                                                    name="monto_tarjeta" value="{{ $data['datos']['monto_tarjeta'] ?? '' }}"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto SINPE (CRC)</label>
                                                <input type="number" class="form-control" step=any id="monto_sinpe"
                                                    name="monto_sinpe" value="{{ $data['datos']['monto_sinpe'] ?? '' }}"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group mb-0">
                                                <label>Pagos en otras monedas (JSON opcional)</label>
                                                <textarea class="form-control font-monospace" name="ingreso_pagos_json" rows="4"
                                                    placeholder='[{"medio_pago":"EFECTIVO","moneda_id":2,"monto_moneda":100,"tipo_cambio_snapshot":520}]'>{{ old('ingreso_pagos_json', $data['datos']['ingreso_pagos_json'] ?? '') }}</textarea>
                                                <small class="text-muted d-block">Si completa este JSON, el total en CRC debe cumplir la regla de mínimo (10) según suma de <code>monto_moneda × tipo_cambio</code>.
                                                    Los campos efectivo/tarjeta/sinpe arriba pueden quedar en 0. Monedas activas (id):
                                                    @if (isset($data['monedas']) && count($data['monedas']) > 0)
                                                        @foreach ($data['monedas'] as $m)
                                                            {{ $m->id }}={{ $m->cod_general }}{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                    @endif
                                                </small>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo ingreso</label>
                                                <select class="form-control" name="tipo_ingreso">
                                                    @foreach ($data['tipos_ingreso'] as $i)
                                                        <option value="{{ $i->id }}" title="{{ $i->tipo ?? '' }}"
                                                            @if ($i->id == ($data['datos']['tipo_ingreso'] ?? -1)) selected @endif>
                                                            {{ $i->tipo }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group mb-0">
                                                <label>Descripción del ingreso</label>
                                                <textarea class="form-control" required maxlength="300" name="descripcion">{{ $data['datos']['descripcion'] ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group mb-0">
                                                <label>Observación</label>
                                                <textarea class="form-control" name="observacion" maxlength="150">{{ $data['datos']['observacion'] ?? '' }}</textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <input type="submit" class="btn btn-primary" value="Registrar" />
                                    <button type="button"
                                        onclick="window.location='{{ URL::to('ingresos/administracion') }}'"
                                        class="btn btn-primary">Volver a todos los ingresos</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection
