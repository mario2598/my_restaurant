@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/izitoast/css/iziToast.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')


    <style>
        .trIngreso :hover {
            font-weight: bold;
        }
    </style>


    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Grupos Promoción </h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" id="input_buscar_generico" class="form-control" placeholder="Buscar..">
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
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Agregar</label>
                                    <button onclick="mdlNuevaPromocion()" class="btn btn-primary btn-icon form-control"
                                        style="cursor: pointer;"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>

                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tbl-promos" style="max-height: 100%;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" style="text-align: center">#</th>
                                            <th scope="col" style="text-align: center">Descripción</th>
                                            <th scope="col" style="text-align: center">Precio</th>
                                            <th scope="col" style="text-align: center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-promos" class="trIngreso" style="cursor: pointer">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    </div>
@endsection
@section('popup')
    <div class="modal fade bs-example-modal-center" id='mdlEditarPromo' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Grupo Promoción</h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        onclick="cerrarMdlEditarPromo()">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
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
                                    <label class="form-label">Precio Total</label>
                                    <input type="number" class="form-control " id="precio">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-4">
                            <div class="form-group">
                                <select class="form-control" id="select_categoria" name="select_categoria">
                                    <option value="T" selected>Seleccionar Categoría</option>
                                    @foreach ($data['categorias'] as $i)
                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->categoria ?? '' }}">
                                            {{ $i->categoria ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Estado : </label>
                                    <input type="checkbox" id="activo" checked>
                                    <label for="activo">Activo / Inactivo</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group ">
                                <label>Foto Producto</label>
                                <input type="file"id="foto_producto" name="foto_producto"
                                    accept="image/png, image/jpeg, image/jpg">
                            </div>
                        </div>


                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <button type="button" class="btn btn-info " onclick="guardarPromocion()">Guardar <i
                                            class="fas fa-payment"aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <button type="button" class="btn btn-warning "
                                        onclick="abrirModalAddProdMenu()">Agregar
                                        Producto Menú <i class="fas fa-plus"aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-4">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <button type="button" class="btn btn-success "
                                        onclick="abrirModalAddProdExt()">Agregar
                                        Producto Externo <i class="fas fa-plus"aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <table class="table" id="tbl-detalles" style="max-height: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" style="text-align: center">PRODUCTO</th>
                                <th scope="col" style="text-align: center">CANTIDAD</th>
                                <th scope="col" style="text-align: center">Eliminar</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detalles" class="trIngreso" style="cursor: pointer">

                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary" onclick="cerrarMdlEditarPromo()">Volver</a>


                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal-->

    <div class="modal fade bs-example-modal-center" id='mdl_addDetalle' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Productos de menú
                        disponibles</h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        onclick="cerrarModalAddProdMenu()">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-xl-12 col-sm-12">
                            <select class="form-control" id="prodcuto_menu" name="prodcuto_menu" required>
                                <option value="-1" selected>Seleccione un producto</option>
                                @foreach ($data['productos_menu'] as $i)
                                    <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}">
                                        {{ $i->nombre ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-12 col-sm-4" style="margin-top:10px;">
                            <div class="form-group">
                                <label>Busqueda</label>
                                <a class="btn btn-secondary" onclick="abrirProductosMenuAyuda()"
                                    style="cursor: pointer">Buscar producto</a>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" class="form-control " id="cantidad_mnu">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                    <a href="#" onclick="cerrarModalAddProdMenu()" class="btn btn-secondary">Volver</a>
                    <button type="button" class="btn btn-info " onclick="guardarDetallePromo()">Agregar <i
                            class="fas fa-plus"aria-hidden="true"></i></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar sucursal-->


    <div class="modal fade bs-example-modal-center" id='mdl_addDetalleExterno' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Producto Externo</h5>
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
                        <div class="col-xl-12 col-sm-4">
                            <div class="form-group">
                                <label>Busqueda</label>
                                <a class="btn btn-secondary" onclick="abrirProductosExternosAyuda()"
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

                    </div>

                </div>
                <div id='footerContiner' class="modal-footer">
                    <a href="#" class="btn btn-secondary" data-dismiss="modal">Volver</a>
                    <input type="button" class="btn btn-primary" onclick="guardarDetallePromo()" value="Guardar" />
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar producto-->

    <div class="modal fade bs-example-modal-center" id='mdl_ayuda_producto' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Producto externos</h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                        data-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <input type="text" name="" id="btn_buscar_producto_ext_ayuda" class="form-control"
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
                            <tbody id="tbody_productos_ext">
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

                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal ayuda prodcutos-->


    <div class="modal fade bs-example-modal-center" id='mdl_ayuda_producto_mnu' tabindex="-1" role="dialog"
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
                        <input type="text" name="" id="btn_buscar_producto_mnu_ayuda" class="form-control"
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
                            <tbody id="tbody_productos_mnu">
                                @foreach ($data['productos_menu'] as $i)
                                    <tr style="cursor: pointer"
                                        onclick='seleccionarProductoAyudaMnu("{{ $i->id }}")'>
                                        <td class="text-center">
                                            {{ strtoupper($i->codigo ?? '') }}
                                        </td>
                                        <td class="text-center">
                                            {{ $i->nombre ?? '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $i->categoria ?? '' }}
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal ayuda prodcutos-->
@endsection

@section('script')
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/mant_grupoPomociones.js') }}"></script>
@endsection
