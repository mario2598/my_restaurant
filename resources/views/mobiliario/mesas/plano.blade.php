@extends('layout.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/mesa-plano-visual.css') }}">
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
                                            <input type="radio" name="modo_plano" id="modo_zonas" value="zonas"> Áreas del local
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

                        <div id="plano-tabs-pisos" class="mb-2 d-flex flex-wrap align-items-center gap-1" style="display:none!important"></div>

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
                                        <span class="leyenda-item sin-posicion"><i></i> Sin ubicar</span>
                                        <span class="leyenda-item leyenda-forma-redonda"><i style="border-radius:50%"></i> Redonda</span>
                                        <span class="leyenda-item leyenda-forma-cuadrada"><i></i> Cuadrada</span>
                                        <span class="leyenda-item"><i style="width:18px;border-radius:3px"></i> Rectangular</span>
                                    </div>
                                </div>
                                <p class="text-muted small mt-2 mb-0" id="plano-ayuda">
                                    <strong>Mesas:</strong> arrastre cada mesa y pulse Guardar.
                                    Referencia: <span id="plano-ref-dimensiones">—</span>.
                                </p>
                                <div class="mt-2 d-flex flex-wrap align-items-center" id="canvas-resize-controls" style="gap:4px">
                                    <small class="text-muted mr-1"><i class="fas fa-expand-arrows-alt"></i></small>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size:11px" onclick="cambiarTamanoCanvas(100,100)">Cuadrado</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size:11px" onclick="cambiarTamanoCanvas(100,150)">Vertical</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size:11px" onclick="cambiarTamanoCanvas(150,100)">Horizontal</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size:11px" onclick="cambiarTamanoCanvas(200,100)">Panorámico</button>
                                    <div class="d-flex align-items-center" style="gap:2px">
                                        <input type="number" id="canvas_ancho" class="form-control form-control-sm py-0" style="width:58px;height:24px" placeholder="W" min="50" max="500">
                                        <span class="text-muted small">x</span>
                                        <input type="number" id="canvas_alto" class="form-control form-control-sm py-0" style="width:58px;height:24px" placeholder="H" min="50" max="500">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1" style="font-size:11px;height:24px" onclick="aplicarTamanoCanvas()">OK</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card card-light mb-2">
                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0">Nueva mesa</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary px-2" onclick="toggleNuevaMesaForm()" title="Agregar mesa">
                                            <i class="fas fa-plus" id="icon-nueva-mesa"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-2 d-none" id="form-nueva-mesa">
                                        <div class="form-group mb-1">
                                            <label class="small mb-0">Nro. de mesa</label>
                                            <input type="text" class="form-control form-control-sm" id="nueva_mesa_numero" placeholder="Ej. 10">
                                        </div>
                                        <div class="form-row mb-1">
                                            <div class="col">
                                                <label class="small mb-0">Capacidad</label>
                                                <input type="number" class="form-control form-control-sm" id="nueva_mesa_capacidad" value="4" min="1" max="50">
                                            </div>
                                            <div class="col">
                                                <label class="small mb-0">Forma</label>
                                                <select class="form-control form-control-sm" id="nueva_mesa_forma">
                                                    <option value="rectangular">Rectangular</option>
                                                    <option value="cuadrada">Cuadrada</option>
                                                    <option value="redonda">Redonda</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="nueva_mesa_impuesto" checked>
                                            <label class="custom-control-label small" for="nueva_mesa_impuesto">Aplica 10% servicio</label>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="agregarMesaRapida()">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>
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
                                        <p class="text-muted small">Seleccione un área en el plano.</p>
                                    </div>
                                </div>
                                <div class="card card-light mt-2 d-none" id="card-config-areas">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Configurar áreas</h6>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="abrirFormNuevaArea()" title="Nueva área">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-2">
                                        <p class="text-muted small mb-2">Defina las zonas de su local (salón, terraza, cocina…). Luego ubíquelas en el plano.</p>
                                        <div id="lista-areas-config" style="max-height: 220px; overflow-y: auto;">
                                            <p class="text-muted small mb-0">Cargue el plano.</p>
                                        </div>
                                        <div id="form-area-config" class="border rounded p-2 mt-2 d-none bg-white">
                                            <input type="hidden" id="area_config_id" value="">
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Nombre</label>
                                                <input type="text" class="form-control form-control-sm" id="area_config_nombre" maxlength="80" placeholder="Ej. Terraza VIP">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Código (opcional)</label>
                                                <input type="text" class="form-control form-control-sm" id="area_config_codigo" maxlength="40" placeholder="terraza_vip">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Color</label>
                                                <input type="color" class="form-control form-control-sm" id="area_config_color" value="#e9ecef">
                                            </div>
                                            <div class="form-group mb-2" id="area_config_piso_row" style="display:none">
                                                <label class="small mb-0">Pertenece a área</label>
                                                <select class="form-control form-control-sm" id="area_config_piso"></select>
                                            </div>
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" class="custom-control-input" id="area_config_colocar" checked>
                                                <label class="custom-control-label small" for="area_config_colocar">Colocar en el plano al guardar</label>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm btn-block" onclick="guardarAreaConfig()">Guardar área</button>
                                            <button type="button" class="btn btn-link btn-sm btn-block" onclick="cerrarFormAreaConfig()">Cancelar</button>
                                        </div>
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
    <script src="{{ asset('assets/js/mobiliario/mesa-plano-utils.js') }}"></script>
    <script src="{{ asset('assets/js/mobiliario/mesas/plano.js') }}"></script>
@endsection
