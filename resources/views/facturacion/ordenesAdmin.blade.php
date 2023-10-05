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
                        <h4>Ordenes / Facturas </h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text"  id="input_buscar_generico" class="form-control"
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
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select class="form-control" id="select_cliente" name="cliente">
                                        <option value="0" selected>Todos</option>
                                        @foreach ($data['clientes'] as $i)
                                            <option value="{{ $i->id ?? -1 }}" title="{{ $i->nombre ?? '' }}">
                                                {{ $i->nombre ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Sucursal</label>
                                    <select class="form-control" id="select_sucursal" name="sucursal">
                                        <option value="T" selected>Todos</option>
                                        @foreach ($data['sucursales'] as $i)
                                            <option value="{{ $i->id ?? '' }}" title="{{ $i->descripcion ?? '' }}">
                                                {{ $i->descripcion ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" class="form-control" id="desde" value="" />

                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date" class="form-control" id="hasta" value="" />
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label>Buscar</label>
                                    <button onclick="filtrar()" class="btn btn-primary btn-icon form-control" style="cursor: pointer;"><i
                                            class="fas fa-search"></i></button>
                                </div>
                            </div>

                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tbl-ordenes" style="max-height: 100%;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" style="text-align: center">No.Orden</th>
                                            <th scope="col" style="text-align: center">Sucursal</th>
                                            <th scope="col" style="text-align: center">Fecha</th>
                                            <th scope="col" style="text-align: center">Cliente</th>
                                            <th scope="col" style="text-align: center">Total pagado</th>
                                            <th scope="col" style="text-align: center">Estado</th>
                                            <th scope="col" style="text-align: center"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-ordenes">

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

    <div class="modal fade bd-example-modal-lg" id='mdl-detallesAnular' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered  modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="" id="input_buscar_generico2" class="form-control"
                                placeholder="Buscar..">
                        </div>
                    </form>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="width: 100%">

                    <table class="table" id="tbl-detallesAnular" style="max-height: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" style="text-align: center">PRODUCTO</th>
                                <th scope="col" style="text-align: center">CANTIDAD</th>
                                <th scope="col" style="text-align: center">Total pagado</th>
                                <th scope="col" style="text-align: center">Devolver a inventario?</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detallesAnular">

                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <a class="btn btn-warning" title="Anular Orden" onclick="anularOrden()"
                            style="color:white;cursor:pointer;">ANULAR</a>
                        <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarMdlAnular()'
                            style="cursor: pointer;">Cerrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

   
@endsection


@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/facturacion/ordenesAdmin.js') }}"></script>
@endsection
