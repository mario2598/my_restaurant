@extends('layout.master')

@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">

                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Comandas del sistema</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="input_buscar_generico" class="form-control"
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

                                <div class="row" style="width: 100%">

                                    <div class="col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label>Sucursal</label>
                                            <select class="form-control" id="select_sucursal" name="sucursal">
                                                @foreach ($data['sucursales'] as $i)
                                                    <option value="{{ $i->id ?? '' }}"
                                                        {{ old('sucursal') == $i->id ? 'selected' : '' }}
                                                        title="{{ $i->descripcion ?? '' }}">
                                                        {{ $i->descripcion ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-2 col-xl-4">
                                        <div class="form-group">
                                            <label style="color: transparent">Cargar Comandas</label><br>
                                            <input type="button" class="btn btn-primary" onclick="cargarComandas()"
                                                value="Cargar" />
                                        </div>

                                    </div>

                                    <div class="col-sm-12 col-md-2 col-xl-4">
                                        <div class="form-group">
                                            <label>Agregar comanda</label>
                                            <a class="btn btn-success btn-icon form-control"
                                                style="cursor: pointer;color:white;"
                                                onclick="addComandaModal();"><i class="fas fa-plus"></i>
                                                Agregar Nueva
                                            </a>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <div id="contenedor_productos" class="col-sm-12 col-md-12 col-xl-12">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="tablaComandas">
                                        <thead>


                                            <tr>
                                                <th class="text-center">ID</th>

                                                <th class="text-center">
                                                    Nombre Comanda
                                                </th>

                                                <th class="text-center">Acciones</th>

                                            </tr>
                                        </thead>
                                        <tbody id="tbodyComandas">


                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
        </section>

    </div>

    <!-- Modal para crear/editar comanda -->
    <div class="modal fade" id="mdl_gestiona_comanda" tabindex="-1" role="dialog"
        aria-labelledby="mdl_gestiona_comanda_label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdl_gestiona_comanda_label">Agregar/Editar Comanda</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_comanda">Nombre Comanda</label>
                        <input type="text" class="form-control" id="nombre_comanda" name="nombre_comanda" required
                            placeholder="Nombre de la comanda">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="comanda_id" name="comanda_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarComanda()">Guardar</button>
                </div>

            </div>
        </div>
    </div>
@endsection



@section('script')
    <script src="{{ asset('assets/js/comandas/administrar.js') }}"></script>
@endsection
