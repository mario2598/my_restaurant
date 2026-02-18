window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

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


function eliminarProdcutoDeMenu(id) {
  swal({
    title: 'Seguro de remover el producto del menú?',
    text: 'Requieres permisos de administrados para realizar la solicitud!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#producto_menu_eliminar').val(id);
        $('#form_eliminar_menu').submit();

      } else {
        swal.close();
      }
    });
}

var idMenuGestion = null;

function cambiarComandera(idProducto, dscComanda, dscProducto) {
  idMenuGestion = idProducto;
  $('#lblProdCambioComanda').html(dscProducto);
  $('#lblComdAsigCambioComanda').html("Comanda asignada : " + dscComanda ?? 'Comanda General');
  $('#mdl_cambio_comanda').modal('show');
}

function cambiarComanda() {
  var idComanda = $('#comanda_cambio_select').val();

  // Validar idMenuGestion e idComanda
  if (!idMenuGestion || !idComanda) {
    showError('Debe seleccionar un menú y una comanda válida.');
    return;
  }

  $.ajax({
    url: `${base_path}/menu/menus/cambiarComanda`,
    type: 'post',
    data: {
      _token: CSRF_TOKEN,
      idMenu: idMenuGestion,
      idComandaNueva: idComanda,
      idSucursal: $('#sucursal').val()
    }
  }).done(function (response) {
    if (!response['estado']) {
      showError(response['mensaje']);
      return;
    }

    showSuccess('Se actualizó la comanda asignada al producto.');

    // Hacer submit al formulario
    $('#form_cargar_menu').submit();

  }).fail(function (jqXHR, textStatus, errorThrown) {
    setError('Recargar Comandas', 'Algo salió mal..');
  });
}

// Variables para gestión de horarios
var idPmXSucursalGestion = null;
var contadorHorarios = 0;
var diasSemana = [
  { valor: 1, nombre: 'Lunes' },
  { valor: 2, nombre: 'Martes' },
  { valor: 3, nombre: 'Miércoles' },
  { valor: 4, nombre: 'Jueves' },
  { valor: 5, nombre: 'Viernes' },
  { valor: 6, nombre: 'Sábado' },
  { valor: 7, nombre: 'Domingo' }
];

function gestionarHorarios(idPmXSucursal, nombreProducto) {
  idPmXSucursalGestion = idPmXSucursal;
  $('#lbl_producto_horario').text(nombreProducto);
  $('#tbody_horarios').empty();
  contadorHorarios = 0;
  
  // Cargar horarios existentes
  cargarHorarios(idPmXSucursal);
  
  $('#mdl_horarios').modal('show');
}

function cargarHorarios(idPmXSucursal) {
  $.ajax({
    url: `${base_path}/menu/menus/obtenerHorarios`,
    type: 'post',
    data: {
      _token: CSRF_TOKEN,
      idPmXSucursal: idPmXSucursal
    }
  }).done(function (response) {
    if (response['estado']) {
      var horarios = response['datos'] || [];
      if (horarios.length > 0) {
        horarios.forEach(function(horario) {
          agregarFilaHorario(horario);
        });
      }
      // Si no hay horarios, no agregar ninguna fila
    } else {
      showError(response['mensaje']);
    }
  }).fail(function (jqXHR, textStatus, errorThrown) {
    showError('Error al cargar los horarios.');
  });
}

function agregarFilaHorario(horario = null) {
  contadorHorarios++;
  var idFila = 'horario_' + contadorHorarios;
  var idHorario = horario ? horario.id : null;
  
  var selectDias = '<select class="form-control form-control-sm dia_semana" data-id="' + idHorario + '">';
  diasSemana.forEach(function(dia) {
    var selected = (horario && horario.dia_semana == dia.valor) ? 'selected' : '';
    selectDias += '<option value="' + dia.valor + '" ' + selected + '>' + dia.nombre + '</option>';
  });
  selectDias += '</select>';
  
  var horaInicio = horario ? horario.hora_inicio : '';
  var horaFin = horario ? horario.hora_fin : '';
  var activo = horario ? (horario.activo == 1 ? 'checked' : '') : 'checked';
  
  var fila = '<tr id="' + idFila + '">' +
    '<td>' + selectDias + '</td>' +
    '<td><input type="time" class="form-control form-control-sm hora_inicio" value="' + horaInicio + '" data-id="' + idHorario + '"></td>' +
    '<td><input type="time" class="form-control form-control-sm hora_fin" value="' + horaFin + '" data-id="' + idHorario + '"></td>' +
    '<td class="text-center"><input type="checkbox" class="activo" ' + activo + ' data-id="' + idHorario + '"></td>' +
    '<td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFilaHorario(\'' + idFila + '\', ' + (idHorario || 'null') + ')"><i class="fas fa-trash"></i></button></td>' +
    '</tr>';
  
  $('#tbody_horarios').append(fila);
}

