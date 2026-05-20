@extends('layout.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/mobiliario-plano.css') }}">
@endsection

@section('content')
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-primary">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <h4 class="mb-0">Plano de sucursal — mesas</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <a href="{{ url('mobiliario/mesas/admin') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-list"></i> Lista de mesas
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Sucursal</label>
                                <select class="form-control" id="select_sucursal_plano">
                                    @foreach ($data['sucursales'] as $i)
                                        <option value="{{ $i->id ?? '' }}">{{ $i->descripcion ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-primary btn-block" onclick="cargarPlano()">
                                    <i class="fas fa-sync"></i> Cargar
                                </button>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0 w-100">
                                    <label class="d-block small text-muted mb-1">Qué desea ajustar</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-primary active btn-sm">
                                            <input type="radio" name="modo_plano" id="modo_mesas" value="mesas" checked> Mesas
                                        </label>
                                        <label class="btn btn-outline-primary btn-sm">
                                            <input type="radio" name="modo_plano" id="modo_zonas" value="zonas"> Áreas (cocina, baño…)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end justify-content-md-end" id="toolbar-mesas">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block mb-0" onclick="distribuirMesasSinPosicion()">
                                    Auto-ubicar
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end justify-content-md-end d-none" id="toolbar-zonas">
                                <button type="button" class="btn btn-outline-warning btn-sm btn-block mb-0" onclick="restaurarZonasDefault()">
                                    Restaurar áreas
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-success btn-sm btn-block" id="btn-guardar-plano" onclick="guardarCambiosPlano()">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-9">
                                <div id="plano-wrapper" class="plano-wrapper">
                                    <div id="plano-canvas" class="plano-canvas" title="Arrastre las mesas para ubicarlas">
                                        <div id="plano-zonas"></div>
                                        <div id="plano-mesas"></div>
                                    </div>
                                    <div class="plano-leyenda mt-2">
                                        <span class="leyenda-item disponible"><i></i> Disponible</span>
                                        <span class="leyenda-item ocupada"><i></i> Ocupada</span>
                                        <span class="leyenda-item sin-posicion"><i></i> Sin ubicar en plano</span>
                                    </div>
                                </div>
                                <p class="text-muted small mt-2 mb-0" id="plano-ayuda">
                                    <strong>Mesas:</strong> arrastre cada mesa y pulse Guardar.
                                    Referencia: <span id="plano-ref-dimensiones">—</span>.
                                </p>
                            </div>
                            <div class="col-lg-3">
                                <div class="card card-light">
                                    <div class="card-header">
                                        <h6 class="mb-0">Mesas sin posición</h6>
                                    </div>
                                    <div class="card-body p-2" id="lista-mesas-sin-posicion" style="max-height: 200px; overflow-y: auto;">
                                        <p class="text-muted small mb-0">Cargue el plano.</p>
                                    </div>
                                </div>
                                <div class="card card-light mt-2" id="card-panel-mesa">
                                    <div class="card-header">
                                        <h6 class="mb-0">Mesa seleccionada</h6>
                                    </div>
                                    <div class="card-body" id="panel-mesa-detalle">
                                        <p class="text-muted small">Haga clic en una mesa del plano.</p>
                                    </div>
                                </div>
                                <div class="card card-light mt-2 d-none" id="card-panel-zona">
                                    <div class="card-header">
                                        <h6 class="mb-0">Área seleccionada</h6>
                                    </div>
                                    <div class="card-body" id="panel-zona-detalle">
                                        <p class="text-muted small">Seleccione cocina, baño, entrada o jardín en el plano.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/mobiliario/mesas/plano.js') }}"></script>
@endsection
