@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')

    <script>
        var productos = [];
        var id_prod_seleccionado = -1;
    </script>

    <div class="main-content">
        <section class="section">
            <div class="section-body">

                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Productos Menú</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="btn_buscar_pro" class="form-control"
                                    placeholder="Buscar producto">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <form action="{{ URL::to('menu/productos/filtro') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="row" style="width: 100%">
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Categoría</label>
                                        <select class="form-control" id="select_categoria" name="categoria">
                                            <option value="T" selected>Todos</option>
                                            @foreach ($data['categorias'] as $i)
                                                <option value="{{ $i->id ?? -1 }}" title="{{ $i->categoria ?? '' }}"
                                                    @if ($i->id == $data['filtros']['categoria']) selected @endif>
                                                    {{ $i->categoria ?? '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Tipo Impuesto</label>
                                        <select class="form-control" id="select_impuesto" name="impuesto">
                                            <option value="T" selected>Todos</option>
                                            @foreach ($data['impuestos'] as $i)
                                                <option value="{{ $i->id }}" title="{{ $i->descripcion ?? '' }}"
                                                    @if ($i->id == $data['filtros']['impuesto']) selected @endif>
                                                    {{ $i->descripcion ?? '' }}</option>
                                            @endforeach
                                        </select>
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
                                        <label>Nuevo</label>
                                        <a href="{{ url('menu/producto/nuevo') }}"
                                            class="btn btn-success btn-icon form-control"
                                            style="cursor: pointer;color:white;"><i class="fas fa-plus"></i> Agregar</a>
                                    </div>

                                </div>

                            </div>
                        </form>
                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaProductos">
                                    <thead>


                                        <tr>
                                            <th class="text-center">Código</th>

                                            <th class="text-center">Producto</th>
                                            <th class="text-center">
                                                Categoría
                                            </th>
                                            <th class="text-center">
                                                Impuestos %
                                            </th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Posición Menú</th>
                                            <th class="text-center">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">

                                        @foreach ($data['productos'] as $g)
                                            <tr class="space_row_table" style="cursor: pointer;">

                                                <td class="text-center" onclick='clickProducto("{{ $g->id }}")'>
                                                    {{ $g->codigo ?? '' }}</td>
                                                <td class="text-center" onclick='clickProducto("{{ $g->id }}")'>
                                                    {{ $g->nombre }}
                                                </td>
                                                <td class="text-center" onclick='clickProducto("{{ $g->id }}")'>
                                                    {{ $g->nombre_categoria ?? '' }}
                                                </td>
                                                <td class="text-center" onclick='clickProducto("{{ $g->id }}")'>
                                                    {{ $g->porcentaje_impuesto ?? '0' }} %
                                                </td>

                                                <td class="text-center" onclick='clickProducto("{{ $g->id }}")'>
                                                    CRC {{ number_format($g->precio ?? '0.00', 2, '.', ',') }}
                                                </td>
                                                <td onclick='clickProducto("{{ $g->id }}")' class="text-center">
                                                    {{ $g->posicion_menu ?? 0}}
                                                </td>

                                                <td class="text-center">
                                                    <a class="btn btn-primary btn-icon" title="Composición del producto"
                                                        onclick='clickMateriaPrima("{{ $g->id }}")'
                                                        style="cursor: pointer;"><i class="fas fa-cog"></i></a>

                                                    <a class="btn btn-secondary btn-icon" title="Extras del producto"
                                                        onclick='clickExtras("{{ $g->id }}")'
                                                        style="cursor: pointer;"><i class="fas fa-list"></i></a>

                                                </td>
                                            </tr>
                                            <script>
                                                var inv = [];
                                                var ext = [];
                                            </script>
                                            @foreach ($g->materia_prima as $mp)
                                                <script>
                                                    inv.push({
                                                        "id": "{{ $mp->id_mp_x_prod }}",
                                                        "nombre": "{{ $mp->nombre }}",
                                                        "cantidad": "{{ $mp->cantidad }}",
                                                        "unidad_medida": "{{ $mp->unidad_medida }}"
                                                    });
                                                </script>
                                            @endforeach

                                            @foreach ($g->extras as $e)
                                                <script>
                                                    ext.push({
                                                        "id": "{{ $e->id }}",
                                                        "descripcion": "{{ $e->descripcion }}",
                                                        "precio": "{{ $e->precio }}",
                                                        "dsc_grupo": "{{ $e->dsc_grupo }}",
                                                        "es_requerido": "{{ $e->es_requerido }}",
                                                        "multiple": "{{ $e->multiple }}"
                                                    });
                                                </script>
                                            @endforeach

                                            <script>
                                                productos.push({
                                                    "id_producto": "{{ $g->id }}",
                                                    "materia_prima": inv,
                                                    "extras": ext
                                                });
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
        <form id="formEditarProducto" action="{{ URL::to('menu/producto/editar') }}" style="display: none" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
        </form>
    </div>


    <div class="modal fade bs-example-modal-center" id='mdl-materia-prima' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <div class="row" style="width: 100%">
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <h5 class="modal-title">Composición Materia Prima</h5>

                        </div>
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <div class="form-group">
                                <label>Materia Prima</label>
                                <select class="form-control" id="select_prod_mp" style="width: 100%"
                                    name="select_prod_mp">
                                    @foreach ($data['materia_prima'] as $i)
                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->unidad_medida ?? '' }}">
                                            {{ $i->nombre ?? '' }} - {{ $i->unidad_medida ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <div class="form-group">
                                <label>Cantidad requerida</label>
                                <input type="number" class="form-control" id="ipt_cantidad_req" name="ipt_cantidad_req"
                                    value="" required step="0.01">
                                <input type="hidden" id="ipt_id_prod_mp" name="ipt_id_prod_mp" value="-1">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <div class="form-group">
                                <a class="btn btn-primary" title="Guardar Composición"
                                    onclick="agregarMateriaPrimaProducto()" style="color:white;cursor:pointer;">Guardar
                                    Composición</a>
                                <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarMateriaPrima()'
                                    style="cursor: pointer;">Cerrar</a>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-body">
                    <table class="table" id="tbl-inv" style="max-height: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col" style="text-align: center">Cantidad</th>
                                <th scope="col" style="text-align: center">Unidad Medida</th>
                                <th scope="col" style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-inv">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade bd-example-modal-lg" id='mdl-extras'  tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl ">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <div class="row" style="width: 100%">
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <h5 class="modal-title">Extras del producto</h5>

                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>Descripción extra</label>
                                <input type="text" class="form-control" id="ipt_dsc_ext" name="ipt_dsc_ext"
                                    value="" required>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>Descripción grupo</label>
                                <input type="text" class="form-control" id="ipt_dsc_gru_ext" name="ipt_dsc_gru_ext"
                                    value="" required>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>Precio</label>
                                <input type="number" class="form-control" id="ipt_precio_ext" name="ipt_precio_ext"
                                    value="" required step="0.01">
                                <input type="hidden" id="ipt_id_prod_ext" name="ipt_id_prod_ext" value="-1">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>Materia Prima</label>
                                <select class="form-control" id="select_prod_mp_extra" style="width: 100%"
                                    name="select_prod_mp_extra">
                                    <option value="" title="Sin materia prima asignada">
                                        Sin asignar</option>
                                    @foreach ($data['materia_prima'] as $i)
                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->unidad_medida ?? '' }}">
                                            {{ $i->nombre ?? '' }} - {{ $i->unidad_medida ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>Cantidad requerida Materia Prima</label>
                                <input type="number" class="form-control" id="ipt_cantidad_req_extra" name="ipt_cantidad_req_extra"
                                    value="" required step="0.01">
                                <input type="hidden" id="ipt_id_prod_mp" name="ipt_id_prod_mp" value="-1">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label for="requisito">¿Es requerido?</label>
                                <input type="checkbox" id="requisito">
                            </div>
                            <div class="form-group">
                                <label for="multiple">¿Permite selección multiple?</label>
                                <input type="checkbox" id="multiple">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-6 col-xl-6">
                            <div class="form-group">
                                <a class="btn btn-primary" title="Guardar Composición"
                                    onclick="agregarExtraProducto()" style="color:white;cursor:pointer;">Guardar
                                    Extra</a>
                                <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarExtras()'
                                    style="cursor: pointer;">Cerrar</a>
                            </div>
                        </div>
                    </div>

                </div>
 
                <div class="modal-body" style="max-height: 100%;padding: 5px !important; max-height: 70vh; overflow-y: auto; ">
                    <table class="table" id="tbl-inv"  >
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Descripción</th>
                                <th scope="col" style="text-align: center">Precio</th>
                                <th scope="col" style="text-align: center">Grupo</th>
                                <th scope="col" style="text-align: center">Materia Prima</th>
                                <th scope="col" style="text-align: center">Cant Mat Prima</th>
                                <th scope="col" style="text-align: center">Es requerido</th>
                                <th scope="col" style="text-align: center">Es Multiple</th>
                                <th scope="col" style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-ext">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            var categoria = $("#select_categoria option[value='" + "{{ $data['filtros']['categoria'] }}" + "']").html();
            var impuesto = $("#select_impuesto option[value='" + "{{ $data['filtros']['impuesto'] }}" + "']").html();

            var topMesage = 'Reporte de Productos del Menú';
            var bottomMesage = 'Reporte de productos del Menú filtrado por';

            topMesage += '.' + ' Solicitud realizada por ' + "{{ session('usuario')['usuario'] }}" + '.';

            if ("{{ $data['filtros']['categoria'] }}" != 'T') {
                bottomMesage += ' categoria [ ' + categoria + ' ],';
            } else {
                bottomMesage += ' categoria [ Todas ],';
            }

            if ("{{ $data['filtros']['impuesto'] }}" != 'T') {
                bottomMesage += ' tipo de impuesto [ ' + impuesto + ' ],';
            } else {
                bottomMesage += 'tipo de impuesto [ Todos ].';
            }

            bottomMesage += ' {{ env('APP_NAME', 'SPACE SOFTWARE CR') }} ';


            $('#tablaProductos').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                'fixedHeader': {
                    'header': true,
                    'footer': true
                },
                buttons: [{
                    extend: 'excel',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_productos'
                }, {
                    extend: 'pdf',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_productos'
                }, {
                    extend: 'print',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_productos'
                }]
            });

        }
    </script>
@endsection



@section('script')

    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/restaurante/productos.js') }}"></script>
@endsection
