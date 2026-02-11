window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var extrasGestion = [];
var extraGestion = null;

$(document).ready(function () {
    $("#btn_buscar_pro").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_generico tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function clickProducto(id) {
    $('#idProductoEditar').val(id);
    $('#formEditarProducto').submit();
}

function eliminarProducto(id) {
    swal({
        title: 'Seguro de inactivar el producto?',
        text: 'No podra deshacer esta acción!',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                swal.close();
                $('#idProductoEliminar').val(id);
                $('#formEliminarProducto').submit();

            } else {
                swal.close();
            }
        });


}

function clickMateriaPrima(id) {
    id_prod_seleccionado = id;
    cargarMateriaPrima();
    $("#mdl-materia-prima").modal("show");
}

function clickExtras(id) {
    id_prod_seleccionado = id;
    cargarExtras();
    $("#mdl-extras").modal("show");
}


function cargarMateriaPrima() {

    $.ajax({
        url: '/menu/productos/cargarMpProd',
        type: 'get',
        data: {
            _token: CSRF_TOKEN,
            id_prod_seleccionado: id_prod_seleccionado
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        $("#tbody-inv").html("");
        extrasGestion = respuesta.datos;
        respuesta.datos.forEach(p => {
            crearMateriaPrima(p);
        });

    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function cargarExtras() {

    $.ajax({
        url: '/menu/productos/cargarExtras',
        type: 'get',
        data: {
            _token: CSRF_TOKEN,
            id_prod_seleccionado: id_prod_seleccionado
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        extrasGestion = respuesta.datos;
        $("#tbody-ext").html("");
        
        // Actualizar contador de extras
        var cantidadExtras = respuesta.datos ? respuesta.datos.length : 0;
        $("#badge-count-extras").text(cantidadExtras);
        
        // Mostrar/ocultar mensaje de vacío
        if (cantidadExtras === 0) {
            $("#empty-extras-message").show();
            $("#tbl-inv").hide();
        } else {
            $("#empty-extras-message").hide();
            $("#tbl-inv").show();
            respuesta.datos.forEach(p => {
                crearExtras(p);
            });
        }

    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function crearExtras(producto) {
    let texto = "<tr class='align-middle'>";
    
    // Descripción
    texto += "<td><strong>" + (producto.descripcion || 'Sin descripción') + "</strong></td>";
    
    // Precio con formato
    let precioFormateado = producto.precio ? currencyCRFormat(producto.precio) : '₡0.00';
    texto += "<td class='text-center'><span class='badge badge-success'>" + precioFormateado + "</span></td>";
    
    // Grupo
    texto += "<td class='text-center'><span class='badge badge-info'>" + (producto.dsc_grupo || 'Sin grupo') + "</span></td>";
    
    // Materia Prima
    let mpNombre = producto.nombreMp == null || producto.nombreMp === '' ? 
        '<span class="text-muted">Sin asignar</span>' : 
        '<span class="text-primary">' + producto.nombreMp + '</span>';
    texto += "<td class='text-center'>" + mpNombre + "</td>";
    
    // Cantidad MP
    let cantMP = producto.cant_mp == null || producto.cant_mp === '' ? '0' : producto.cant_mp;
    texto += "<td class='text-center'><span class='badge badge-secondary'>" + cantMP + "</span></td>";
    
    // Es requerido
    let esRequerido = producto.es_requerido == 0 ? 
        '<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>' : 
        '<span class="badge badge-warning"><i class="fas fa-check"></i> Sí</span>';
    texto += "<td class='text-center'>" + esRequerido + "</td>";
    
    // Es múltiple
    let esMultiple = producto.multiple == 0 ? 
        '<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>' : 
        '<span class="badge badge-primary"><i class="fas fa-check-double"></i> Sí</span>';
    texto += "<td class='text-center'>" + esMultiple + "</td>";
    
    // Acciones
    texto += `<td class="text-center">
        <button class="btn btn-sm btn-primary" onclick="cargarEditarExtra('${producto.id}')" title="Editar">
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-sm btn-danger" onclick="eliminarExtra('${producto.id}')" title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    </td>`;
    
    texto += "</tr>";

    $("#tbody-ext").append(texto);

}

function crearMateriaPrima(producto) {
    let texto = "<tr>";
    texto += "<td class='text-center'>" + producto.nombre + "</td>";
    texto += "<td class='text-center'>" + producto.cantidad + "</td>";
    texto += "<td  class='text-center''>" + producto.unidad_medida + "</td>";
    texto += '<td class="text-center"><button  class="btn btn-icon btn-secondary" onclick="eliminarProdMp(' + producto.id_mp_x_prod + ')"' +
        '><i class="fas fa-trash"></i></button></td>';
    texto += "</tr>";

    $("#tbody-inv").append(texto);

}

function cerrarMateriaPrima() {
    $("#mdl-materia-prima").modal("hide");
}

function cerrarExtras() {
    $("#mdl-extras").modal("hide");
}

function limpiarMateriaPrimaProducto() {
    $('#select_prod_mp').val(1);
    $('#ipt_cantidad_req').val(0);
    $('#ipt_id_prod_mp').val(-1);
}

function agregarMateriaPrimaProducto() {
    let id_prod = $('#select_prod_mp').val();
    let cant = $('#ipt_cantidad_req').val();
    let id_mp_prod = $('#ipt_id_prod_mp').val();
    $.ajax({
        url: '/menu/productos/guardarMpProd',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id_prod_seleccionado: id_prod_seleccionado,
            id_mp_prod: id_mp_prod,
            id_prod: id_prod,
            cant: cant
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            console.log(respuesta.datos);
            showError(respuesta.mensaje);
            return;
        }


        showSuccess("Se agregó correctamente");
        cargarMateriaPrima();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function limpiarExtraProd() {
    $('#ipt_dsc_ext').val("");
    $('#ipt_precio_ext').val("");
    $('#ipt_id_prod_ext').val("-1");
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
    
    // Focus en el primer campo
    $('#ipt_dsc_ext').focus();
    
    // Mostrar mensaje de confirmación
    showSuccess("Formulario limpiado correctamente");
}

function agregarExtraProducto() {
    let ipt_dsc_ext = $('#ipt_dsc_ext').val();
    let ipt_precio_ext = $('#ipt_precio_ext').val();
    let ipt_id_prod_ext = $('#ipt_id_prod_ext').val();
    let ipt_dsc_gru_ext = $('#ipt_dsc_gru_ext').val();
    let esRequerido = $("#requisito").is(':checked');
    let multiple = $("#multiple").is(':checked');
    let ipt_cantidad_req_extra = $('#ipt_cantidad_req_extra').val();
    let select_prod_mp_extra = $('#select_prod_mp_extra').val();
    
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
    
    $.ajax({
        url: '/menu/productos/guardarExtProd',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id: ipt_id_prod_ext,
            precio: ipt_precio_ext,
            dsc: ipt_dsc_ext,
            dsc_grupo: ipt_dsc_gru_ext,
            producto: id_prod_seleccionado,
            es_Requerido: esRequerido,
            multiple: multiple,
            materia_prima_extra: select_prod_mp_extra,
            cantidad_mp_extra: ipt_cantidad_req_extra
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        showSuccess("Se guardó correctamente");
        limpiarExtraProd(); // Limpiar el formulario después de guardar
        
        // Colapsar el formulario y expandir la lista de extras
        colapsarSeccion('formulario-extra-section');
        expandirSeccion('lista-extras-section');
        
        cargarExtras();
        
        // Scroll suave hacia la lista de extras
        setTimeout(function() {
            var $lista = $('#lista-extras-section');
            if ($lista.length) {
                $('html, body').animate({
                    scrollTop: $lista.offset().top - 100
                }, 500);
            }
        }, 350);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}



function eliminarProdMp(id_prod_mp) {
    $.ajax({
        url: '/menu/productos/eliminarMpProd',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id_prod_mp: id_prod_mp
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        showSuccess("Se elimino correctamente");
        cargarMateriaPrima();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function eliminarExtra(id_prod_mp) {
    $.ajax({
        url: '/menu/productos/eliminarExtra',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id_prod: id_prod_mp
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        showSuccess("Se elimino correctamente");
        cargarExtras();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function cargarEditarExtra(id_prod_mp) {
    extrasGestion.forEach(e => {
        if (e.id == id_prod_mp) {
            extraGestion = e;
        }
    });

    cargarEditarExtrarMdl();
}

function cargarEditarExtrarMdl() {
    $('#ipt_dsc_ext').val(extraGestion.descripcion);
    $('#ipt_precio_ext').val(extraGestion.precio);
    $('#ipt_id_prod_ext').val(extraGestion.id);
    $('#ipt_dsc_gru_ext').val(extraGestion.dsc_grupo);
    $("#requisito").prop('checked', extraGestion.es_requerido == 1);
    $("#multiple").prop('checked', extraGestion.multiple == 1);
    $('#ipt_cantidad_req_extra').val(extraGestion.cant_mp);
    $('#select_prod_mp_extra').val(extraGestion.materia_prima);
    
    // Actualizar Select2 si está disponible
    if (typeof $.fn.select2 !== 'undefined' && $('#select_prod_mp_extra').hasClass('select2-hidden-accessible')) {
        $('#select_prod_mp_extra').trigger('change');
    }
    
    // Validar cantidad si hay materia prima seleccionada
    if (extraGestion.materia_prima) {
        $('#ipt_cantidad_req_extra').prop('required', true);
        $('#label-cantidad-required').show();
    } else {
        $('#ipt_cantidad_req_extra').prop('required', false);
        $('#label-cantidad-required').hide();
    }
    
    // Expandir la sección del formulario cuando se edita
    expandirSeccion('formulario-extra-section');
    
    // Scroll suave hacia el formulario
    setTimeout(function() {
        var $formulario = $('#formulario-extra-section');
        if ($formulario.length) {
            $('html, body').animate({
                scrollTop: $formulario.offset().top - 100
            }, 500);
        }
    }, 350);
}


// Funciones para el modal de configuración de FE
function clickConfigFE(idProducto) {
    limpiarFormularioFE();
    id_prod_seleccionado = idProducto;
    cargarDatosFE();


}

function cargarDatosFE() {
    $("#loader").fadeIn();
    $.ajax({
        url: '/productos/cargarDatosFE',
        type: 'get',
        data: {
            _token: CSRF_TOKEN,
            idProducto: id_prod_seleccionado,
            tipoProducto: 'MENU'
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        // Cargar unidades de medida desde la API si no se han cargado
        if ($('#unidad_medida_fe option').length <= 1) {
            cargarUnidadesMedida(function() {
                // Una vez cargadas las unidades, cargar los datos del producto
                cargarDatosFEHtml(respuesta.datos);
            });
        } else {
            cargarDatosFEHtml(respuesta.datos);
        }
        $('#mdl-config-fe').modal('show');

    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
    $("#loader").fadeOut();
}

function cargarDatosFEHtml(info) {
    $('#codigo_cabys').val(info.codigo_cabys);
    $('#unidad_medida_fe').val(info.unidad_medida);
    $('#tarifa_impuesto').val(info.tarifa_impuesto + " %");
    $('#tipo_codigo').val(info.tipo_codigo);
    $('#descripcion_fe').val(info.descripcionDetalle);
    $('#id_producto_fe').val(info.id_producto);
}

function cerrarConfigFE() {
    $('#mdl-config-fe').modal('hide');
    limpiarFormularioFE();
}

function limpiarFormularioFE() {
    $('#codigo_cabys').val('');
    $('#unidad_medida_fe').val('');
    $('#tarifa_impuesto').val('');
    $('#tipo_codigo').val('');
    $('#descripcion_fe').val('');
    $('#id_producto_fe').val('');
}

/**
 * Carga las unidades de medida desde la API de FactuX
 * @param {Function} callback - Función a ejecutar después de cargar las unidades
 */
function cargarUnidadesMedida(callback) {
    $.ajax({
        url: `${base_path}/fe/obtenerUnidadesMedida`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje'] || 'Error al cargar unidades de medida');
            if (callback) callback();
            return;
        }

        // Limpiar el select (mantener solo la opción por defecto)
        $('#unidad_medida_fe option:not(:first)').remove();

        // Agregar las unidades obtenidas de la API
        var unidades = response['datos'] || [];
        unidades.forEach(function (unidad) {
            // La API puede retornar objetos con diferentes estructuras
            // Intentar diferentes campos comunes
            var codigo = unidad.codigo || unidad.cod || unidad.id || unidad.nombre;
            var nombre = unidad.nombre || unidad.descripcion || unidad.desc || codigo;
            
            if (codigo && nombre) {
                var option = $('<option></option>')
                    .attr('value', codigo)
                    .text(nombre);
                $('#unidad_medida_fe').append(option);
            }
        });

        // Ejecutar callback si existe
        if (callback) callback();

    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Error al cargar unidades de medida:", errorThrown);
        showError("Error al cargar unidades de medida desde FactuX");
        if (callback) callback();
    });
}

function validarFormularioFE() {
    if (!$('#codigo_cabys').val()) {
        showError('El código CABYS es obligatorio');
        $('#codigo_cabys').focus();
        return false;
    }
    if (!$('#unidad_medida_fe').val()) {
        showError('La unidad de medida es obligatoria');
        $('#unidad_medida_fe').focus();
        return false;
    }

    if (!$('#tipo_codigo').val()) {
        showError('El tipo de código es obligatorio');
        $('#tipo_codigo').focus();
        return false;
    }

    return true;
}

function guardarConfigFE() {
    if (!validarFormularioFE()) {
        return;
    }

    // Recopilar datos del formulario
    var datosFE = {
        id_producto: $('#id_producto_fe').val(),
        codigo_cabys: $('#codigo_cabys').val(),
        unidad_medida: $('#unidad_medida_fe').val(),
        tipo_codigo: $('#tipo_codigo').val(),
        impuesto_incluido: $('#impuesto_incluido').val(),
        descripcion: $('#descripcion_fe').val()
    };

    $("#loader").fadeIn();
    $.ajax({
        url: '/productos/guardarConfigFE',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            data: datosFE,
            idProducto: id_prod_seleccionado,
            tipoProducto: 'MENU'
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }
        showSuccess("Se guardo correctamente");
        cerrarConfigFE();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
    $("#loader").fadeOut();
}

// Funciones para manejar extras genéricos
function cargarExtrasGenericosDisponibles() {
    $.ajax({
        url: '/materiaPrima/extras-generico/obtener',
        type: 'get',
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function(respuesta) {
        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        var extras = respuesta.datos || [];
        var contenedor = $('#contenedor-extras-genericos');
        contenedor.html('');

        if (extras.length === 0) {
            contenedor.html('<p class="text-muted text-center">No hay extras genéricos disponibles. <a href="/materiaPrima/extras-generico" target="_blank">Crear extras genéricos</a></p>');
            $('#card-extras-genericos').show();
            return;
        }

        // Agrupar por grupo + es_requerido + multiple
        var grupos = {};
        extras.forEach(function(extra) {
            var grupoNombre = extra.dsc_grupo || 'Sin grupo';
            var claveGrupo = grupoNombre + '_' + (extra.es_requerido || 0) + '_' + (extra.multiple || 0);
            
            if (!grupos[claveGrupo]) {
                grupos[claveGrupo] = {
                    nombre: grupoNombre,
                    es_requerido: extra.es_requerido || 0,
                    multiple: extra.multiple || 0,
                    extras: [],
                    clave: claveGrupo
                };
            }
            grupos[claveGrupo].extras.push(extra);
        });

        // Botones de selección rápida (todos/ninguno)
        var botonesRapidos = $('<div class="mb-3 text-center" style="border-bottom: 2px solid #dee2e6; padding-bottom: 10px;"></div>');
        botonesRapidos.append('<button type="button" class="btn btn-sm btn-success mr-2" onclick="seleccionarTodosExtrasGenericos()" title="Seleccionar todos los extras">' +
            '<i class="fas fa-check-double"></i> Seleccionar Todos</button>');
        botonesRapidos.append('<button type="button" class="btn btn-sm btn-secondary" onclick="deseleccionarTodosExtrasGenericos()" title="Deseleccionar todos los extras">' +
            '<i class="fas fa-times"></i> Deseleccionar Todos</button>');
        contenedor.append(botonesRapidos);

        // Crear HTML para cada grupo (ordenado por nombre de grupo)
        Object.keys(grupos).sort().forEach(function(claveGrupo) {
            var grupo = grupos[claveGrupo];
            var grupoDiv = $('<div class="mb-3 border rounded p-3" style="background-color: #f8f9fa;" data-grupo-clave="' + claveGrupo + '"></div>');
            
            // Encabezado del grupo con información de configuración y botones de selección
            var headerDiv = $('<div class="d-flex justify-content-between align-items-center mb-2"></div>');
            
            var headerInfo = $('<div></div>');
            headerInfo.append('<h6 class="text-primary mb-0" style="display: inline-block;">');
            headerInfo.append('<i class="fas fa-layer-group"></i> <strong>' + grupo.nombre + '</strong> - ');
            headerInfo.append('Requerido: ' + (grupo.es_requerido == 1 ? '<span class="badge badge-warning badge-sm">Sí</span>' : '<span class="badge badge-secondary badge-sm">No</span>') + ' - ');
            headerInfo.append('Múltiple: ' + (grupo.multiple == 1 ? '<span class="badge badge-primary badge-sm">Sí</span>' : '<span class="badge badge-secondary badge-sm">No</span>'));
            headerInfo.append('</h6>');
            
            var botonesGrupo = $('<div></div>');
            botonesGrupo.append('<button type="button" class="btn btn-xs btn-success mr-1" onclick="seleccionarGrupoExtrasGenericos(\'' + claveGrupo + '\')" title="Seleccionar todo el grupo">' +
                '<i class="fas fa-check"></i> Todo</button>');
            botonesGrupo.append('<button type="button" class="btn btn-xs btn-secondary" onclick="deseleccionarGrupoExtrasGenericos(\'' + claveGrupo + '\')" title="Deseleccionar todo el grupo">' +
                '<i class="fas fa-times"></i> Ninguno</button>');
            
            headerDiv.append(headerInfo);
            headerDiv.append(botonesGrupo);
            grupoDiv.append(headerDiv);
            
            // Contenedor para los checkboxes del grupo
            var extrasContainer = $('<div class="extras-grupo-container" style="max-height: 200px; overflow-y: auto;"></div>');
            
            grupo.extras.forEach(function(extra) {
                var extraDiv = $('<div class="form-check mb-2" style="padding-left: 2rem;"></div>');
                var checkbox = $('<input class="form-check-input extra-checkbox" type="checkbox" value="' + extra.id + '" id="extra_gen_' + extra.id + '" data-extra=\'' + JSON.stringify(extra).replace(/'/g, "&#39;") + '\' data-grupo-clave="' + claveGrupo + '">');
                var label = $('<label class="form-check-label" for="extra_gen_' + extra.id + '" style="cursor: pointer;">' + 
                    extra.descripcion + ' - <span class="text-success font-weight-bold">' + currencyCRFormat(extra.precio) + '</span>' +
                    '</label>');
                
                // Hacer el label clickeable
                label.on('click', function(e) {
                    if (e.target.tagName !== 'INPUT') {
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }
                });
                
                extraDiv.append(checkbox);
                extraDiv.append(label);
                extrasContainer.append(extraDiv);
            });
            
            grupoDiv.append(extrasContainer);
            contenedor.append(grupoDiv);
        });

        // Agregar botón para agregar seleccionados con contador
        var botonAgregarDiv = $('<div class="mt-3 text-center" style="border-top: 2px solid #dee2e6; padding-top: 15px;"></div>');
        botonAgregarDiv.append('<p class="mb-2"><strong>Extras seleccionados: <span id="contador-extras-seleccionados" class="badge badge-info">0</span></strong></p>');
        botonAgregarDiv.append('<button type="button" class="btn btn-primary btn-lg btn-block" onclick="agregarExtrasGenericosSeleccionados()">' +
            '<i class="fas fa-plus-circle"></i> Agregar extras seleccionados al producto</button>');
        contenedor.append(botonAgregarDiv);
        
        // Actualizar contador cuando cambien los checkboxes
        $(document).on('change', '.extra-checkbox', function() {
            actualizarContadorExtrasSeleccionados();
        });
        
        // Inicializar contador
        actualizarContadorExtrasSeleccionados();

        $('#card-extras-genericos').show();
    }).fail(function(jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function agregarExtrasGenericosSeleccionados() {
    if (!id_prod_seleccionado || id_prod_seleccionado == '-1') {
        showError("Debe seleccionar un producto primero");
        return;
    }

    var extrasSeleccionados = [];
    $('#contenedor-extras-genericos input[type="checkbox"]:checked').each(function() {
        var extraData = $(this).data('extra');
        if (extraData) {
            extrasSeleccionados.push(extraData);
        }
    });

    if (extrasSeleccionados.length === 0) {
        showError("Debe seleccionar al menos un extra genérico");
        return;
    }

    // Agregar cada extra genérico al producto
    var agregados = 0;
    var total = extrasSeleccionados.length;

    extrasSeleccionados.forEach(function(extra) {
        $.ajax({
            url: '/menu/productos/guardarExtProd',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                id: '-1',
                precio: extra.precio,
                dsc: extra.descripcion,
                dsc_grupo: extra.dsc_grupo,
                producto: id_prod_seleccionado,
                es_Requerido: extra.es_requerido == 1 ? 'true' : 'false',
                multiple: extra.multiple == 1 ? 'true' : 'false',
                materia_prima_extra: extra.materia_prima || '',
                cantidad_mp_extra: extra.cant_mp || ''
            },
            async: false
        }).done(function(respuesta) {
            if (respuesta.estado) {
                agregados++;
            }
        });
    });

    if (agregados > 0) {
        showSuccess("Se agregaron " + agregados + " de " + total + " extras genéricos correctamente");
        cargarExtras();
        // Limpiar checkboxes
        $('#contenedor-extras-genericos input[type="checkbox"]').prop('checked', false);
    } else {
        showError("No se pudieron agregar los extras genéricos");
    }
}

// Funciones para selección rápida de extras genéricos
function seleccionarTodosExtrasGenericos() {
    $('.extra-checkbox').prop('checked', true);
    actualizarContadorExtrasSeleccionados();
    showSuccess("Todos los extras han sido seleccionados");
}

function deseleccionarTodosExtrasGenericos() {
    $('.extra-checkbox').prop('checked', false);
    actualizarContadorExtrasSeleccionados();
    showSuccess("Todos los extras han sido deseleccionados");
}

function seleccionarGrupoExtrasGenericos(claveGrupo) {
    $('.extra-checkbox[data-grupo-clave="' + claveGrupo + '"]').prop('checked', true);
    actualizarContadorExtrasSeleccionados();
    
    // Obtener nombre del grupo para el mensaje
    var grupoDiv = $('[data-grupo-clave="' + claveGrupo + '"]');
    var nombreGrupo = grupoDiv.find('h6 strong').text();
    showSuccess("Todos los extras del grupo '" + nombreGrupo + "' han sido seleccionados");
}

function deseleccionarGrupoExtrasGenericos(claveGrupo) {
    $('.extra-checkbox[data-grupo-clave="' + claveGrupo + '"]').prop('checked', false);
    actualizarContadorExtrasSeleccionados();
    
    // Obtener nombre del grupo para el mensaje
    var grupoDiv = $('[data-grupo-clave="' + claveGrupo + '"]');
    var nombreGrupo = grupoDiv.find('h6 strong').text();
    showSuccess("Todos los extras del grupo '" + nombreGrupo + "' han sido deseleccionados");
}

function actualizarContadorExtrasSeleccionados() {
    var cantidad = $('.extra-checkbox:checked').length;
    $('#contador-extras-seleccionados').text(cantidad);
    
    // Cambiar color del badge según la cantidad
    var badge = $('#contador-extras-seleccionados');
    badge.removeClass('badge-info badge-warning badge-success');
    if (cantidad === 0) {
        badge.addClass('badge-secondary');
    } else if (cantidad < 5) {
        badge.addClass('badge-info');
    } else if (cantidad < 10) {
        badge.addClass('badge-warning');
    } else {
        badge.addClass('badge-success');
    }
}

// Funciones auxiliares para expandir/colapsar secciones
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

// Función para formatear moneda (si no existe)
function currencyCRFormat(value) {
    if (value == null || value === '') return '₡0.00';
    return '₡' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}