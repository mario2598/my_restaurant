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
                            <form action="{{ URL::to('gastos/guardar') }}" method="POST" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="id" value="-1">
                                <div class="card-header">
                                    <h4>Ingresar gasto</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-xl-4">
                                            <div class="form-group">
                                                <label>Fecha Gasto </label>
                                                <input type="date" id="fecha" name="fecha" max='{{ date('Y-m-d') }}'
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Proveedor</label>
                                                <select class="form-control" name="proveedor">
                                                    <option value="" selected>Ninguno</option>
                                                    @foreach ($data['proveedores'] as $i)
                                                        <option value="{{ $i->id ?? -1 }}"
                                                            title="{{ $i->descripcion ?? '' }}">{{ $i->nombre ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de pago</label>
                                                <select class="form-control" name="tipo_pago">
                                                    @foreach ($data['tipos_pago'] as $i)
                                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->tipo ?? '' }}">
                                                            {{ $i->tipo ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de gasto</label>
                                                <select class="form-control" name="tipo_gasto">
                                                    @foreach ($data['tipos_gasto'] as $i)
                                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->tipo ?? '' }}">
                                                            {{ $i->tipo ?? '' }}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Número comprobante</label>
                                                <input type="text" class="form-control" name="num_comprobante"
                                                    maxlength="50">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Total CRC</label>
                                                <input type="number" step="any" class="form-control" placeholder="0.00"
                                                    name="total" min="10" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Descripción del gasto</label>
                                                <textarea class="form-control" required="" name="descripcion"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group ">
                                                <label>Observación</label>
                                                <textarea class="form-control" name="observacion"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group ">
                                                <label>Foto comprobante</label>
                                                <input type="file" class="form-control" id="foto_comprobante"
                                                    name="foto_comprobante" accept="image/png, image/jpeg, image/jpg"
                                                    onchange="fileValidation()">
                                                <input type="text" id="foto_comprobante_b64" style='display:none;'
                                                    name="foto_comprobante_b64">

                                            </div>
                                        </div>


                                    </div>
                                </div>


                                <div class="card-footer text-right">
                                    <input type="submit" class="btn btn-primary" value="Registrar" />
                                    
                                </div>
                                <div class="card-footer text-right">
                                    <button type="button"
                                        onclick="window.location='{{ URL::to('gastos/administracion') }}'"
                                        class="btn btn-warning">Volver a todos los gastos</button>
                                </div>
                               
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>


@section('script')

    <script src="{{ asset('assets/js/gastos/gasto.js') }}"></script>

@endsection

@endsection
