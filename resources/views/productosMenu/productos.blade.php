@extends('layout.master')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
<link rel="stylesheet"
    href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
<style>
    /* Estilos para el modal de extras */
    #mdl-extras .modal-content {
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    #mdl-extras .modal-header {
        border-radius: 12px 12px 0 0;
        padding: 15px 20px;
    }
    
    #mdl-extras .card {
        border-radius: 8px;
        transition: box-shadow 0.3s ease;
    }
    
    #mdl-extras .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    #mdl-extras .card-header {
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
    }
    
    #mdl-extras .form-group label {
        margin-bottom: 5px;
        font-size: 14px;
    }
    
    #mdl-extras .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    #mdl-extras .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    #mdl-extras .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.25);
    }
    
    #mdl-extras #tbl-inv {
        font-size: 13px;
    }
    
    #mdl-extras #tbl-inv thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    #mdl-extras #tbl-inv tbody tr {
        transition: background-color 0.2s ease;
    }
    
    #mdl-extras #tbl-inv tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    #mdl-extras .badge {
        font-size: 0.85em;
        padding: 5px 10px;
    }
    
    #mdl-extras .input-group-text {
        background-color: #e9ecef;
        font-weight: 600;
    }
    
    #mdl-extras .btn-lg {
        padding: 12px 24px;
        font-size: 16px;
    }
    
    /* Responsive para móviles */
    @media (max-width: 768px) {
        #mdl-extras .modal-dialog {
            margin: 10px;
        }
        
        #mdl-extras #tbl-inv {
            font-size: 11px;
        }
        
        #mdl-extras #tbl-inv th,
        #mdl-extras #tbl-inv td {
            padding: 8px 4px;
        }
    }
    
    /* Animación para el mensaje vacío */
    #empty-extras-message {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Estilos para secciones colapsables */
    .collapsible-section {
        transition: all 0.3s ease;
    }
    
    .collapsible-header {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
    }
    
    .collapsible-header:hover {
        background-color: rgba(0, 123, 255, 0.1) !important;
    }
    
    .collapsible-header.collapsed .collapse-icon {
        transform: rotate(-90deg);
    }
    
    .collapse-icon {
        transition: transform 0.3s ease;
        display: inline-block;
    }
    
    .collapsible-content {
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.3s ease, padding 0.3s ease;
        max-height: 5000px;
    }
    
    .collapsible-content.collapsed {
        max-height: 0 !important;
        opacity: 0;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        display: none !important;
    }
    
    .collapsible-content.expanded {
        display: block !important;
    }
    
    .collapsible-content.expanded {
        opacity: 1;
    }
</style>
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
                                            {{ $i->categoria ?? '' }}
                                        </option>
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
                                            {{ $i->descripcion ?? '' }}
                                        </option>
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
                                            {{ $g->codigo ?? '' }}
                                        </td>
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

                                            <a class="btn btn-info btn-icon" title="Configuración de Facturación Electrónica"
                                                onclick='clickConfigFE("{{ $g->id }}")'
                                                style="cursor: pointer;"><i class="fas fa-file-invoice"></i></a>

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
                                    {{ $i->nombre ?? '' }} - {{ $i->unidad_medida ?? '' }}
                                </option>
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


