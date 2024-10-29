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
                        <form action="{{ URL::to('mant/guardarparametrosgenerales') }}" enctype="multipart/form-data"
                            autocomplete="off" method="POST">
                            {{ csrf_field() }}
                            <div class="row" style="width: 100%">
                                <div class="col-sm-12 col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <label>Logo Factura </label>
                                        <img src="{{ asset('assets/images/default-logo.png') }}"
                                            style="max-width: 100%; height: auto;" alt="Imagen">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="form-group ">
                                        <label>Cambiar Logo </label>
                                        <input type="file"id="logo_empresa" name="logo_empresa"
                                            accept="image/png, image/jpeg, image/jpg">
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <label>Guardar</label>
                                        <input type="submit" class="btn btn-primary form-control" value="Guardar">
                                    </div>
                                </div>


                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </section>



    </div>



@section('script')


@endsection
