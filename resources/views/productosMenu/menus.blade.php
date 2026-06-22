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
                        <h4>Menús</h4>
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

                        <div class="row" style="width: 100%">
                            <div class="col-sm-12 col-md-12 col-xl-12">
                                <form id="form_cargar_menu" action="{{ URL::to('menu/menus/filtro') }}" method="POST">
                                    {{ csrf_field() }}
                                    <div class="row align-items-end">
                                        <div class="col-sm-12 col-md-6 col-xl-5">
                                            <label class="font-weight-bold text-muted mb-1" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">
                                                <i class="fas fa-store mr-1"></i>Sucursal
                                            </label>
                                            <select class="form-control form-control-sm" id="sucursal" name="sucursal" required onchange="this.form.submit()">
                                                <option value="-1">Seleccione una sucursal</option>
                                                @foreach ($data['sucursales'] as $i)
                                                    <option value="{{ $i->id ?? '' }}"
                                                        title="{{ $i->descripcion ?? '' }}"
                                                        @if ($i->id == $data['filtros']['sucursal']) selected @endif>
                                                        {{ $i->descripcion ?? '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-12 col-md-auto mt-2 mt-md-0">
                                            <button type="button" class="btn btn-success btn-sm" onclick="$('#mdl_generico').modal('show');">
                                                <i class="fas fa-plus mr-1"></i>Agregar menú
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div id="contenedor_productos" class="col-sm-12 col-md-12 col-xl-12">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="tablaMenus">
                                        <thead>


                                            <tr>
                                                <th class="text-center" style="width:75px;">Código</th>
                                                <th class="text-center">Nombre</th>
                                                <th class="text-center d-none d-lg-table-cell">Descripción</th>
                                                <th class="text-center d-none d-md-table-cell">Categoría</th>
                                                <th class="text-center">Precio</th>
                                                <th class="text-center">Comanda</th>
                                                <th class="text-center" style="width:90px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_generico">
                                            @foreach ($data['menusSucursal'] as $g)
                                                <tr class="space_row_table" style="cursor:pointer;" onclick='clickProducto("{{ $g->id }}")'>
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-dark" style="font-size:0.78rem;">{{ $g->codigo ?? '' }}</span>
                                                    </td>
                                                    <td class="align-middle"><strong>{{ $g->nombre }}</strong></td>
                                                    <td class="align-middle d-none d-lg-table-cell" style="max-width:160px;">
                                                        <span class="d-block text-truncate text-muted" style="font-size:0.82rem;max-width:160px;" title="{{ $g->descripcion ?? '' }}">
                                                            {{ $g->descripcion ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center align-middle d-none d-md-table-cell">
                                                        <span class="badge badge-info" style="font-size:0.78rem;">{{ $g->nombre_categoria ?? '—' }}</span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="font-weight-bold" style="font-size:0.9rem;">₡{{ number_format($g->precio ?? '0.00', 2, '.', ',') }}</span>
                                                    </td>
                                                    <td class="text-center align-middle" onclick="event.stopPropagation()">
                                                        <button class="btn btn-outline-success btn-sm" style="font-size:0.78rem;"
                                                            onclick="cambiarComandera('{{ $g->id }}','{{ $g->nombreComanda }}',' {{ $g->nombre }}')"
                                                            title="Cambiar Comanda">
                                                            <i class="fas fa-utensils mr-1"></i>{{ $g->nombreComanda ?? 'General' }}
                                                        </button>
                                                    </td>
                                                    <td class="text-center align-middle" onclick="event.stopPropagation()">
                                                        <button class="btn btn-info btn-sm mr-1"
                                                            onclick="gestionarHorarios('{{ $g->id_pm_x_sucursal ?? $g->id }}', '{{ $g->nombre }}')"
                                                            title="Gestionar Horarios">
                                                            <i class="fas fa-clock"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="eliminarProdcutoDeMenu('{{ $g->id }}')"
                                                            title="Eliminar del menú">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
    <form id="form_eliminar_menu" action="{{ URL::to('menu/menus/eliminar') }}" autocomplete="off" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idSucursal" value="{{ $data['sucursal']->id ?? '-1' }}">
        <input type="hidden" name="producto_menu_eliminar" id="producto_menu_eliminar" value="-1">
    </form>
    <!-- modal modal de agregar menus -->
    <div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ URL::to('menu/menus/agregar') }}" autocomplete="off" method="POST">
                    {{ csrf_field() }}

                    <div class="modal-header">

                        <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;'
                            role="status">
                        </div>
                        <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Productos de menú
                            disponibles</h5>
                        <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                            onclick="$('#mdl_generico').modal('hide');">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="idSucursal" value="{{ $data['sucursal']->id ?? '-1' }}">
                            <div class="col-xl-12 col-sm-12">
                                <label>Producto</label>
                                <select class="form-control" id="prodcuto_menu" name="prodcuto_menu" required>
                                    <option value="-1" selected>Seleccione un producto</option>
                                    @foreach ($data['productos_menu'] as $i)
                                        <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}">
                                            {{ $i->nombre ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xl-12 col-sm-12 mt-3">
                                <label>Seleccione la comanda asignada</label>
                                <select class="form-control" id="comanda_select" name="comanda_select" required>
                                    <option value="-1" selected>Comanda General</option>
                                    @foreach ($data['comandas'] as $i)
                                        <option value="{{ $i->id ?? '' }}" title="{{ $i->nombre ?? '' }}">
                                            {{ $i->nombre ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                        <a href="#" onclick="$('#mdl_generico').modal('hide');"
                            class="btn btn-secondary">Volver</a>
                        <input type="submit" class="btn btn-primary" value="Agregar" />
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar sucursal-->

    <div class="modal fade bs-example-modal-center" id='mdl_cambio_comanda' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0"><i class="fas fa-cog"></i> Cambiar Comanda Asignada</h5>
                    <button type="button" id='btnSalirFact1' class="close" aria-hidden="true"
                        onclick="$('#mdl_cambio_comanda').modal('hide');">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <label id='lblProdCambioComanda'></label>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <label id='lblComdAsigCambioComanda'></label>
                        </div>

                        <div class="col-xl-12 col-sm-12 mt-3">
                            <label>Seleccione la nueva comanda </label>
                            <select class="form-control" id="comanda_cambio_select" name="comanda_cambio_select"
                                required>
                                <option value="-1" selected>Comanda General</option>
                                @foreach ($data['comandas'] as $i)
                                    <option value="{{ $i->id ?? '' }}" title="{{ $i->nombre ?? '' }}">
                                        {{ $i->nombre ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
                <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                    <a href="#" onclick="$('#mdl_cambio_comanda').modal('hide');"
                        class="btn btn-secondary">Volver</a>
                    <input type="button"  onclick="cambiarComanda()" class="btn btn-primary" value="Cambiar Comanda" />
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar sucursal-->

    <!-- Modal de gestión de horarios -->
    <div class="modal fade bs-example-modal-center" id='mdl_horarios' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="spinner-border" id='modal_spinner_horarios' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0"><i class="fas fa-clock"></i> Gestionar Horarios - <span id="lbl_producto_horario"></span></h5>
                    <button type="button" class="close" aria-hidden="true" onclick="$('#mdl_horarios').modal('hide');">×</button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i> Si no se configuran horarios, el producto se mostrará siempre. 
                                Si se configuran horarios, el producto solo se mostrará en los días y horas especificados.
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-success btn-sm" onclick="agregarFilaHorario()">
                                <i class="fas fa-plus"></i> Agregar Horario
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabla_horarios">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Activo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_horarios">
                                <!-- Las filas se agregarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" onclick="$('#mdl_horarios').modal('hide');" class="btn btn-secondary">Cerrar sin guardar</a>
                    <button type="button" onclick="guardarHorarios()" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar y Continuar
                    </button>
                    <button type="button" onclick="guardarHorariosYCerrar()" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar y Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            // Auto-seleccionar primera sucursal si ninguna está seleccionada
            var sucSelect = document.getElementById('sucursal');
            if (sucSelect && sucSelect.value == '-1' && sucSelect.options.length > 1) {
                sucSelect.value = sucSelect.options[1].value;
                document.getElementById('form_cargar_menu').submit();
                return;
            }
            // Obtener la fecha actual en formato deseado
            var currentDate = new Date().toLocaleDateString('es-CR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            var topMessage = 'Reporte de Menús';
            var bottomMessage = 'Reporte de Menús generado el ' + currentDate + '.';

            // Detalle adicional sobre el usuario que realiza la solicitud
            topMessage += '.' + ' Solicitud realizada por ' + "{{ session('usuario')['usuario'] }}" + ' el ' +
                currentDate + '.';
            bottomMessage += ' Desarrollado por Space Software CR. ';

            $('#tablaMenus').DataTable({
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
                        messageTop: topMessage,
                        messageBottom: bottomMessage,
                        filename: 'reporte_menu_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}_' + currentDate
                            .replace(/[\s,:]/g, '_')
                    },
                    {
                        extend: 'pdf',
                        title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                        messageTop: topMessage,
                        messageBottom: bottomMessage,
                        filename: 'reporte_menu_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}_' + currentDate
                            .replace(/[\s,:]/g, '_')
                    },
                    {
                        extend: 'print',
                        title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}',
                        messageTop: topMessage,
                        messageBottom: bottomMessage,
                        filename: 'reporte_menu_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}_' + currentDate
                            .replace(/[\s,:]/g, '_')
                    }
                ]
            });
        }
    </script>
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/productosMenu/menus/editar.js') }}"></script>
@endsection
