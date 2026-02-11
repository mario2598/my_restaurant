@extends('layout.master')

@section('style')
<link rel="stylesheet" href="{{asset("assets/bundles/datatables/datatables.min.css")}}">
<link rel="stylesheet" href="{{asset("assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css")}}">
<style>
    /* Estilos para la página de extras genéricos */
    .card {
        border-radius: 8px;
        transition: box-shadow 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .card-header {
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
    }
    
    .form-group label {
        margin-bottom: 5px;
        font-size: 14px;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.25);
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
    }
    
    #empty-extras-message {
        padding: 3rem 1rem;
    }
</style>
@endsection

@section('content')
@include('layout.sidebar')

<div class="main-content">
    <section class="section">
        <div class="section-body">
            
            <div class="card card-primary">
                <div class="card-header">
                    <h4><i class="fas fa-layer-group"></i> Extras Genéricos</h4>
                    <div class="card-header-action">
                        <button type="button" class="btn btn-success" onclick="limpiarFormulario()">
                            <i class="fas fa-plus"></i> Nuevo Extra
                        </button>
                    </div>
                </div>
                
                <div class="card-body" style="padding: 20px;">
                    <!-- Formulario de nuevo extra -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-edit"></i> 
                                <span id="form-title">Agregar nuevo extra genérico</span>
                            </h6>
                        </div>
                        <div class="card-body">
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
                                        <div class="input-group">
                                            <select class="form-control" id="select_grupo_ext" name="select_grupo_ext" style="display: none;">
                                                <option value="">Seleccione o escriba un grupo</option>
                                            </select>
                                            <input type="text" class="form-control" id="ipt_dsc_gru_ext" name="ipt_dsc_gru_ext"
                                                value="" required placeholder="Ej: Agregados, Salsas, etc." 
                                                list="lista-grupos-extras" autocomplete="off">
                                            <datalist id="lista-grupos-extras">
                                                <!-- Se llenará dinámicamente con grupos existentes -->
                                            </datalist>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="cargarGruposExtras()" title="Recargar grupos">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Seleccione un grupo existente o escriba uno nuevo. Los grupos se agrupan automáticamente.
                                        </small>
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
                                        <input type="hidden" id="ipt_id_ext_generico" name="ipt_id_ext_generico" value="-1">
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
                                                onclick="guardarExtraGenerico()" title="Guardar Extra">
                                                <i class="fas fa-save"></i> Guardar Extra
                                            </button>
                                            <div class="btn-group btn-block mt-2" role="group">
                                                <button type="button" class="btn btn-warning" 
                                                    onclick="limpiarFormulario()" title="Limpiar formulario">
                                                    <i class="fas fa-eraser"></i> Limpiar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de extras existentes -->
                    <div class="card border-info">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-list"></i> Extras genéricos registrados 
                                <span class="badge badge-primary" id="badge-count-extras">0</span>
                            </h6>
                        </div>
                        <div class="card-body" style="padding: 10px;">
                            <div style="max-height: 60vh; overflow-y: auto;">
                                <table class="table table-hover table-sm" id="tbl-extras-genericos">
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
                                    <tbody id="tbody-extras-genericos">
                                        <!-- Los extras se cargarán aquí dinámicamente -->
                                    </tbody>
                                </table>
                                <div id="empty-extras-message" class="text-center text-muted py-4" style="display: none;">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No hay extras genéricos registrados aún</p>
                                    <small>Agregue un extra usando el formulario de arriba</small>
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
<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var base_path = "{{ url('/') }}";

    $(document).ready(function() {
        // Inicializar Select2 si está disponible
        if (typeof $.fn.select2 !== 'undefined') {
            $('#select_prod_mp_extra').select2({
                placeholder: 'Seleccione una materia prima',
                allowClear: true
            });
        }

        // Validar cantidad cuando se selecciona materia prima
        $('#select_prod_mp_extra').on('change', function() {
            var materiaPrimaSeleccionada = $(this).val();
            if (materiaPrimaSeleccionada && materiaPrimaSeleccionada !== '') {
                $('#ipt_cantidad_req_extra').prop('required', true);
                $('#label-cantidad-required').show();
            } else {
                $('#ipt_cantidad_req_extra').prop('required', false);
                $('#label-cantidad-required').hide();
                $('#ipt_cantidad_req_extra').val('');
            }
        });

        // Normalizar el grupo cuando el usuario termine de escribir
        $('#ipt_dsc_gru_ext').on('blur', function() {
            var valor = $(this).val();
            if (valor && valor.trim() !== '') {
                // Normalizar: trim y capitalizar primera letra de cada palabra
                var normalizado = valor.trim();
                normalizado = normalizado.replace(/\b\w/g, function(char) {
                    return char.toUpperCase();
                });
                $(this).val(normalizado);
            }
        });

        // Cargar grupos de extras al iniciar
        cargarGruposExtras();
        
        // Cargar extras al iniciar
        cargarExtrasGenericos();
    });

    function cargarGruposExtras() {
        $.ajax({
            url: base_path + '/materiaPrima/extras-generico/grupos',
            type: 'get',
            data: {
                _token: CSRF_TOKEN
            }
        }).done(function(respuesta) {
            if (respuesta.estado && respuesta.datos) {
                var datalist = $('#lista-grupos-extras');
                datalist.html('');
                
                respuesta.datos.forEach(function(grupo) {
                    if (grupo && grupo.trim() !== '') {
                        datalist.append('<option value="' + grupo + '">');
                    }
                });
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            // Silenciar error, no es crítico
            console.log("Error al cargar grupos:", textStatus);
        });
    }

    function cargarExtrasGenericos() {
        $.ajax({
            url: base_path + '/materiaPrima/extras-generico/cargar',
            type: 'get',
            data: {
                _token: CSRF_TOKEN
            }
        }).done(function(respuesta) {
            if (!respuesta.estado) {
                showError(respuesta.mensaje);
                return;
            }

            $("#tbody-extras-genericos").html("");
            
            // Actualizar contador de extras
            var cantidadExtras = respuesta.datos ? respuesta.datos.length : 0;
            $("#badge-count-extras").text(cantidadExtras);
            
            // Mostrar/ocultar mensaje de vacío
            if (cantidadExtras === 0) {
                $("#empty-extras-message").show();
                $("#tbl-extras-genericos").hide();
            } else {
                $("#empty-extras-message").hide();
                $("#tbl-extras-genericos").show();
                
                // Agrupar extras por: grupo + es_requerido + multiple
                var gruposAgrupados = {};
                respuesta.datos.forEach(function(extra) {
                    var claveGrupo = (extra.dsc_grupo || 'Sin grupo') + '_' + 
                                    (extra.es_requerido || 0) + '_' + 
                                    (extra.multiple || 0);
                    
                    if (!gruposAgrupados[claveGrupo]) {
                        gruposAgrupados[claveGrupo] = {
                            grupo: extra.dsc_grupo || 'Sin grupo',
                            es_requerido: extra.es_requerido || 0,
                            multiple: extra.multiple || 0,
                            extras: []
                        };
                    }
                    gruposAgrupados[claveGrupo].extras.push(extra);
                });
                
                // Ordenar grupos y mostrar
                Object.keys(gruposAgrupados).sort().forEach(function(clave) {
                    var grupo = gruposAgrupados[clave];
                    
                    // Agregar encabezado de grupo si hay más de un extra en el grupo
                    if (grupo.extras.length > 1) {
                        var textoGrupo = "<tr class='table-info'><td colspan='8' style='font-weight: bold; background-color: #d1ecf1;'>";
                        textoGrupo += "<i class='fas fa-layer-group'></i> <strong>" + grupo.grupo + "</strong> - ";
                        textoGrupo += "Requerido: " + (grupo.es_requerido == 1 ? '<span class="badge badge-warning">Sí</span>' : '<span class="badge badge-secondary">No</span>') + " - ";
                        textoGrupo += "Múltiple: " + (grupo.multiple == 1 ? '<span class="badge badge-primary">Sí</span>' : '<span class="badge badge-secondary">No</span>');
                        textoGrupo += "</td></tr>";
                        $("#tbody-extras-genericos").append(textoGrupo);
                    }
                    
                    // Agregar extras del grupo
                    grupo.extras.forEach(function(extra) {
                        crearFilaExtra(extra);
                    });
                });
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            showError("Ocurrió un error consultando el servidor");
        });
    }

    function crearFilaExtra(extra) {
        var texto = "<tr class='align-middle'>";
        
        // Descripción
        texto += "<td><strong>" + (extra.descripcion || 'Sin descripción') + "</strong></td>";
        
        // Precio con formato
        var precioFormateado = extra.precio ? currencyCRFormat(extra.precio) : '₡0.00';
        texto += "<td class='text-center'><span class='badge badge-success'>" + precioFormateado + "</span></td>";
        
        // Grupo
        texto += "<td class='text-center'><span class='badge badge-info'>" + (extra.dsc_grupo || 'Sin grupo') + "</span></td>";
        
        // Materia Prima
        var mpNombre = extra.nombreMp == null || extra.nombreMp === '' ? 
            '<span class="text-muted">Sin asignar</span>' : 
            '<span class="text-primary">' + extra.nombreMp + '</span>';
        texto += "<td class='text-center'>" + mpNombre + "</td>";
        
        // Cantidad MP
        var cantMP = extra.cant_mp == null || extra.cant_mp === '' ? '0' : extra.cant_mp;
        texto += "<td class='text-center'><span class='badge badge-secondary'>" + cantMP + "</span></td>";
        
        // Es requerido
        var esRequerido = extra.es_requerido == 0 ? 
            '<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>' : 
            '<span class="badge badge-warning"><i class="fas fa-check"></i> Sí</span>';
        texto += "<td class='text-center'>" + esRequerido + "</td>";
        
        // Es múltiple
        var esMultiple = extra.multiple == 0 ? 
            '<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>' : 
            '<span class="badge badge-primary"><i class="fas fa-check-double"></i> Sí</span>';
        texto += "<td class='text-center'>" + esMultiple + "</td>";
        
        // Acciones
        texto += `<td class="text-center">
            <button class="btn btn-sm btn-primary" onclick="editarExtraGenerico('${extra.id}')" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="eliminarExtraGenerico('${extra.id}')" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </td>`;
        
        texto += "</tr>";

        $("#tbody-extras-genericos").append(texto);
    }

    function limpiarFormulario() {
        $('#ipt_dsc_ext').val("");
        $('#ipt_precio_ext').val("");
        $('#ipt_id_ext_generico').val("-1");
        $('#ipt_dsc_gru_ext').val("");
        $("#requisito").prop('checked', false);
        $("#multiple").prop('checked', false);
        $('#ipt_cantidad_req_extra').val("");
        $('#select_prod_mp_extra').val("");
        
        // Limpiar validación de campo requerido
        $('#ipt_cantidad_req_extra').prop('required', false);
        $('#label-cantidad-required').hide();
        
        // Si Select2 está inicializado, resetearlo
        if (typeof $.fn.select2 !== 'undefined' && $('#select_prod_mp_extra').hasClass('select2-hidden-accessible')) {
            $('#select_prod_mp_extra').val(null).trigger('change');
        }
        
        // Actualizar título del formulario
        $('#form-title').text('Agregar nuevo extra genérico');
        
        // Recargar grupos disponibles
        cargarGruposExtras();
        
        // Focus en el primer campo
        $('#ipt_dsc_ext').focus();
    }

    function guardarExtraGenerico() {
        var ipt_dsc_ext = $('#ipt_dsc_ext').val();
        var ipt_precio_ext = $('#ipt_precio_ext').val();
        var ipt_id_ext_generico = $('#ipt_id_ext_generico').val();
        var ipt_dsc_gru_ext = $('#ipt_dsc_gru_ext').val();
        var esRequerido = $("#requisito").is(':checked');
        var multiple = $("#multiple").is(':checked');
        var ipt_cantidad_req_extra = $('#ipt_cantidad_req_extra').val();
        var select_prod_mp_extra = $('#select_prod_mp_extra').val();
        
        // Validación básica
        if (!ipt_dsc_ext || ipt_dsc_ext.trim() === '') {
            showError("La descripción del extra es requerida");
            $('#ipt_dsc_ext').focus();
            return;
        }
        
        if (!ipt_dsc_gru_ext || ipt_dsc_gru_ext.trim() === '') {
            showError("La descripción del grupo es requerida");
            $('#ipt_dsc_gru_ext').focus();
            return;
        }
        
        if (!ipt_precio_ext || parseFloat(ipt_precio_ext) < 0) {
            showError("El precio debe ser mayor o igual a 0");
            $('#ipt_precio_ext').focus();
            return;
        }
        
        // Validar que si se selecciona materia prima, se debe indicar cantidad
        if (select_prod_mp_extra && select_prod_mp_extra !== '' && (!ipt_cantidad_req_extra || parseFloat(ipt_cantidad_req_extra) <= 0)) {
            showError("Si selecciona materia prima, debe indicar la cantidad requerida");
            $('#ipt_cantidad_req_extra').focus();
            return;
        }
        
        $.ajax({
            url: base_path + '/materiaPrima/extras-generico/guardar',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                id: ipt_id_ext_generico,
                precio: ipt_precio_ext,
                dsc: ipt_dsc_ext,
                dsc_grupo: ipt_dsc_gru_ext,
                es_Requerido: esRequerido,
                multiple: multiple,
                materia_prima_extra: select_prod_mp_extra || '',
                cantidad_mp_extra: ipt_cantidad_req_extra || ''
            }
        }).done(function(respuesta) {
            if (!respuesta.estado) {
                showError(respuesta.mensaje);
                return;
            }

            showSuccess("Se guardó correctamente");
            limpiarFormulario();
            cargarExtrasGenericos();
            // Recargar grupos después de guardar
            cargarGruposExtras();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            showError("Ocurrió un error consultando el servidor");
        });
    }

    function editarExtraGenerico(id) {
        $.ajax({
            url: base_path + '/materiaPrima/extras-generico/cargar',
            type: 'get',
            data: {
                _token: CSRF_TOKEN
            }
        }).done(function(respuesta) {
            if (!respuesta.estado) {
                showError(respuesta.mensaje);
                return;
            }

            var extra = respuesta.datos.find(function(e) {
                return e.id == id;
            });

            if (!extra) {
                showError("No se encontró el extra genérico");
                return;
            }

            // Llenar el formulario
            $('#ipt_id_ext_generico').val(extra.id);
            $('#ipt_dsc_ext').val(extra.descripcion);
            $('#ipt_precio_ext').val(extra.precio);
            $('#ipt_dsc_gru_ext').val(extra.dsc_grupo);
            $("#requisito").prop('checked', extra.es_requerido == 1);
            $("#multiple").prop('checked', extra.multiple == 1);
            $('#ipt_cantidad_req_extra').val(extra.cant_mp || '');
            
            // Seleccionar materia prima si existe
            if (extra.materia_prima) {
                $('#select_prod_mp_extra').val(extra.materia_prima).trigger('change');
                $('#ipt_cantidad_req_extra').prop('required', true);
                $('#label-cantidad-required').show();
            } else {
                $('#select_prod_mp_extra').val(null).trigger('change');
                $('#ipt_cantidad_req_extra').prop('required', false);
                $('#label-cantidad-required').hide();
            }

            // Actualizar título del formulario
            $('#form-title').text('Editar extra genérico: ' + extra.descripcion);

            // Scroll al formulario
            $('html, body').animate({
                scrollTop: $('.card.border-primary').offset().top - 100
            }, 500);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            showError("Ocurrió un error consultando el servidor");
        });
    }

    function eliminarExtraGenerico(id) {
        if (!confirm("¿Está seguro de que desea eliminar este extra genérico?")) {
            return;
        }

        $.ajax({
            url: base_path + '/materiaPrima/extras-generico/eliminar',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                id: id
            }
        }).done(function(respuesta) {
            if (!respuesta.estado) {
                showError(respuesta.mensaje);
                return;
            }

            showSuccess("Se eliminó correctamente");
            cargarExtrasGenericos();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            showError("Ocurrió un error consultando el servidor");
        });
    }

    // Función para formatear moneda (si no existe)
    function currencyCRFormat(value) {
        if (value == null || value === '') return '₡0.00';
        return '₡' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
</script>
@endsection
