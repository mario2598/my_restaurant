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
                        <h4>Inventario productos materia prima</h4>
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
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Sucursal</label>
                                    <select class="form-control" id="sucursal" onchange="cargarMateriPrimaInvSucursal()"
                                        name="sucursal" required>
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
                                    <label>Buscar</label>
                                    <button type="button" class="btn btn-primary btn-icon form-control"
                                        onclick="buscarInv()" style="cursor: pointer;"><i
                                            class="fas fa-search"></i></button>
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

                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaInventariosMp">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">
                                                Cantidad
                                            </th>
                                            <th class="text-center">
                                                Unidad Medida
                                            </th>

                                            <th class="text-center">
                                                Proveedor
                                            </th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_genericoMp">

                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

    </div>
@endsection

@section('popup')
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
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Producto inventario
                    </h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        data-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div id="contInfoProd0" class="col-xl-12 col-sm-8">
                            <div class="form-group">
                                <label>Prodcuto</label>
                                <h5 id="txtNombreProducto"> </h5>
                            </div>
                        </div>
                        <div id="contInfoProd1" class="col-xl-12 col-sm-8">
                            <div class="form-group">
                                <label>Prodcuto</label>
                                <select class="form-control" id="producto_externo" name="producto_externo" required>
                                </select>
                            </div>
                        </div>
                        <div id="contInfoProd2" class="col-xl-12 col-sm-4">
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
                                        name="cantidad_agregar" required>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                    <a href="#" class="btn btn-secondary" data-dismiss="modal">Volver</a>
                    <input type="button" class="btn btn-primary" onclick="mdlAjustarInventario()"
                        id="btn_ajustar_inventario" value="Ajustar Cantidad Inventario" />
                    <input type="button" class="btn btn-primary" onclick="crearProductoSucursal()"
                        id="btn_add_inventario" value="Agregar al inventario" />

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
                <div class="modal-header">
                    <div class="spinner-border" id="modal_spinner" style="margin-right: 3%; display: none;"
                        role="status"></div>
                    <h5 class="modal-title mt-0" id="lbl_ajustar_cant_producto"><i class="fas fa-cog"></i>
                        Aumentar/Disminuir Cantidad Inventario</h5>
                    <button type="button" id="btnSalirFact" class="close" aria-hidden="true"
                        data-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group form-float">
                                <label for="cantidad_ajustar" class="form-label">Cantidad a Ajustar</label>
                                <input type="number" class="form-control space_input_modal" id="cantidad_ajustar"
                                    name="cantidad_ajustar" required min="1">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="footerContiner" class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <!-- Clase para que los botones estén distribuidos de forma uniforme -->
                        <a href="#" class="btn btn-secondary" data-dismiss="modal">Volver</a>
                        <input type="button" class="btn btn-primary" onclick="aumentarInventario()"
                            id="btn_aumenta_inventario" value="Aumentar Inventario" />
                        <input type="button" class="btn btn-primary" onclick="disminuirInventario()"
                            id="btn_disminuye_inventario" value="Disminuir Inventario" />
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal - Fin modal de ajustar producto -->
@endsection

@section('script')
    <script>
        function initTabla() {
            if ($.fn.DataTable.isDataTable('#tablaInventariosMp')) {
                $('#tablaInventariosMp').DataTable().destroy();
            }

            // Obtener el nombre de la sucursal o asignar "Todas las sucursales" si no está definido
            var sucursal = $("#sucursal option:selected").html();
            sucursal = sucursal ? sucursal : "Sin asignar sucursal";

            // Mensajes para el reporte
            var topMesage = 'Reporte de Inventario de Materia Prima - Sucursal: ' + sucursal + '.';
            var bottomMesage = 'Este reporte de inventario ha sido generado por Space Software CR.';

            // Añadir información adicional de usuario
            topMesage += ' Solicitud realizada por el usuario: ' + "{{ session('usuario')['usuario'] }}" + '.';

            // Añadir detalles de filtros en el mensaje inferior
            if ("{{ $data['filtros']['sucursal'] }}" != '-1') {
                bottomMesage += ' Filtrado por sucursal: [' + sucursal + '].';
            } else {
                bottomMesage += ' Filtrado por todas las sucursales.';
            }

            // Configuración de la tabla con DataTables
            $('#tablaInventariosMp').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                'fixedHeader': {
                    'header': true,
                    'footer': true
                },
                buttons: [{
                    extend: 'excel',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }} - Inventario',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'Reporte_Inventario_MT_' +
                        '_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }, {
                    extend: 'pdf',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }} - Inventario',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'Reporte_Inventario_MT_' +
                        '_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }, {
                    extend: 'print',
                    title: '{{ env('APP_NAME', 'SPACE SOFTWARE CR') }} - Inventario',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'Reporte_Inventario_MT_' +
                        '_{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}'
                }]
            });
        }
    </script>


    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/materiaPrima/inventario/inventarios.js') }}"></script>
@endsection
