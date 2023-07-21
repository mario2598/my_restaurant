@extends('layout.master')

@section('styles')
    <link rel="stylesheet" href='{{ asset('assets/bundles/pretty-checkbox/pretty-checkbox.min.css') }}'>
@endsection


@section('content')

    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Reporte de moviminetos de inventario</h4>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Tipo de movimiento</label>
                                    <select class="form-control" id="tipo_movimiento" name="tipo_movimiento" disabled>
                                        <option value="T" selected>Todos</option>
                                        @foreach ($data['tipos_movimiento'] as $i)
                                            <option value="{{ $i->id ?? -1 }}" title="{{ $i->codigo ?? '' }}" @if ($i->id == $data['filtros']['tipo_movimiento'])
                                                selected
                                        @endif
                                        >{{ $i->descripcion ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Despacho</label>
                                    <select class="form-control" id="select_despacho" name="despacho" disabled>
                                        <option value="T" selected>Todos</option>
                                        @foreach ($data['sucursales'] as $i)
                                            <option value="{{ $i->id }}" title="{{ $i->descripcion ?? '' }}" @if ($i->id == $data['filtros']['despacho'])
                                                selected
                                        @endif
                                        >{{ $i->descripcion ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Destino</label>
                                    <select class="form-control" id="select_destino" name="destino" disabled>
                                        <option value="T" selected>Todos</option>
                                        @foreach ($data['sucursales'] as $i)
                                            <option value="{{ $i->id }}" title="{{ $i->descripcion ?? '' }}" @if ($i->id == $data['filtros']['destino'])
                                                selected
                                        @endif
                                        >{{ $i->descripcion ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select class="form-control" id="select_estado" name="estado" disabled>
                                        <option value="TT" selected>Todos</option>
                                        <option value="P" title="Pendiente de completar" @if ($data['filtros']['estado'] == 'P')
                                            selected
                                            @endif
                                            >Pendiente
                                        </option>
                                        <option value="T" title="Movimiento Terminado" @if ($data['filtros']['estado'] == 'T')
                                            selected
                                            @endif
                                            >Terminado
                                        </option>
                                        <option value="C" title="Cancelado" @if ($data['filtros']['estado'] == 'C')
                                            selected
                                            @endif
                                            >Cancelado
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" class="form-control" id="desdeFecha" name="desde"
                                        value="{{ $data['filtros']['desde'] ?? date('Y-m-d') }}" disabled/>

                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date" class="form-control" id="hastaFecha" name="hasta"
                                        value="{{ $data['filtros']['hasta'] ?? date('Y-m-d') }}" disabled/>
                                </div>
                            </div>

                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div id="table_monitor_cont" class="table-responsive">
                                <table class="table table-striped" id="tablaMovs">
                                    <thead>

                                        <tr>

                                            <th class="text-center">Tipo Movimiento</th>
                                            <th class="text-center">
                                                Fecha
                                            </th>
                                            <th class="text-center">
                                                Despacho
                                            </th>
                                            <th class="text-center">
                                                Destino
                                            </th>
                                            <th class="text-center">
                                                Encargado
                                            </th>
                                            <th class="text-center">Estado</th>

                                        </tr>
                                    </thead>

                                    <tbody id="tbodyBitacoraMovimientos">

                                        @foreach ($data['movimientos'] as $i)
                                            <tr class="space_row_table" style="cursor: pointer;"
                                                onclick='goMovimientoInv("{{ $i->id }}")'>
                                                <td class="text-center" title="{{ $i->codigo_movimiento ?? '' }}">
                                                    {{ strtoupper($i->descripcion_movimiento ?? '') }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->fecha ?? '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->despacho ?? '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->detino ?? '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre_usuario ?? '' }}
                                                </td>
                                                <td class="text-center">
                                                    @switch($i->estado)
                                                        @case("C")
                                                        Cancelado
                                                        @break
                                                        @case("P")
                                                        Pendiente
                                                        @break
                                                        @case("T")
                                                        Terminado
                                                        @break
                                                        @default

                                                    @endswitch

                                                </td>

                                            </tr>
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

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            updateTable();

        }

        function updateTable() {
            var destino = $("#select_destino option[value='" + "{{ $data['filtros']['destino'] }}" + "']").html();
            var despacho = $("#select_despacho option[value='" + "{{ $data['filtros']['despacho'] }}" + "']").html();
            var tipo_mov = $("#tipo_movimiento option[value='" + "{{ $data['filtros']['tipo_movimiento'] }}" + "']").html();
            var estado = $("#select_estado option[value='" + "{{ $data['filtros']['estado'] }}" + "']").html();

            var topMesage = 'Reporte de Movimientos de Inventario';
            var bottomMesage = 'Reporte de movimientos de inventario filtrado por';

            if ("{{ $data['filtros']['desde'] }}" != '') {
                topMesage += ' desde el ' + "{{ $data['filtros']['desde'] }}";
            }
            if ("{{ $data['filtros']['hasta'] }}" != '') {
                topMesage += ' hasta el ' + "{{ $data['filtros']['hasta'] }}";
            }

            topMesage += '.' + ' Solicitud realizada por ' + "{{ session('usuario')['usuario'] }}" + '.';

            if ("{{ $data['filtros']['estado'] }}" != 'TT') {
                bottomMesage += ' estado [ ' + estado + ' ],';
            } else {
                bottomMesage += ' estado [ Todos ],';
            }

            if ("{{ $data['filtros']['tipo_movimiento'] }}" != 'T') {
                bottomMesage += ' Tipo de movimiento [ ' + tipo_mov + ' ],';
            } else {
                bottomMesage += ' Tipo de movimiento [ Todos ],';
            }

            if ("{{ $data['filtros']['destino'] }}" != 'T') {
                bottomMesage += ' destino [ ' + destino + ' ],';
            } else {
                bottomMesage += ' destino [ Todos ],';
            }

            if ("{{ $data['filtros']['despacho'] }}" != 'T') {
                bottomMesage += ' despacho [ ' + despacho + ' ].';
            } else {
                bottomMesage += 'despacho [ Todos ].';
            }

            bottomMesage += ' Desarrollado por Space Software CR. ';


            $('#tablaMovs').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                'fixedHeader': {
                    'header': true,
                    'footer': true
                },
                buttons: [{
                    extend: 'excel',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_movimientos_inventario_el_amanecer'
                }, {
                    extend: 'pdf',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_movimientos_inventario_el_amanecer'
                }, {
                    extend: 'print',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_movimientos_inventario_el_amanecer'
                }]
            });

        }

    </script>

@endsection


@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

@endsection
