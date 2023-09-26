@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ URL::to('ingresos/guardar') }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="id" value="{{ $data['ingreso']->id }}">
                                <div class="card-header">
                                    <h4>Ingreso   
                                        @if ($data['ingreso']->aprobado == 'N')
                                                Sin Aprobar
                                            @endif
                                            @if ($data['ingreso']->aprobado == 'S')
                                                Aprobado
                                            @endif
                                            @if ($data['ingreso']->aprobado == 'R')
                                                Rechazado
                                            @endif
                                             - {{ $data['ingreso']->nombreUsuario }}  - CRC
                                             {{ number_format($data['ingreso']->subtotal ?? '0.00', 2, '.', ',') }}
                                            </h4>

                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['ingreso']->fecha }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Sucursal</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['ingreso']->nombreSucursal }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto Efectivo (CRC)
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_efectivo"
                                                    name="monto_efectivo"
                                                    value="{{ $data['ingreso']->monto_efectivo ?? '' }}"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto Tarjeta (CRC)
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_tarjeta"
                                                    name="monto_tarjeta"
                                                    value="{{ $data['ingreso']->monto_tarjeta ?? '' }}" placeholder="0.00"
                                                    min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto SINPE (CRC) 
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_sinpe"
                                                    name="monto_sinpe" value="{{ $data['ingreso']->monto_sinpe ?? '' }}"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo ingreso</label>
                                                <select class="form-control space_disabled" name="tipo_ingreso">
                                                    @foreach ($data['tipos_ingreso'] as $i)
                                                        <option value="{{ $i->id }}" title="{{ $i->tipo ?? '' }}"
                                                            @if ($i->id == ($data['ingreso']->tipo ?? -1)) selected @endif>{{ $i->tipo }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @if ($data['ingreso']->cliente != null)
                                            <div class="col-12 col-md-4 col-lg-4">
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

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label>Descripción del ingreso</label>
                                                <textarea class="form-control" required maxlength="300" readonly
                                                    name="descripcion">{{ $data['ingreso']->descripcion ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
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
                                        <a onclick='confirmarIngreso("{{ $data['ingreso']->id }}")'
                                            style="cursor: pointer; color:white;" class="btn btn-success">Confirmar</a>
                                    @endif
                                </div>


                            </form>
                        </div>
                    </div>
          
                    @if ($data['tieneVentas'])
                        <div class="col-12 col-sm-12 col-lg-12">
                            <div>
                                <h4>Ventas Relacionadas</h4>
                            </div>
                            <div class="card">
                                <form class="card-header-form">
                                    <div class="input-group">
                                        <input type="text" name="" id="input_buscar_generico" class="form-control"
                                            placeholder="Buscar..">
                                    </div>
                                </form>
                                <table class="table" id="tbl-detallesAnular" style="max-height: 100%;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" style="text-align: center">No.Factura</th>
                                            <th scope="col" style="text-align: center">Fecha</th>
                                            <th scope="col" style="text-align: center">Total pagado</th>
                                            <th scope="col" style="text-align: center">Cliente</th>
                                            <th scope="col" style="text-align: center">Imprimir</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-ventasRel">
                                        @foreach ($data['ventas'] as $i)
                                            <tr>
                                                <td style="text-align: center">{{ $i->numero_orden }}</td>
                                                <td style="text-align: center">{{ $i->fecha_inicio }}</td>
                                                <td style="text-align: center">{{ number_format($i->total ?? '0.00', 2, '.', ',') }}</td>
                                                <td style="text-align: center">{{ $i->nombre_cliente ?? '*' }}</td>
                                                <td style="text-align: center"><button class="btn btn-primary" style="width: 100%" 
                                                         onclick='tickete("{{ $i->id }}")'>IMPRIMIR
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
    </div>
    </section>
    </div>
    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    
@endsection
@section('script')

    <script src="{{ asset('assets/js/ingresos.js') }}"></script>



@endsection