<div class="modal fade bd-example-modal-lg" id='mdl-extras' tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Extras del producto
                </h5>
                <button type="button" class="close text-white" onclick="cerrarExtras()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body" style="padding: 20px;">
                <!-- Sección de extras genéricos -->
                <div class="card border-success mb-3 collapsible-section">
                    <div class="card-header bg-light collapsible-header collapsed" onclick="toggleCollapsible('extras-genericos-section')">
                        <h6 class="mb-0">
                            <i class="fas fa-layer-group"></i> Cargar extras genéricos
                            <span class="collapse-icon float-right mr-2" style="font-size: 0.8em;">
                                <i class="fas fa-chevron-up"></i>
                            </span>
                            <button type="button" class="btn btn-sm btn-success float-right mr-2" onclick="event.stopPropagation(); cargarExtrasGenericosDisponibles()">
                                <i class="fas fa-sync-alt"></i> Cargar
                            </button>
                        </h6>
                    </div>
                    <div class="card-body collapsible-content collapsed" id="extras-genericos-section" style="display: none;">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-mouse-pointer"></i> Seleccione extras genéricos para agregar al producto:
                            </label>
                            <small class="form-text text-muted d-block mb-2">
                                <i class="fas fa-info-circle"></i> Use los botones para seleccionar por grupo, individualmente o todos a la vez
                            </small>
                            <div id="contenedor-extras-genericos" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; background-color: #ffffff;">
                                <p class="text-muted text-center">Haga clic en "Cargar" para ver los extras genéricos disponibles</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Formulario de nuevo extra -->
                <div class="card border-primary mb-3 collapsible-section">
                    <div class="card-header bg-light collapsible-header collapsed" onclick="toggleCollapsible('formulario-extra-section')">
                        <h6 class="mb-0">
                            <i class="fas fa-edit"></i> Agregar nuevo extra
                            <span class="collapse-icon float-right" style="font-size: 0.8em;">
                                <i class="fas fa-chevron-up"></i>
                            </span>
                        </h6>
                    </div>
                    <div class="card-body collapsible-content collapsed" id="formulario-extra-section" style="display: none;">
                        <div class="row">
                            <!-- Información básica -->
                            <div class="col-12 mb-3">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-info-circle"></i> Información básica
                                </h6>
                            </div>
                            
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Descripción del extra <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="ipt_dsc_ext" name="ipt_dsc_ext"
                                        value="" required placeholder="Ej: Queso extra, Tocineta, etc.">
                                    <small class="form-text text-muted">Nombre que verá el cliente</small>
                                </div>
                            </div>
                            
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Grupo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="ipt_dsc_gru_ext" name="ipt_dsc_gru_ext"
                                        value="" required placeholder="Ej: Agregados, Salsas, etc.">
                                    <small class="form-text text-muted">Agrupa extras relacionados</small>
                                </div>
                            </div>
                            
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-dollar-sign"></i> Precio (CRC) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₡</span>
                                        </div>
                                        <input type="number" class="form-control" id="ipt_precio_ext" name="ipt_precio_ext"
                                            value="" required step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <small class="form-text text-muted">Precio adicional por este extra</small>
                                    <input type="hidden" id="ipt_id_prod_ext" name="ipt_id_prod_ext" value="-1">
                                </div>
                            </div>
                            
                            <!-- Materia Prima -->
                            <div class="col-12 mt-3 mb-3">
                                <h6 class="text-info border-bottom pb-2">
                                    <i class="fas fa-box"></i> Materia prima (opcional)
                                </h6>
                            </div>
                            
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-cube"></i> Materia Prima
                                    </label>
                                    <select class="form-control select2" id="select_prod_mp_extra" style="width: 100%"
                                        name="select_prod_mp_extra">
                                        <option value="" title="Sin materia prima asignada">
                                            Sin asignar
                                        </option>
                                        @foreach ($data['materia_prima'] as $i)
                                        <option value="{{ $i->id ?? -1 }}" title="{{ $i->unidad_medida ?? '' }}">
                                            {{ $i->nombre ?? '' }} - {{ $i->unidad_medida ?? '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Materia prima que consume este extra</small>
                                </div>
                            </div>
                            
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Cantidad requerida
                                        <span class="text-danger" id="label-cantidad-required" style="display: none;">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="ipt_cantidad_req_extra" name="ipt_cantidad_req_extra"
                                        value="" step="0.01" min="0" placeholder="0.00">
                                    <small class="form-text text-muted">Cantidad de materia prima necesaria (requerido si selecciona materia prima)</small>
                                    <input type="hidden" id="ipt_id_prod_mp" name="ipt_id_prod_mp" value="-1">
                                </div>
                            </div>
                            
                            <!-- Opciones -->
                            <div class="col-12 mt-3 mb-3">
                                <h6 class="text-success border-bottom pb-2">
                                    <i class="fas fa-cog"></i> Opciones de configuración
                                </h6>
                            </div>
                            
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="requisito">
                                        <label class="custom-control-label font-weight-bold" for="requisito">
                                            <i class="fas fa-exclamation-circle text-warning"></i> Es requerido
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">El cliente debe seleccionar al menos uno de este grupo</small>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="multiple">
                                        <label class="custom-control-label font-weight-bold" for="multiple">
                                            <i class="fas fa-check-double text-info"></i> Permite selección múltiple
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Permite seleccionar varios extras del mismo grupo</small>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group d-flex align-items-end h-100">
                                    <div class="w-100">
                                        <button type="button" class="btn btn-primary btn-lg btn-block" 
                                            onclick="agregarExtraProducto()" title="Guardar Extra">
                                            <i class="fas fa-save"></i> Guardar Extra
                                        </button>
                                        <div class="btn-group btn-block mt-2" role="group">
                                            <button type="button" class="btn btn-warning" 
                                                onclick="limpiarExtraProd()" title="Limpiar formulario">
                                                <i class="fas fa-eraser"></i> Limpiar
                                            </button>
                                            <button type="button" class="btn btn-secondary" 
                                                onclick="cerrarExtras()" title="Cerrar">
                                                <i class="fas fa-times"></i> Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de extras existentes -->
                <div class="card border-info collapsible-section">
                    <div class="card-header bg-light collapsible-header collapsed" onclick="toggleCollapsible('lista-extras-section')">
                        <h6 class="mb-0">
                            <i class="fas fa-list"></i> Extras registrados 
                            <span class="badge badge-primary" id="badge-count-extras">0</span>
                            <span class="collapse-icon float-right" style="font-size: 0.8em;">
                                <i class="fas fa-chevron-up"></i>
                            </span>
                        </h6>
                    </div>
                    <div class="card-body collapsible-content collapsed" id="lista-extras-section" style="padding: 10px; display: none;">
                        <div style="max-height: 50vh; overflow-y: auto;">
                            <table class="table table-hover table-sm" id="tbl-inv">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th scope="col" style="min-width: 150px;">
                                            <i class="fas fa-tag"></i> Descripción
                                        </th>
                                        <th scope="col" style="text-align: center; min-width: 100px;">
                                            <i class="fas fa-dollar-sign"></i> Precio
                                        </th>
                                        <th scope="col" style="text-align: center; min-width: 120px;">
                                            <i class="fas fa-layer-group"></i> Grupo
                                        </th>
                                        <th scope="col" style="text-align: center; min-width: 150px;">
                                            <i class="fas fa-cube"></i> Materia Prima
                                        </th>
                                        <th scope="col" style="text-align: center; min-width: 100px;">
                                            <i class="fas fa-balance-scale"></i> Cantidad
                                        </th>
                                        <th scope="col" style="text-align: center; min-width: 80px;">
                                            <i class="fas fa-exclamation-circle"></i> Requerido
                                        </th>
                                        <th scope="col" style="text-align: center; min-width: 80px;">
                                            <i class="fas fa-check-double"></i> Múltiple
                                        </th>
                                        <th scope="col" style="text-align: center; min-width: 100px;">
                                            <i class="fas fa-cogs"></i> Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-ext">
                                    <!-- Los extras se cargarán aquí dinámicamente -->
                                </tbody>
                            </table>
                            <div id="empty-extras-message" class="text-center text-muted py-4" style="display: none;">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No hay extras registrados aún</p>
                                <small>Agregue un extra usando el formulario de arriba</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Configuración de Facturación Electrónica -->
<div class="modal fade bs-example-modal-center" id='mdl-config-fe' tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="width: 100%">
                <div class="row" style="width: 100%">
                    <div class="col-sm-12 col-md-12 col-xl-12">
                        <h5 class="modal-title">Configuración de Facturación Electrónica</h5>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <!-- Campo oculto para el ID del producto -->
                <input type="hidden" id="id_producto_fe" value="">

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-xl-12">
                        <div class="form-group">
                            <label>Código CABYS</label>
                            <input type="text" class="form-control" id="codigo_cabys"
                                placeholder="Código de clasificación arancelaria" maxlength="20">
                            <small class="form-text text-muted">Código según catálogo del MEIC</small>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-xl-6">
                        <div class="form-group">
                            <label>Tarifa de Impuesto (%)</label>
                            <input type="number" class="form-control" id="tarifa_impuesto"
                                placeholder="13.00" step="0.01" min="0" max="100" disabled>
                            <small class="form-text text-muted">Porcentaje de IVA aplicable</small>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-xl-6">
                        <div class="form-group">
                            <label>Unidad de Medida</label>
                            <select class="form-control" id="unidad_medida_fe">
                                <option value="">Cargando unidades...</option>
                            </select>
                            <small class="form-text text-muted">Las unidades se cargan desde FactuX</small>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-xl-6">
                        <div class="form-group">
                            <label>Tipo de Código</label>
                            <select class="form-control" id="tipo_codigo">
                                <option value="">Seleccione tipo</option>
                                <option value="01">Producto</option>
                                <option value="02">Servicio</option>
                                <option value="03">Producto y Servicio</option>
                                <option value="04">Otro</option>
                            </select>
                        </div>
                    </div>

                </div>
                <!--
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>¿Es Exento de IVA?</label>
                                <select class="form-control" id="exento">
                                    <option value="">Seleccione</option>
                                    <option value="S">Sí</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>¿IVA Incluido en Precio?</label>
                                <select class="form-control" id="impuesto_incluido">
                                    <option value="">Seleccione</option>
                                    <option value="S">Sí</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    -->
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-xl-12">
                        <div class="form-group">
                            <label>Descripción para Facturación</label>
                            <textarea class="form-control" id="descripcion_fe" rows="3"
                                placeholder="Descripción detallada para la facturación electrónica" disabled></textarea>
                            <small class="form-text text-muted">Descripción que aparecerá en la factura</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row" style="width: 100%">
                    <div class="col-sm-12 col-md-12 col-xl-12 text-right">
                        <a class="btn btn-primary btn-icon" title="Guardar" onclick='guardarConfigFE()' style="color:white;"    
                            style="cursor: pointer;">Guardar</a>
                        <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarConfigFE()'
                            style="cursor: pointer;">Cerrar</a>
                    </div>
                </div>
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

        bottomMesage += ' {{ env('
        APP_NAME ', '
        SPACE SOFTWARE CR ') }} ';


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
                title: '{{ env('
                APP_NAME ', '
                SPACE SOFTWARE CR ') }}',
                messageTop: topMesage,
                messageBottom: bottomMesage,
                filename: 'reporte_productos'
            }, {
                extend: 'pdf',
                title: '{{ env('
                APP_NAME ', '
                SPACE SOFTWARE CR ') }}',
                messageTop: topMesage,
                messageBottom: bottomMesage,
                filename: 'reporte_productos'
            }, {
                extend: 'print',
                title: '{{ env('
                APP_NAME ', '
                SPACE SOFTWARE CR ') }}',
                messageTop: topMesage,
                messageBottom: bottomMesage,
                filename: 'reporte_productos'
            }]
        });

    }