function eliminarFilaHorario(idFila, idHorario) {
  if (idHorario) {
    // Si tiene ID, eliminar del servidor
    $.ajax({
      url: `${base_path}/menu/menus/eliminarHorario`,
      type: 'post',
      data: {
        _token: CSRF_TOKEN,
        idHorario: idHorario
      }
    }).done(function (response) {
      if (response['estado']) {
        $('#' + idFila).remove();
        showSuccess('Horario eliminado correctamente.');
      } else {
        showError(response['mensaje']);
      }
    }).fail(function () {
      showError('Error al eliminar el horario.');
    });
  } else {
    // Si no tiene ID, solo eliminar la fila
    $('#' + idFila).remove();
  }
}

function guardarHorarios() {
  var horarios = [];
  var hayErrores = false;
  
  $('#tbody_horarios tr').each(function() {
    var $fila = $(this);
    var diaSemana = $fila.find('.dia_semana').val();
    var horaInicio = $fila.find('.hora_inicio').val();
    var horaFin = $fila.find('.hora_fin').val();
    var activo = $fila.find('.activo').is(':checked') ? 1 : 0;
    var idHorario = $fila.find('.dia_semana').data('id');
    
    // Validar que tenga hora inicio y fin
    if (horaInicio && horaFin) {
      if (horaInicio >= horaFin) {
        showError('La hora de inicio debe ser menor que la hora de fin.');
        hayErrores = true;
        return false;
      }
      
      horarios.push({
        id: idHorario,
        dia_semana: diaSemana,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
        activo: activo
      });
    } else if (horaInicio || horaFin) {
      showError('Debe completar ambas horas (inicio y fin) o dejar ambas vacías.');
      hayErrores = true;
      return false;
    }
  });
  
  if (hayErrores) {
    return;
  }
  
  // Si no hay horarios, eliminar todos los existentes
  if (horarios.length === 0) {
    // Eliminar todos los horarios existentes
    $.ajax({
      url: `${base_path}/menu/menus/eliminarTodosHorarios`,
      type: 'post',
      data: {
        _token: CSRF_TOKEN,
        idPmXSucursal: idPmXSucursalGestion
      }
    }).done(function (response) {
      if (response['estado']) {
        showSuccess('Horarios actualizados correctamente. El producto se mostrará siempre.');
        // Recargar los horarios desde el servidor para actualizar la vista
        $('#tbody_horarios').empty();
        contadorHorarios = 0;
      } else {
        showError(response['mensaje']);
      }
    }).fail(function () {
      showError('Error al guardar los horarios.');
    });
    return;
  }
  
  // Guardar horarios
  $.ajax({
    url: `${base_path}/menu/menus/guardarHorarios`,
    type: 'post',
    data: {
      _token: CSRF_TOKEN,
      idPmXSucursal: idPmXSucursalGestion,
      horarios: horarios
    }
  }).done(function (response) {
    if (response['estado']) {
      showSuccess('Horarios guardados correctamente. Puede seguir agregando más horarios.');
      // Recargar los horarios desde el servidor para actualizar los IDs
      $('#tbody_horarios').empty();
      contadorHorarios = 0;
      cargarHorarios(idPmXSucursalGestion);
    } else {
      showError(response['mensaje']);
    }
  }).fail(function () {
    showError('Error al guardar los horarios.');
  });
}

function guardarHorariosYCerrar() {
  guardarHorarios();
  // Esperar un momento para que se guarde y luego cerrar
  setTimeout(function() {
    $('#mdl_horarios').modal('hide');
    // Recargar la página para actualizar la vista principal
    $('#form_cargar_menu').submit();
  }, 1000);
}


