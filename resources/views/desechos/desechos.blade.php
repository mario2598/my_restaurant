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
                        <h4>Desechos</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ URL::to('desechos/filtro') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="row" style="width: 100%">
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Sucursal</label>
                                        <select class="form-control" id="sucursal" name="sucursal" required>
                                            <option value="T" selected>Todas</option>
                                            @foreach ($data['sucursales'] as $i)
                                                <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}"
                                                    @if ($i->id == $data['filtros']['sucursal'])
                                                    selected
                                            @endif
                                            >{{ $i->descripcion ?? '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Agrupación</label>
                                        <select class="form-control" id="grupo" name="grupo" required>
                                            <option value="P" 
                                            @if ('P' == $data['filtros']['grupo'])
                                                selected
                                            @endif
                                                >Productos</option>
                                            <option value="L" 
                                            @if ('L' == $data['filtros']['grupo'])
                                                selected
                                            @endif>Lotes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Buscar</label>
                                        <button type="submit" class="btn btn-primary btn-icon form-control"
                                            style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>

                            </div>
                        </form>
                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaDesechos">
                                    <thead>

                                        <tr>
                                            <th class="text-center">
                                                Sucursal
                                            </th>
                                            <th class="text-center">Código</th>

                                            <th class="text-center">Producto/Lote</th>
                                           
                                            <th class="text-center">
                                                Cantidad
                                            </th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">
                                        @foreach ($data['desechos'] as $i)
                                            <tr>
                                                <td class="text-center">
                                                    {{ strtoupper($i->sucursal_nombre ?? '') }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->codigo ?? '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->producto_nombre ?? '' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->cantidad ?? '' }}
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
        <form id="formEditarProducto" action="{{ URL::to('bodega/producto/editar') }}" style="display: none" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
        </form>
    </div>

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            var sucursal = $("#sucursal option[value='" + "{{ $data['filtros']['sucursal'] }}" + "']").html();
            var grupo = $("#grupo option[value='" + "{{ $data['filtros']['grupo'] }}" + "']").html();
            if(grupo == "L"){
                grupo = "LOTE"
            }
            if ("{{ $data['filtros']['sucursal'] }}" != 'T') {
                var topMesage = 'Reporte de desechos de la sucursal ' + sucursal +'.';
            } else {
                var topMesage = 'Reporte de desechos general.' + ;
            }
            var bottomMesage = 'Reporte de Desechos filtrado por';

            topMesage += '.' + ' Solicitud realizada por ' + "{{ session('usuario')['usuario'] }}" + '.';

            if ("{{ $data['filtros']['sucursal'] }}" != 'T') {
                bottomMesage += ' sucursal [ ' + sucursal + ' ],';
            } else {
                bottomMesage += ' sucursal [ Todas ],';
            }

            bottomMesage += ' agrupado por ' + grupo + ' .';

            bottomMesage += ' Desarrollado por Space Software CR. ';


            $('#tablaDesechos').DataTable({
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
                    filename: 'desechos_' + sucursal + '_el_amanecer'
                }, {
                    extend: 'pdf',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'desechos_' + sucursal + '_el_amanecer'
                }, {
                    extend: 'print',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'desechos_' + sucursal + '_el_amanecer'
                }]
            });

        }

    </script>
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
@endsection
