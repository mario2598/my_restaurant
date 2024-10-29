@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/izitoast/css/iziToast.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')


    <style>
        .trIngreso :hover {
            font-weight: bold;
        }
    </style>

    <script>
        var tipos = [];
        var promociones = [];
        var promocionSeleccionada = {
            "id": 0,
            "dscTipo": "",
            "cod_general": "",
            "descuento": "",
            "fecha_inicio": "",
            "fecha_fin": "",
            "descripcion": "",
            "codigo": "",
            "activo": "",
            "cant_codigos": ""
        };
    </script>

    @foreach ($data['tipos'] as $tipo)
        <script>
            var auxTipo = {
                "id": "{{ $tipo->id }}",
                "nombre": "{{ $tipo->nombre ?? '' }}",
                "cod_general": "{{ $tipo->cod_general ?? 0 }}"
            };
            tipos.push(auxTipo);
        </script>
    @endforeach


    <div class="main-content">

        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Códigos promoción</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="input_buscar_generico" class="form-control"
                                    placeholder="Buscar..">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"
                                        onclick="$('#input_buscar_generico').trigger('change');"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">

                        <div class="row" style="width: 100%">
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <a class="btn btn-primary" title="Agregar Impuesto" style="color:white;"
                                        onclick="abrirModalNuevaPromo()">+ Agregar</a>
                                </div>
                            </div>


                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="">
                                    <thead>
                                        <tr>

                                            <th class="space-align-center">#</th>
                                            <th class="space-align-center">Descripción</th>
                                            <th class="space-align-center">Fecha Inicio</th>
                                            <th class="space-align-center">Fecha Fin</th>
                                            <th class="space-align-center">Código</th>
                                            <th class="space-align-center">Descuento</th>
                                            <th class="space-align-center">Tipo Descuento</th>
                                            <th class="space-align-center">Códigos restantes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico" class="trIngreso">
                                        @foreach ($data['promociones'] as $p)
                                            <tr onclick='abrirModalEditarPromo("{{ $p->id ?? '' }}")'
                                                style="cursor: pointer">
                                                <td class="space-align-center">
                                                    {{ $p->id ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $p->descripcion ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $p->fecha_inicio ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $p->fecha_fin ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $p->codigo ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $p->descuento ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $p->dscTipo ?? '' }}
                                                </td>
                                                <td class="space-align-center">
                                                    {{ $p->cant_codigos ?? '' }}
                                                </td>


                                            </tr>
                                            <script>
                                                var auxProm = {
                                                    "id": "{{ $p->id }}",
                                                    "dscTipo": "{{ $p->dscTipo ?? '' }}",
                                                    "cod_general": "{{ $p->cod_general ?? 0 }}",
                                                    "descuento": "{{ $p->descuento ?? 0 }}",
                                                    "fecha_inicio": "{{ $p->fecha_inicio ?? 0 }}",
                                                    "fecha_fin": "{{ $p->fecha_fin ?? 0 }}",
                                                    "descripcion": "{{ $p->descripcion ?? 0 }}",
                                                    "codigo": "{{ $p->codigo ?? 0 }}",
                                                    "activo": "{{ $p->activo  }}" ,
                                                    "cant_codigos": "{{ $p->cant_codigos ?? 0 }}"
                                                };
                                                promociones.push(auxProm);
                                            </script>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </section>
    </div>

    <div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Promoción</h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        onclick="cerrarModalGenerico()">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control " id="fec_inicio">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control " id="fec_fin">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">

                                    <label class="form-label">Tipo descuento</label>
                                    <select class="form-control" id="tipo_descuento">
                                        <option value="0" selected>Seleccione un tipo</option>
                                        @foreach ($data['tipos'] as $i)
                                            <option value="{{ $i->cod_general ?? '' }}" title="{{ $i->nombre ?? '' }}">
                                                {{ $i->nombre ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cantidad Descuento</label>
                                    <input type="number" class="form-control " id="descuento">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Código de descuento</label>
                                    <input type="text" class="form-control " id="cod_descuento">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cantidad usos restantes</label>
                                    <input type="number" class="form-control " id="cod_rest">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-sm-8">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Estado  : </label>
                                    <input type="checkbox" id="activo" checked>
                                    <label for="activo">Activo / Inactivo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary" onclick="cerrarModalGenerico()">Volver</a>
                    <button type="button" class="btn btn-info " onclick="guardarPromocion()">Guardar <i
                            class="fas fa-payment"aria-hidden="true"></i></button>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal-->
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/mant_promociones.js') }}"></script>
@endsection