</script>
@endsection



@section('script')
<script>
    // Validación dinámica para cantidad de materia prima
    $(document).ready(function() {
        $('#select_prod_mp_extra').on('change', function() {
            var materiaPrimaSeleccionada = $(this).val();
            var campoCantidad = $('#ipt_cantidad_req_extra');
            var labelRequired = $('#label-cantidad-required');
            
            if (materiaPrimaSeleccionada && materiaPrimaSeleccionada !== '') {
                campoCantidad.prop('required', true);
                labelRequired.show();
                campoCantidad.focus();
            } else {
                campoCantidad.prop('required', false);
                labelRequired.hide();
                campoCantidad.val('');
            }
        });
        
        // Inicializar Select2 si está disponible
        if (typeof $.fn.select2 !== 'undefined') {
            $('#select_prod_mp_extra').select2({
                placeholder: 'Seleccione una materia prima',
                allowClear: true,
                width: '100%'
            });
        }
        
    });
    
    // Funciones auxiliares para expandir/colapsar secciones (disponibles globalmente)
    function expandirSeccion(sectionId) {
        var $section = $('#' + sectionId);
        var $card = $section.closest('.collapsible-section');
        var $header = $card.find('.collapsible-header');
        var $icon = $header.find('.collapse-icon i');
        
        if ($section.hasClass('collapsed')) {
            $section.removeClass('collapsed').addClass('expanded');
            $section.slideDown(300);
            $header.removeClass('collapsed');
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    }
    
    function colapsarSeccion(sectionId) {
        var $section = $('#' + sectionId);
        var $card = $section.closest('.collapsible-section');
        var $header = $card.find('.collapsible-header');
        var $icon = $header.find('.collapse-icon i');
        
        if (!$section.hasClass('collapsed')) {
            $section.slideUp(300, function() {
                $section.addClass('collapsed').removeClass('expanded');
            });
            $header.addClass('collapsed');
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    }
    
    // Función para colapsar/expandir secciones
    function toggleCollapsible(sectionId) {
        var $section = $('#' + sectionId);
        
        if ($section.hasClass('collapsed')) {
            expandirSeccion(sectionId);
        } else {
            colapsarSeccion(sectionId);
        }
    }
</script>

<script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/page/datatables.js') }}"></script>
<script src="{{ asset('assets/js/restaurante/productos.js') }}"></script>
@endsection