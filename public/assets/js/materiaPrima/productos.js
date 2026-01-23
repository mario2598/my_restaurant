window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var base_path = $('#base_path').val() || window.location.origin;
var tablaProductos = null;

$(document).ready(function () {
  $("#btn_buscar_pro").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_generico tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

  // Inicializar DataTable si existe
  if ($.fn.DataTable.isDataTable('#tablaProductos')) {
    tablaProductos = $('#tablaProductos').DataTable();
  }

  // Manejar el envío del formulario del modal
  $('#formNuevoProducto').on('submit', function(e) {
    e.preventDefault();
    guardarProductoAjax();
  });

  // Limpiar formulario cuando se cierra el modal
  $('#modalNuevoProducto').on('hidden.bs.modal', function () {
    limpiarFormularioProducto();
  });
});


function initialice() {
  var t=  document.getElementById('nombre');
  if (t) {
    t.addEventListener('input',function(){ 
      if (this.value.length > 30) 
         this.value = this.value.slice(0,30); 
    });
  }

  t=  document.getElementById('codigo');
  if (t) {
    t.addEventListener('input',function(){ 
      if (this.value.length > 15) 
         this.value = this.value.slice(0,15); 
    });
  }
}

function abrirModalNuevoProducto() {
  limpiarFormularioProducto();
  configurarModalModoNuevo();
  $('#modalNuevoProducto').modal('show');
}

function configurarModalModoNuevo() {
  $('#titulo_modal').text('Nuevo Producto Materia Prima');
  $('#icono_modal').removeClass('fa-edit').addClass('fa-plus-circle');
  $('#texto_guardar').text('Guardar Producto');
  $('#btn_eliminar_producto').hide();
}

function configurarModalModoEdicion() {
  $('#titulo_modal').text('Editar Producto Materia Prima');
  $('#icono_modal').removeClass('fa-plus-circle').addClass('fa-edit');
  $('#texto_guardar').text('Actualizar Producto');
  $('#btn_eliminar_producto').show();
}

function editarProducto(id) {
  if (!id || id < 1) {
    mostrarError('ID de producto inválido');
    return;
  }

  // Mostrar loading
  var btnGuardar = $('#formNuevoProducto button[type="submit"]');
  btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');

  $.ajax({
    url: base_path + '/materiaPrima/producto/cargarAjax',
    type: 'POST',
    data: {
      id: id,
      _token: CSRF_TOKEN
    },
    headers: {
      'X-CSRF-TOKEN': CSRF_TOKEN
    },
    success: function(response) {
      btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Producto');
      
      if (response.estado && response.codigo === 200 && response.datos) {
        var producto = response.datos;
        
        // Llenar el formulario con los datos del producto
        $('#id_producto_mp').val(producto.id);
        $('#nombre_mp').val(producto.nombre || '');
        $('#unidad_medida_mp').val(producto.unidad_medida || '');
        $('#precio_mp').val(producto.precio || '0.00');
        $('#cant_min_mp').val(producto.cant_min_deseada || '0.00');
        
        // Seleccionar el proveedor si existe
        if (producto.proveedor && producto.proveedor !== null) {
          $('#proveedor_mp').val(producto.proveedor);
        } else {
          $('#proveedor_mp').val('');
        }
        
        // Configurar modal en modo edición
        configurarModalModoEdicion();
        
        // Abrir el modal
        $('#modalNuevoProducto').modal('show');
      } else {
        mostrarError(response.mensaje || 'Error al cargar el producto');
      }
    },
    error: function(xhr, status, error) {
      btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Producto');
      var mensajeError = 'Error al cargar el producto';
      if (xhr.responseJSON && xhr.responseJSON.mensaje) {
        mensajeError = xhr.responseJSON.mensaje;
      } else if (xhr.responseText) {
        try {
          var response = JSON.parse(xhr.responseText);
          if (response.mensaje) {
            mensajeError = response.mensaje;
          }
        } catch (e) {
          mensajeError = 'Error de conexión con el servidor';
        }
      }
      mostrarError(mensajeError);
    }
  });
}

function eliminarProductoDesdeModal() {
  var id = $('#id_producto_mp').val();
  
  if (!id || id < 1 || id === '-1') {
    mostrarError('No se puede eliminar un producto nuevo');
    return;
  }

  swal({
    title: '¿Está seguro de eliminar el producto?',
    text: 'Esta acción no se puede deshacer. El producto será inactivado.',
    icon: 'warning',
    buttons: {
      cancel: {
        text: 'Cancelar',
        value: false,
        visible: true,
        className: 'btn-secondary',
        closeModal: true
      },
      confirm: {
        text: 'Sí, eliminar',
        value: true,
        visible: true,
        className: 'btn-danger',
        closeModal: true
      }
    },
    dangerMode: true
  }).then((willDelete) => {
    if (willDelete) {
      // Deshabilitar botones
      $('#formNuevoProducto button').prop('disabled', true);
      
      $.ajax({
        url: base_path + '/materiaPrima/producto/eliminarAjax',
        type: 'POST',
        data: {
          id: id,
          _token: CSRF_TOKEN
        },
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        success: function(response) {
          if (response.estado && response.codigo === 200) {
            swal({
              title: '¡Éxito!',
              text: response.mensaje || 'Producto eliminado correctamente',
              icon: 'success',
              button: 'Aceptar'
            }).then(() => {
              // Cerrar modal
              $('#modalNuevoProducto').modal('hide');
              // Recargar la página para actualizar la tabla
              location.reload();
            });
          } else {
            mostrarError(response.mensaje || 'Error al eliminar el producto');
            $('#formNuevoProducto button').prop('disabled', false);
          }
        },
        error: function(xhr, status, error) {
          var mensajeError = 'Error al eliminar el producto';
          if (xhr.responseJSON && xhr.responseJSON.mensaje) {
            mensajeError = xhr.responseJSON.mensaje;
          } else if (xhr.responseText) {
            try {
              var response = JSON.parse(xhr.responseText);
              if (response.mensaje) {
                mensajeError = response.mensaje;
              }
            } catch (e) {
              mensajeError = 'Error de conexión con el servidor';
            }
          }
          mostrarError(mensajeError);
          $('#formNuevoProducto button').prop('disabled', false);
        }
      });
    }
  });
}

