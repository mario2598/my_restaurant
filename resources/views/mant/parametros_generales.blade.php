@extends('layout.master')

@section('style')


@endsection


@section('content')

    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Parámetros generales</h4>

                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">Logos por sucursal</h5>
                        <p class="text-muted small">Cada imagen se guarda de forma independiente en el servidor y en la base de datos.</p>
                        <div class="row" style="width: 100%">
                            @foreach($data['sucursales'] ?? [] as $sucursal)
                            <div class="col-12 col-md-6 col-xl-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <label class="font-weight-bold d-block">{{ $sucursal->descripcion }}</label>
                                    <div class="row mt-2">
                                        <div class="col-12 mb-3">
                                            <form action="{{ URL::to('mant/guardarparametrosgenerales') }}" enctype="multipart/form-data" autocomplete="off" method="POST">
                                                {{ csrf_field() }}
                                                <label class="small font-weight-bold mb-1">Logo sistema</label>
                                                <div style="width: 100%; height: 100px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                                    @if(!empty($sucursal->url_logo_sistema) && file_exists(public_path($sucursal->url_logo_sistema)))
                                                        <img src="{{ asset($sucursal->url_logo_sistema) }}?v={{ time() }}" alt="Logo sistema {{ $sucursal->descripcion }}" style="width: 100%; height: 100%; object-fit: contain;">
                                                    @else
                                                        <img src="{{ asset('assets/images/default-logo.png') }}?v={{ time() }}" alt="Sin logo sistema" style="width: 100%; height: 100%; object-fit: contain;">
                                                    @endif
                                                </div>
                                                <input type="file" name="logo_sistema[{{ $sucursal->id }}]" accept="image/*" class="form-control form-control-sm mt-2" required>
                                                <button type="submit" class="btn btn-primary btn-sm mt-2">Guardar logo sistema</button>
                                            </form>
                                        </div>

                                        <div class="col-12">
                                            <form action="{{ URL::to('mant/guardarparametrosgenerales') }}" enctype="multipart/form-data" autocomplete="off" method="POST">
                                                {{ csrf_field() }}
                                                <label class="small font-weight-bold mb-1">Logo factura</label>
                                                <div style="width: 100%; height: 100px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                                    @if(!empty($sucursal->url_logo_factura) && file_exists(public_path($sucursal->url_logo_factura)))
                                                        <img src="{{ asset($sucursal->url_logo_factura) }}?v={{ time() }}" alt="Logo factura {{ $sucursal->descripcion }}" style="width: 100%; height: 100%; object-fit: contain;">
                                                    @else
                                                        <img src="{{ asset('assets/images/default-logo.png') }}?v={{ time() }}" alt="Sin logo factura" style="width: 100%; height: 100%; object-fit: contain;">
                                                    @endif
                                                </div>
                                                <input type="file" name="logo_factura[{{ $sucursal->id }}]" accept="image/*" class="form-control form-control-sm mt-2" required>
                                                <button type="submit" class="btn btn-primary btn-sm mt-2">Guardar logo factura</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </section>



    </div>



@section('script')


@endsection
