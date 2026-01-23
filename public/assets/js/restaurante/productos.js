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
        cargarExtras();
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
}


// Funciones para el modal de configuración de FE
function clickConfigFE(idProducto) {
    limpiarFormularioFE();
    id_prod_seleccionado = idProducto;
    cargarDatosFE();


}

function cargarDatosFE() {
    console.log("=== DEBUG cargarDatosFE ===");
    console.log("id_prod_seleccionado:", id_prod_seleccionado);
    console.log("tipoProducto: MENU");
    
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
        console.log("=== Respuesta del servidor ===");
        console.log("Respuesta completa:", respuesta);
        console.log("respuesta.estado:", respuesta.estado);
        console.log("respuesta.mensaje:", respuesta.mensaje);
        console.log("respuesta.datos:", respuesta.datos);

        if (!respuesta.estado) {
            console.error("Error en respuesta:", respuesta.mensaje);
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
        console.error("=== Error en AJAX ===");
        console.error("jqXHR:", jqXHR);
        console.error("textStatus:", textStatus);
        console.error("errorThrown:", errorThrown);
        console.error("Response Text:", jqXHR.responseText);
        showError("Ocurrió un error consultando el servidor");
    });
    $("#loader").fadeOut();
}

function cargarDatosFEHtml(info) {
    console.log("=== DEBUG cargarDatosFEHtml ===");
    console.log("info completa:", info);
    console.log("codigo_cabys:", info.codigo_cabys);
    console.log("unidad_medida:", info.unidad_medida);
    console.log("tarifa_impuesto:", info.tarifa_impuesto);
    console.log("tipo_codigo:", info.tipo_codigo);
    console.log("descripcionDetalle:", info.descripcionDetalle);
    console.log("id_producto:", info.id_producto);
    
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