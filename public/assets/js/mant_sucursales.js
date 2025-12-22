window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var sucursalGestion = null;
$(document).ready(function () {
  $("#input_buscar_sucursal").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_sucursal tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

function initialice() {
  var t = document.getElementById('mdl_sucursal_ipt_descripcion');
  t.addEventListener('input', function () { // 
    if (this.value.length > 50)
      this.value = this.value.slice(0, 50);
  });
}

function cargarSucursal(idSucursal) {

  $.ajax({
    url: `${base_path}/mant/sucursales/cargar`,
    type: 'post',
    dataType: "json",
    data: {
      _token: CSRF_TOKEN,
      idSucursal: idSucursal
    }
  }).done(function (response) {
    if (!response['estado']) {
      showError(response['mensaje']);
      return;
    };
    cargarHtmlSucursal(response['datos']);
  }).fail(function (jqXHR, textStatus, errorThrown) {
    showError("Algo salió mal");
  });

}

function cargarHtmlSucursal(sucursal) {
  sucursalGestion = sucursal;

  // Llenar los campos del modal con los valores recibidos
  $('#mdl_sucursal_ipt_descripcion').val(sucursal.descripcion);
  $('#mdl_sucursal_ipt_nombre_factura').val(sucursal.nombre_factura);
  $('#mdl_sucursal_ipt_cedula_factura').val(sucursal.cedula_factura);
  $('#mdl_sucursal_ipt_correo_factura').val(sucursal.correo_factura);
  $('#mdl_sucursal_ipt_id').val(sucursal.id);
  $('#tipo_identificacion_emisor').val(sucursal.tipo_identificacion_emisor);

  // Manejar el estado (A = Activo, I = Inactivo)
  if (sucursal.estado === 'A') {
    $('#mdl_sucursal_chk_activa').prop('checked', true);
  } else {
    $('#mdl_sucursal_chk_activa').prop('checked', false);
  }
  $('#mdl_sucursal').modal('show');
}

function editarSucursal(idSucursal) {
  cargarSucursal(idSucursal);
}


function cerrarModalSucursal() {
  $('#mdl_sucursal').modal('hide');
}

function nuevaSucursal() {
  $('#mdl_sucursal_ipt_descripcion').val("");
  $('#mdl_sucursal_ipt_id').val('-1');
  $('#mdl_sucursal_ipt_nombre_factura').val("");
  $('#mdl_sucursal_ipt_cedula_factura').val("");
  $('#mdl_sucursal_ipt_correo_factura').val("");
  $('#tipo_identificacion_emisor').val("");
  $('#mdl_sucursal_chk_activa').prop('checked', true);
  $('#mdl_sucursal').modal('show');
}
