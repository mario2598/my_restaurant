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
                        <h4>Inventario productos externos</h4>
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
                        <form id="form_cargar_menu" action="{{ URL::to('productoExterno/inventario/inventarios/filtro') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="row" style="width: 100%">
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Sucursal</label>
                                        <select class="form-control" id="sucursal" name="sucursal"
                                            onchange="cambiarSucursal(this.form)" required>
                                            <option value="-1" selected>Seleccione una sucursal</option>
                                            @foreach ($data['sucursales'] as $i)
                                                <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}"
                                                    @if ($i->id == $data['filtros']['sucursal']) selected @endif>
                                                    {{ $i->descripcion ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Agregar</label>
                                        <button type="button" class="btn btn-secondary btn-icon form-control"
                                            style="cursor: pointer;" onclick="abrirAgregarProducto()"><i
                                                class="fas fa-plus"></i> Agregar producto</button>
                                    </div>
                                </div>


                            </div>
                        </form>
                        <div class="alert alert-info alert-sm py-2 px-3 mb-2" style="border-left:4px solid #17a2b8;background:#f0f9ff;">
                            <i class="fas fa-info-circle"></i>
                            <small>Usa <strong>[-] [+]</strong> para ajuste r&#225;pido de stock &bull; Bot&#243;n <i class="fas fa-sliders-h"></i> para ajuste manual con cantidad personalizada.</small>
                        </div>
                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaInventarios">
                                    <thead>


                                        <tr>
                                            <th class="text-center">Código</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center d-none d-md-table-cell">Categoría</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center d-none d-sm-table-cell">Comanda</th>
                                            <th class="text-center">Ajuste</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">
                                        @foreach ($data['inventarios'] as $i)
                                            @php
                                                $cant_disp = $i->cantidad ?? 0;
                                                $badge_c = $cant_disp <= 0 ? 'danger' : ($cant_disp <= 5 ? 'warning' : 'success');
                                            @endphp
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-secondary" style="font-size:0.85rem;">{{ strtoupper($i->codigo_barra ?? '') }}</span>
                                                </td>
                                                <td class="align-middle"><strong>{{ $i->nombre ?? '' }}</strong></td>
                                                <td class="text-center align-middle d-none d-md-table-cell">
                                                    <span class="badge badge-light border" style="font-size:0.8rem;">{{ $i->categoria ?? '' }}</span>
                                                </td>
                                                <td class="text-center align-middle" onclick="event.stopPropagation()" style="min-width:140px;">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <button class="btn btn-danger btn-sm" style="width:32px;height:32px;padding:0;border-radius:50%;"
                                                            data-pe-id="{{ $i->pe_id }}" data-id="{{ $i->id }}" data-comanda="{{ $i->comanda }}"
                                                            onclick="ajusteRapido(this,'disminuir',1)" title="Quitar 1">
                                                            <i class="fas fa-minus" style="font-size:0.7rem;"></i>
                                                        </button>
                                                        <span class="badge badge-{{ $badge_c }} mx-2" id="cant_{{ $i->id }}" style="font-size:1rem;min-width:38px;padding:6px 10px;">{{ $cant_disp }}</span>
                                                        <button class="btn btn-success btn-sm" style="width:32px;height:32px;padding:0;border-radius:50%;"
                                                            data-pe-id="{{ $i->pe_id }}" data-id="{{ $i->id }}" data-comanda="{{ $i->comanda }}"
                                                            onclick="ajusteRapido(this,'aumentar',1)" title="Agregar 1">
                                                            <i class="fas fa-plus" style="font-size:0.7rem;"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle d-none d-sm-table-cell">
                                                    @if($i->nombreComanda ?? false)
                                                        <span class="badge badge-warning text-dark" style="font-size:0.8rem;">{{ $i->nombreComanda }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle" onclick="event.stopPropagation()">
                                                    <button class="btn btn-primary btn-sm" onclick="abrirAjusteDirecto(this)"
                                                        data-pe-id="{{ $i->pe_id }}" data-id="{{ $i->id }}"
                                                        data-cantidad="{{ $i->cantidad ?? 0 }}" data-comanda="{{ $i->comanda }}"
                                                        data-nombre="{{ $i->nombre }}" title="Ajuste manual">
                                                        <i class="fas fa-sliders-h"></i>
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
        <form id="formEditarProducto" action="{{ URL::to('bodega/producto/editar') }}" style="display: none"
            method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
        </form>
    </div>

    <!-- modal modal de agregar producto -->
    <div class="modal fade bs-example-modal-center" id='mdl_agregar_producto' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <input type="hidden" id="pe_id" name="pe_id" value="-1">
                <input type="hidden" name="sucursal_agregar_id" id="sucursal_agregar_id" value="-1">
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Producto menú</h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        data-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-8">
                            <div class="form-group">
                                <label>Prodcuto</label>
                                <select class="form-control" id="producto_externo" name="producto_externo" required>

                                    @foreach ($data['productos_externos'] as $i)
                                        <option value="{{ $i->id ?? '' }}" title="{{ $i->codigo_barra ?? '' }}">
                                            {{ $i->nombre ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-4" id="contBusdcarPe">
                            <div class="form-group">
                                <label>Busqueda</label>
                                <a class="btn btn-secondary" onclick="abrirProductosExternos()"
                                    style="cursor: pointer">Buscar producto</a>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" class="form-control space_input_modal" id="cantidad_agregar"
                                        name="cantidad_agregar" required max="10000" min="1">

                                </div>
                            </div>
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
                <div id='footerContiner' class="modal-footer">
                    <a href="#" class="btn btn-secondary" data-dismiss="modal">Volver</a>
                    <input type="button" class="btn btn-primary" onclick="mdlAjustarInventario()"
                        id="btn_ajustar_inventario" value="Ajustar Cantidad Inventario" />
                    <input type="button" class="btn btn-primary" onclick="guardarProductoSucursal()" value="Guardar" />

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar producto-->

    <!-- Modal para ajustar cantidad de producto -->
    <div class="modal fade bs-example-modal-center" id="mdl_ajustar_cant_producto" tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <!-- Cambiado a modal-lg para darle un tamaño más grande controlado -->
            <div class="modal-content">
                <div class="modal-header py-2" style="background:#f8f9fa;border-bottom:2px solid #dee2e6;">
                    <div class="d-flex align-items-center" style="flex:1;">
                        <div class="spinner-border spinner-border-sm mr-2" id="modal_spinner" style="display:none;" role="status"></div>
                        <div>
                            <div class="text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Ajuste de inventario</div>
                            <h5 class="modal-title mb-0" id="lbl_ajustar_cant_producto" style="font-size:1rem;font-weight:700;">
                                <i class="fas fa-box-open mr-1 text-primary"></i>
                                <span id="lbl_nombre_producto">—</span>
                            </h5>
                        </div>
                    </div>
                    <button type="button" id="btnSalirFact" class="close ml-2" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body p-3">
                    <div class="row">
                        <!-- Stock actual compacto -->
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-between rounded px-3 py-2" style="background:#f1f3f5;border-left:4px solid #28a745;">
                                <span class="text-muted" style="font-size:0.875rem;"><i class="fas fa-cubes mr-1"></i>Stock actual</span>
                                <span class="font-weight-bold text-dark" style="font-size:1.15rem;" id="lbl_cantidad_actual">—</span>
                            </div>
                        </div>
                        <!-- Comanda con Guardar inline -->
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold text-muted mb-1" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">
                                <i class="fas fa-utensils mr-1"></i>Comanda
                            </label>
                            <div class="input-group">
                                <select class="form-control" id="comanda_ajuste">
                                    <option value="-1">Comanda General</option>
                                    @foreach ($data['comandas'] as $c)
                                        <option value="{{ $c->id ?? '' }}">{{ $c->nombre ?? '' }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" onclick="guardarSoloComanda()" title="Guardar solo la comanda">
                                        <i class="fas fa-save mr-1"></i>Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Cantidad con botones +/- inline -->
                        <div class="col-12">
                            <label class="font-weight-bold text-muted mb-1" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">
                                <i class="fas fa-sort-numeric-up-alt mr-1"></i>Cantidad a Ajustar
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantModal(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                                <input type="number" class="form-control text-center font-weight-bold space_input_modal"
                                    id="cantidad_ajustar" name="cantidad_ajustar" required min="1" value="1"
                                    style="font-size:1.1rem;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantModal(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="footerContiner" class="modal-footer py-2" style="background:#f8f9fa;border-top:2px solid #dee2e6;">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <a href="#" class="btn btn-light" data-dismiss="modal" style="border:1px solid #ced4da;">
                            <i class="fas fa-times mr-1"></i>Cerrar
                        </a>
                        <div class="btn-group">
                            <button class="btn btn-success" onclick="aumentarInventario()" id="btn_aumenta_inventario">
                                <i class="fas fa-plus mr-1"></i>Aumentar
                            </button>
                            <button class="btn btn-danger" onclick="disminuirInventario('N')" id="btn_disminuye_inventario">
                                <i class="fas fa-minus mr-1"></i>Disminuir
                            </button>
                            <button class="btn btn-warning text-dark" onclick="desecharInventario()" id="btn_sacar_desecho">
                                <i class="fas fa-trash mr-1"></i>Desecho
                            </button>
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal - Fin modal de ajustar producto -->


    <!--ayuda prodcutos-->
    <div class="modal fade bs-example-modal-center" id='mdl_ayuda_producto' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Producto menú</h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        data-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <input type="text" name="" id="btn_buscar_producto_ayuda" class="form-control"
                            placeholder="Buscar producto">

                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaProductos">
                            <thead>
                                <tr>
                                    <th class="text-center">Código</th>

                                    <th class="text-center">Producto</th>
                                    <th class="text-center">
                                        Categoría
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody_productos">
                                @foreach ($data['productos_externos'] as $i)
                                    <tr style="cursor: pointer"
                                        onclick='seleccionarProductoAyuda("{{ $i->id }}")'>
                                        <td class="text-center">
                                            {{ strtoupper($i->codigo_barra ?? '') }}
                                        </td>
                                        <td class="text-center">
                                            {{ $i->nombre ?? '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $i->categoria ?? '' }}
                                        </td>
                                        <td class="text-center">
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal ayuda prodcutos-->
    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            var sucursal = $("#sucursal option[value='" + "{{ $data['filtros']['sucursal'] }}" + "']").html();

            var topMesage = 'Reporte de Inventario de productos externos de la sucursal ' + sucursal + '.';
            var bottomMesage = 'Reporte de Inventario de productos externos filtrado por';

            topMesage += ' Solicitud realizada por ' + "{{ session('usuario')['usuario'] }}" + '.';

            if ("{{ $data['filtros']['sucursal'] }}" != '-1') {
                bottomMesage += ' sucursal [ ' + sucursal + ' ],';
            } else {
                bottomMesage += ' sucursal [ Todas ],';
            }


            bottomMesage += ' Desarrollado por Space Software CR. ';


            $('#tablaInventarios').DataTable({
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
                    filename: 'inventario_' + sucursal + '_el_amanecer'
                }, {
                    extend: 'pdf',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'inventario_' + sucursal + '_el_amanecer'
                }, {
                    extend: 'print',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'inventario_' + sucursal + '_el_amanecer'
                }]
            });

            if ($('#sucursal').val() == '-1') {
                var firstSuc = $('#sucursal option:not([value="-1"])').first();
                if (firstSuc.length) {
                    $('#sucursal').val(firstSuc.val());
                    cambiarSucursal(document.getElementById('form_cargar_menu'));
                }
            }
        }
    </script>
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/productoExterno/inventario/inventarios.js') }}"></script>
@endsection
