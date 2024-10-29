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
                            <form action="{{ URL::to('gastos/guardar') }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="id" value="{{ $data['gasto']->id }}">
                                <div class="card-header">
                                    <h4>Información del gasto - {{ $data['gasto']->nombreUsuario }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $data['gasto']->fecha }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Proveedor</label>
                                                <select class="form-control" name="proveedor">
                                                    <option value="" selected>Ninguno</option>
                                                    @foreach ($data['proveedores'] as $i)
                                                        <option value="{{ $i->id ?? -1 }}"
                                                            title="{{ $i->descripcion ?? '' }}"
                                                            @if ($i->id == ($data['gasto']->proveedor ?? -1)) selected @endif>
                                                            {{ $i->nombre ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de pago</label>
                                                <select class="form-control" name="tipo_pago">
                                                    @foreach ($data['tipos_pago'] as $i)
                                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->tipo ?? '' }}"
                                                            @if ($i->id == $data['gasto']->tipo_pago) selected @endif>
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
                                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->tipo ?? '' }}"
                                                            @if ($i->id == $data['gasto']->tipo_gasto) selected @endif>
                                                            {{ $i->tipo ?? '' }}</option>
                                                    @endforeach

                                                </select>
                                            </div>

                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Estado</label>
                                                <input type="text" class="form-control space_disabled"
                                                    name="num_comprobante" readonly
                                                    value="{{ $data['gasto']->estadoUsuario ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo de documento</label>
                                                <select class="form-control" name="tipo_documento">
                                                    <option value="F"
                                                        @if ($data['gasto']->tipo_documento == 'F') selected @endif>Factura</option>
                                                    <option value="O" title="Debera definir en observación"
                                                        @if ($data['gasto']->tipo_documento == 'O') selected @endif>Otro</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Número comprobante</label>
                                                <input type="text" class="form-control" name="num_comprobante"
                                                    value="{{ $data['gasto']->num_factura ?? '' }}" maxlength="50">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label>Total CRC</label>
                                                <input type="number" step="any" class="form-control" name="total"
                                                    value="{{ $data['gasto']->monto ?? '' }}" placeholder="0.00"
                                                    min="10" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label>Descripción del gasto</label>
                                                <textarea class="form-control" name="descripcion" required>{{ $data['gasto']->descripcion ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label>Observación</label>
                                                <textarea class="form-control" name="observacion">{{ $data['gasto']->observacion ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        @if ($data['gasto']->url_factura != null)
                                            <div class="col-sm-12 col-md-6 col-xl-4">
                                                <div class="form-group mb-0">
                                                    <label>Foto comprobante</label> <br>
                                                    <button id="showImageBtn" type="button" class="btn btn-primary"
                                                        onclick="toggleImage()">Mostrar comprobante</button>
                                                    <img id="idFactImg" src="{{ $data['gasto']->url_factura }}"
                                                        style="max-width: 100%; height: auto; display: none;"
                                                        alt="Imagen">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>


                                <div class="card-footer text-right">
                                    @if ($data['gasto']->codEstadoUsuario == 'EST_GASTO_APB')
                                        <input type="button" onclick='eliminarGastoAdmin("{{ $data['gasto']->id }}")'
                                            class="btn btn-warning" value="Eliminar" />
                                        <input type="submit" class="btn btn-primary" value="Guardar" />
                                    @endif
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
@endsection

<script>
    function toggleImage() {
        var img = document.getElementById("idFactImg");
        var btn = document.getElementById("showImageBtn");

        // Si la imagen está oculta, mostrarla
        if (img.style.display === "none") {
            img.style.display = "block";
            btn.innerText = "Ocultar comprobante"; // Cambiar texto del botón
        } else {
            img.style.display = "none";
            btn.innerText = "Mostrar comprobante"; // Cambiar texto del botón
        }
    }
</script>
