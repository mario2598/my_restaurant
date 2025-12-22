window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

// Variables globales
var clienteIdActual = -1;
var tablaClientes;

// Funciones para mostrar mensajes
function showSuccess(message) {
  // Usar iziToast si está disponible, sino usar alert
  if (typeof iziToast !== 'undefined') {
    iziToast.success({
      title: 'Éxito',
      message: message,
      position: 'topRight'
    });
  } else {
    alert(message);
  }
}

function showError(message) {
  // Usar iziToast si está disponible, sino usar alert
  if (typeof iziToast !== 'undefined') {
    iziToast.error({
      title: 'Error',
      message: message,
      position: 'topRight'
    });
  } else {
    alert(message);
  }
}

// Función para recargar la tabla
function recargarTabla() {
  if (tablaClientes) {
    tablaClientes.ajax.reload();
  }
}

$(document).ready(function () {
  $("#input_buscar_generico").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_generico tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

  // Inicializar DataTables con server-side processing
  tablaClientes = $('#tabla_clientes').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
      "url": `${base_path}/mant/clientes/obtener-clientes-ajax`,
      "type": "POST",
      "data": function(d) {
        d._token = CSRF_TOKEN;
      },
      "error": function(xhr, error, thrown) {
        showError('Error al cargar los datos de la tabla');
      }
    },
    "columns": [
      { "data": "nombre" },
      { "data": "apellidos" },
      { "data": "telefono" },
      { "data": "correo" },
      { "data": "ubicacion" },
      { 
        "data": "fe_badge",
        "orderable": false,
        "searchable": false
      },
      { 
        "data": "acciones",
        "orderable": false,
        "searchable": false,
        "render": function(data, type, row) {
          return `
            <a onclick='abrirModalEditarCliente("${data}")' title="Editar" class="btn btn-primary" style="color:white">
              <i class="fas fa-cog"></i>
            </a>
            <a class="btn btn-info btn-icon" title="Configuración de Facturación Electrónica" onclick='clickConfigFECliente("${data}")' style="cursor: pointer;">
              <i class="fas fa-file-invoice"></i>
            </a>
            <a onclick="eliminarCliente(${data})" title="Eliminar" class="btn btn-danger" style="color:white">
              <i class="fa fa-trash"></i>
            </a>
          `;
        }
      }
    ],
    "pageLength": 10,
    "order": [[0, "asc"]],
    "responsive": true,
    "autoWidth": false,
    "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
           '<"row"<"col-sm-12"tr>>' +
           '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
    "language": {
      "sProcessing": "Procesando...",
      "sLengthMenu": "Mostrar _MENU_ registros",
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ningún dato disponible en esta tabla",
      "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
      "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
      "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
      "sInfoPostFix": "",
      "sSearch": "Buscar:",
      "sUrl": "",
      "sInfoThousands": ",",
      "sLoadingRecords": "Cargando...",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
      },
      "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
      }
    }
  });
});

// Función para abrir modal de nuevo cliente
function abrirModalNuevoCliente() {
  clienteIdActual = -1;
  limpiarFormulario();
  $('#edit_cliente_text').html('<i class="fas fa-plus"></i> Nuevo Cliente');
  $('#mdl_generico').modal('show');
}

// Función para abrir modal de editar cliente
function abrirModalEditarCliente(clienteId) {
  clienteIdActual = clienteId;

  // Cargar datos del cliente
  $.ajax({
    url: `${base_path}/mant/clientes/obtener-cliente`,
    method: 'POST',
    data: {
      _token: CSRF_TOKEN,
      cliente_id: clienteId
    },
    success: function (response) {
      if (response.estado && response.datos) {
        $('#mdl_generico_ipt_nombre').val(response.datos.nombre || '');
        $('#mdl_generico_ipt_apellidos').val(response.datos.apellidos || '');
        $('#mdl_generico_ipt_tel').val(response.datos.telefono || '');
        $('#mdl_generico_ipt_correo').val(response.datos.correo || '');
        $('#mdl_generico_ipt_ubicacion').val(response.datos.ubicacion || '');
      } else {
        showError('Error al cargar los datos del cliente: ' + (response.mensaje || 'Error desconocido'));
      }
    },
    error: function () {
      showError('Error al cargar los datos del cliente');
    }
  });

  $('#edit_cliente_text').html('<i class="fas fa-edit"></i> Editar Cliente');
  $('#mdl_generico').modal('show');
}