function limpiarFormularioProducto() {
  $('#formNuevoProducto')[0].reset();
  $('#id_producto_mp').val('-1');
  // Remover clases de validación
  $('#formNuevoProducto .form-control').removeClass('is-invalid is-valid');
  $('#formNuevoProducto .invalid-feedback').remove();
  // Configurar modal en modo nuevo
  configurarModalModoNuevo();
}

function guardarProductoAjax() {
  // Validar campos requeridos
  var nombre = $('#nombre_mp').val().trim();
  var unidad_medida = $('#unidad_medida_mp').val().trim();
  var proveedor = $('#proveedor_mp').val();
  var precio = $('#precio_mp').val();

  // Remover validaciones previas
  $('#formNuevoProducto .form-control').removeClass('is-invalid is-valid');
  $('#formNuevoProducto .invalid-feedback').remove();

  var hayErrores = false;

  if (!nombre || nombre === '') {
    mostrarErrorCampo('#nombre_mp', 'El nombre es requerido');
    hayErrores = true;
  }

  if (!unidad_medida || unidad_medida === '') {
    mostrarErrorCampo('#unidad_medida_mp', 'La unidad de medida es requerida');
    hayErrores = true;
  }

  // Proveedor ya no es requerido

  if (!precio || precio === '' || parseFloat(precio) < 0) {
    mostrarErrorCampo('#precio_mp', 'El precio es requerido y debe ser mayor o igual a 0');
    hayErrores = true;
  }

  if (hayErrores) {
    return;
  }

  // Deshabilitar botón de guardar
  var btnGuardar = $('#formNuevoProducto button[type="submit"]');
  var id = $('#id_producto_mp').val();
  var textoGuardando = (id && id !== '-1') ? 'Actualizando...' : 'Guardando...';
  btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + textoGuardando);

  var formData = $('#formNuevoProducto').serialize();

  $.ajax({
    url: base_path + '/materiaPrima/producto/guardarAjax',
    type: 'POST',
    data: formData,
    headers: {
      'X-CSRF-TOKEN': CSRF_TOKEN
    },
    success: function(response) {
      if (response.estado && response.codigo === 200) {
        // Mostrar mensaje de éxito
        swal({
          title: '¡Éxito!',
          text: response.mensaje || 'Producto guardado correctamente',
          icon: 'success',
          button: 'Aceptar'
        }).then(() => {
          // Cerrar modal
          $('#modalNuevoProducto').modal('hide');
          // Recargar la página para actualizar la tabla
          location.reload();
        });
      } else {
        mostrarError(response.mensaje || 'Error al guardar el producto');
        var id = $('#id_producto_mp').val();
        var textoBtn = (id && id !== '-1') ? 'Actualizar Producto' : 'Guardar Producto';
        btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> ' + textoBtn);
      }
    },
    error: function(xhr, status, error) {
      var mensajeError = 'Error al guardar el producto';
      if (xhr.responseJSON && xhr.responseJSON.mensaje) {
        mensajeError = xhr.responseJSON.mensaje;
      } else if (xhr.responseText) {
        try {
          var response = JSON.parse(xhr.responseText);
          if (response.mensaje) {
            mensajeError = response.mensaje;
          }
        } catch (e) {
          mensajeError = 'Error de conexión con el servidor';
        }
      }
      mostrarError(mensajeError);
      var id = $('#id_producto_mp').val();
      var textoBtn = (id && id !== '-1') ? 'Actualizar Producto' : 'Guardar Producto';
      btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> ' + textoBtn);
    }
  });
}

function mostrarErrorCampo(selector, mensaje) {
  $(selector).addClass('is-invalid');
  $(selector).after('<div class="invalid-feedback">' + mensaje + '</div>');
}

function mostrarError(mensaje) {
  swal({
    title: 'Error',
    text: mensaje,
    icon: 'error',
    button: 'Aceptar'
  });
}

// Función clickProducto ya no se usa, se reemplazó por editarProducto
// function clickProducto(id) se mantiene por compatibilidad pero ya no se usa
function clickProducto(id){
  editarProducto(id);
}

function aplicarFiltroProveedor() {
  var proveedor = $('#filtro_proveedor').val();
  
  // Crear formulario temporal para enviar el filtro
  var form = $('<form>', {
    'method': 'POST',
    'action': base_path + '/materiaPrima/productos/filtro'
  });
  
  form.append($('<input>', {
    'type': 'hidden',
    'name': '_token',
    'value': CSRF_TOKEN
  }));
  
  form.append($('<input>', {
    'type': 'hidden',
    'name': 'proveedor',
    'value': proveedor
  }));
  
  // Agregar el formulario al body y enviarlo
  $('body').append(form);
  form.submit();
}