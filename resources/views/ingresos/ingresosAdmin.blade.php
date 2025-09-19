@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Ingresos</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" 
                                    id="btn_buscar_ingreso" class="form-control" placeholder="Buscar ingreso">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" ><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <form action="{{ URL::to('ingresos/administracion/filtro') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="row" style="width: 100%">

                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Sucursal</label>
                                        <select class="form-control" id="select_sucursal" name="sucursal">

                                            <option value="T" selected>Todos</option>

                                            @foreach ($data['sucursales'] as $i)
                                                <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}"
                                                    @if ($i->id == $data['filtros']['sucursal']) selected @endif>
                                                    {{ $i->descripcion ?? '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Tipo Ingreso</label>
                                        <select class="form-control" id="select_tipo_ingreso" name="tipo_ingreso">
                                            <option value="T" selected>Todos</option>
                                            @foreach ($data['tipos_ingreso'] as $i)
                                                <option value="{{ $i->id }}" title="{{ $i->tipo ?? '' }}"
                                                    @if ($i->id == $data['filtros']['tipo_ingreso']) selected @endif>{{ $i->tipo ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select class="form-control" name="aprobado">
                                            <option value="T" <?php if ($data['filtros']['aprobado'] == 'T') {
                                                echo 'selected';
                                            } ?>>Todos</option>
                                            @foreach ($data['estados_ingreso'] as $i)
                                                <option value="{{ $i->id }}" title="{{ $i->nombre ?? '' }}"
                                                    @if ($i->id == $data['filtros']['aprobado']) selected @endif>
                                                    {{ $i->nombre ?? '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Desde</label>
                                        <input type="date" class="form-control" name="desde"
                                            value="{{ $data['filtros']['desde'] ?? '' }}" />

                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Hasta</label>
                                        <input type="date" class="form-control" name="hasta"
                                            value="{{ $data['filtros']['hasta'] ?? '' }}" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-2">
                                    <div class="form-group">
                                        <label>Buscar</label>
                                        <button type="submit" class="btn btn-primary btn-icon form-control"
                                            style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-2">
                                    <div class="form-group">
                                        <label>+ Nuevo</label>
                                        <button type="button" onclick="window.location='{{ URL::to('ingresos/nuevo') }}'"
                                            class="btn btn-primary btn-icon form-control">Crear nuevo ingreso</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaIngresos">
                                    <thead>

                                        <tr>

                                            <th class="text-center">Tipo Ingreso</th>
                                            <th class="text-center">
                                                Monto
                                            </th>
                                            <th class="text-center">
                                                Usuario
                                            </th>
                                            <th class="text-center">Sucursal</th>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Estado</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">
                                        @foreach ($data['ingresos'] as $g)
                                            <tr class="space_row_table" style="cursor: pointer;"
                                                onclick='clickIngreso("{{ $g->id }}")'>

                                                <td class="text-center">{{ $g->nombre_tipo_ingreso ?? '' }}</td>
                                                <td class="text-center">
                                                    CRC {{ number_format($g->total ?? '0.00', 2, '.', ',') }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $g->nombreUsuario ?? '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $g->nombreSucursal }}
                                                </td>


                                                <td class="text-center">{{ $g->fecha ?? '' }}</td>
                                                <td class="text-center">
                                                    <div class="badge badge-success badge-shadow">
                                                        {{ $g->dscEstado ?? '' }}</div>
                                                </td>

                                            </tr>
                                        @endforeach

                                    </tbody>
                                    <tfoot>
                                        @if (count($data['ingresos']) > 0)
                                            <tr class="space_row_table">

                                                <td class="text-center" style="background: rgb(226, 196, 196);">
                                                    <strong>Total General</strong>
                                                </td>
                                                <td class="text-center" style="background: rgb(226, 196, 196);">
                                                    <strong> CRC
                                                        {{ number_format($data['totalIngresos'] ?? '0.00', 2, '.', ',') }}</strong>
                                                </td>
                                                <td class="text-center" style="background: rgb(226, 196, 196);">
                                                    ***
                                                </td>
                                                <td class="text-center" style="background: rgb(226, 196, 196);">
                                                    <strong> ***</strong>
                                                </td>

                                                <td class="text-center" style="background: rgb(226, 196, 196);">

                                                    <strong>***</strong>
                                                </td>
                                                <td class="text-center" style="background: rgb(226, 196, 196);">***</td>


                                            </tr>
                                        @endif
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        <form id="formIngreso" action="{{ URL::to('ingresos/ingreso') }}" style="display: none" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="idIngreso" id="idIngreso" value="-1">
        </form>

    </div>

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            var tipo_ingreso = $("#select_tipo_ingreso option[value='" + "{{ $data['filtros']['tipo_ingreso'] }}" + "']")
                .html();
            var sucursal = $("#select_sucursal option[value='" + "{{ $data['filtros']['sucursal'] }}" + "']").html();

            var topMesage = 'Reporte de Ingresos';
            var bottomMesage = 'Reporte general de ingresos ';

            if ("{{ $data['filtros']['desde'] }}" != '') {
                topMesage += ' desde el ' + "{{ $data['filtros']['desde'] }}";
            }
            if ("{{ $data['filtros']['hasta'] }}" != '') {
                topMesage += ' hasta el ' + "{{ $data['filtros']['hasta'] }}";
            }
            var fechaActual = new Date();
            var fechaFormateada = fechaActual.toLocaleString();
            topMesage += '.' + ' Solicitud realizada el ' + fechaFormateada + ' por ' +
                "{{ session('usuario')['usuario'] }}" + '.';


            if ("{{ $data['filtros']['sucursal'] }}" != 'T') {
                bottomMesage += ' sucursal [ ' + sucursal + ' ],';
            } else {
                bottomMesage += ' sucursal [ Todas ],';
            }

            if ("{{ $data['filtros']['tipo_ingreso'] }}" != '') {
                bottomMesage += ' tipo de ingreso [ ' + tipo_ingreso + ' ],';
            } else {
                bottomMesage += ' tipo de ingreso [ Todas ],';
            }

            bottomMesage += ' {{ env('APP_NAME', 'SPACE SOFTWARE CR') }}. ';


            $('#tablaIngresos').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                buttons: [{
                    extend: 'excel',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    messageTop: topMesage,
                    footer: true,
                    messageBottom: bottomMesage,
                    filename: 'reporte_ingresos_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }, {
                    extend: 'pdf',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    footer: true,
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_ingresos_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }, {
                    extend: 'print',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    footer: true,
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_ingresos_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }]
            });

        }
    </script>
@endsection


@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/ingresos/ingresosAdmin.js') }}"></script>
@endsection