// Función para guardar cliente
function guardarCliente() {
  var nombre = $('#mdl_generico_ipt_nombre').val().trim();
  var telefono = $('#mdl_generico_ipt_tel').val().trim();
  var correo = $('#mdl_generico_ipt_correo').val().trim();
  var ubicacion = $('#mdl_generico_ipt_ubicacion').val().trim();
  var apellidos = $('#mdl_generico_ipt_apellidos').val().trim();

  // Mostrar spinner
  $('#loader').show();

  $.ajax({
    url: `${base_path}/mant/clientes/guardar`,
    method: 'POST',
    data: {
      _token: CSRF_TOKEN,
      mdl_generico_ipt_id: clienteIdActual,
      mdl_generico_ipt_nombre: nombre,
      mdl_generico_ipt_apellidos: apellidos,
      mdl_generico_ipt_tel: telefono,
      mdl_generico_ipt_correo: correo,
      mdl_generico_ipt_ubicacion: ubicacion
    },
         success: function (response) {
       $('#loader').hide();
       if (!response.estado) {
         showError('Error al guardar el cliente: ' + (response.mensaje || 'Error desconocido'));
         return;
       } 
       showSuccess(response.mensaje || 'Cliente guardado correctamente');
       $('#mdl_generico').modal('hide');
       recargarTabla(); // Recargar la tabla en lugar de la página
     },
    error: function () {
      showError('Error al guardar el cliente');
    }
  });
  $('#loader').hide();
}

// Función para cerrar modal
function cerrarModalGenerico() {
  $('#mdl_generico').modal('hide');
  limpiarFormulario();
}

// Función para limpiar formulario
function limpiarFormulario() {
  $('#mdl_generico_ipt_nombre').val('');
  $('#mdl_generico_ipt_apellidos').val('');
  $('#mdl_generico_ipt_tel').val('');
  $('#mdl_generico_ipt_correo').val('');
  $('#mdl_generico_ipt_ubicacion').val('');
  clienteIdActual = -1;
}

// Función para validar email
function isValidEmail(email) {
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Función para eliminar cliente
function eliminarCliente(clienteId) {
  if (confirm('¿Está seguro de que desea eliminar este cliente?')) {
    $.ajax({
      url: `${base_path}/mant/clientes/eliminarcliente`,
      method: 'POST',
      data: {
        _token: CSRF_TOKEN,
        cliente_id: clienteId
      },
      success: function(response) {
        if (response.estado) {
          showSuccess(response.mensaje || 'Cliente eliminado correctamente');
          recargarTabla(); // Recargar la tabla en lugar de la página
        } else {
          showError('Error al eliminar el cliente: ' + (response.mensaje || 'Error desconocido'));
        }
      },
      error: function() {
        showError('Error al eliminar el cliente');
      }
    });
  }
}

// Funciones para el modal de Facturación Electrónica de Clientes
function clickConfigFECliente(clienteId) {
  $('#id_cliente_fe').val(clienteId);
  
  // Cargar datos existentes si los hay
  $.ajax({
    url: `${base_path}/mant/clientes/obtener-info-fe-cliente`,
      method: 'POST',
      data: {
          _token: CSRF_TOKEN,
          cliente_id: clienteId
      },
    success: function (response) {
      if (response.estado && response.datos) {
        $('#codigo_actividad_cliente').val(response.datos.codigo_actividad || '722003');
        $('#tipo_identificacion_cliente').val(response.datos.tipo_identificacion || '01');
        $('#nombre_comercial_cliente').val(response.datos.nombre_comercial || '');
        $('#direccion_cliente').val(response.datos.direccion || '');
          } else {
              // Valores por defecto
              $('#codigo_actividad_cliente').val('722003');
              $('#tipo_identificacion_cliente').val('01');
              $('#nombre_comercial_cliente').val('');
              $('#direccion_cliente').val('');
          }
      },
    error: function () {
          // Valores por defecto en caso de error
          $('#codigo_actividad_cliente').val('722003');
          $('#tipo_identificacion_cliente').val('01');
          $('#nombre_comercial_cliente').val('');
          $('#direccion_cliente').val('');
      }
  });
  
  $('#mdl-config-fe-cliente').modal('show');
}

function guardarConfigFECliente() {
  var clienteId = $('#id_cliente_fe').val();
  var codigoActividad = $('#codigo_actividad_cliente').val();
  var tipoIdentificacion = $('#tipo_identificacion_cliente').val();
  var nombreComercial = $('#nombre_comercial_cliente').val();
  var direccion = $('#direccion_cliente').val();

  if (!clienteId) {
    showError('Error: No se ha seleccionado un cliente');
    return;
  }

  $.ajax({
    url: `${base_path}/mant/clientes/guardar-info-fe-cliente`,
    method: 'POST',
    data: {
      _token: CSRF_TOKEN,
      cliente_id: clienteId,
      codigo_actividad: codigoActividad,
      tipo_identificacion: tipoIdentificacion,
      nombre_comercial: nombreComercial,
      direccion: direccion
    },
    success: function (response) {
      if (response.estado) {
        showSuccess(response.mensaje || 'Información de facturación electrónica guardada correctamente');
              $('#mdl-config-fe-cliente').modal('hide');
        recargarTabla(); // Recargar la tabla para mostrar el cambio en FE Configurado
          } else {
        showError('Error al guardar: ' + (response.mensaje || 'Error desconocido'));
          }
      },
    error: function () {
      showError('Error al guardar la información de facturación electrónica');
      }
  });
}

function cerrarConfigFECliente() {
  $('#mdl-config-fe-cliente').modal('hide');
}