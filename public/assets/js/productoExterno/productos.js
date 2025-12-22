window.addEventListener("load", initialice, false);
var id_prod_seleccionado = 0;

$(document).ready(function () {
  $("#btn_buscar_pro").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_generico tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

function initialice() {

}

function clickProducto(id){
  $('#idProductoEditar').val(id);
  $('#formEditarProducto').submit();
}

function eliminarProducto(id){
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
  cargarMateriaPrima() ;
  $("#mdl-materia-prima").modal("show");
}

function cargarMateriaPrima() {

  $.ajax({
      url: '/productoExterno/productos/cargarMpProd',
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
      respuesta.datos.forEach(p => {
          crearMateriaPrima(p);
      });

  }).fail(function (jqXHR, textStatus, errorThrown) {
      showError("Ocurrió un error consultando el servidor");
  });
}

function crearMateriaPrima(producto) {
  let texto = "<tr>";
  texto += "<td class='text-center'>" + producto.nombre + "</td>";
  texto += "<td class='text-center'>" + producto.cantidad + "</td>";
  texto += "<td  class='text-center''>" +   producto.unidad_medida +"</td>";
  texto += '<td class="text-center"><button  class="btn btn-icon btn-secondary" onclick="eliminarProdMp(' + producto.id_mp_x_prod + ')"' +
      '><i class="fas fa-trash"></i></button></td>';
  texto += "</tr>";

  $("#tbody-inv").append(texto);

}

function eliminarProdMp(id_prod_mp) {
  $.ajax({
      url: '/productoExterno/productos/eliminarMpProd',
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

function cerrarMateriaPrima(){
  $("#mdl-materia-prima").modal("hide");
}


function agregarMateriaPrimaProducto() {
  let id_prod = $('#select_prod_mp').val();
  let cant = $('#ipt_cantidad_req').val();
  let id_mp_prod = $('#ipt_id_prod_mp').val();
  $.ajax({
      url: '/productoExterno/productos/guardarMpProd',
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
          showError(respuesta.mensaje);
          return;
      }

      showSuccess("Se agregó correctamente");
      cargarMateriaPrima();
  }).fail(function (jqXHR, textStatus, errorThrown) {
      showError("Ocurrió un error consultando el servidor");
  });
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
            tipoProducto: 'EXTERNO'
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
            idProducto: id_prod_seleccionado,
            tipoProducto: 'EXTERNO',
            data: datosFE
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