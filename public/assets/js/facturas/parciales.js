window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var ordenSeleccionada = 0;

$(document).ready(function () {
  $("#btn_buscar").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_generico tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});


function initialice() {
}



function tickete(id) {
  $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
  document.getElementById('btn-pdf').click();
}

function ticketeParcial(id) {
  $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/ruta/parcial/${id}`);
  document.getElementById('btn-pdf').click();
}

function ticketePagoParcial(id) {
  $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/ruta/parcial/pago/${id}`);
  document.getElementById('btn-pdf').click();
}


function abrirPagos(id){
  ordenSeleccionada = id;
  $.ajax({
    url: `${base_path}/facturas/parciales/cargarPagos`,
    type: 'post',
    data: {_token: CSRF_TOKEN,orden:id}
  }).done(function( res ) {
    $('#tbody_pagos').html(res);
    $('#mdl_pagos').modal('show');
  }).fail(function (jqXHR, textStatus, errorThrown){
   
    iziToast.error({
      title: 'Error!',
      message: 'Algo salio mal, reintentalo..',
      position: 'topRight'
    });
  });
}
function cerrarModal(id){
  $('#mdl_pagos').modal('hide');
}

function crearPago(){
  if(ordenSeleccionada < 1){
    iziToast.error({
      title: 'Error!',
      message: 'Debe seleccionar una orden',
      position: 'topRight'
    });
    return;
  }
  let mtoSinpe = $('#monto_sinpe').val();
  let mtoEfectivo = $('#monto_efectivo').val();
  let mtoTarjeta = $('#monto_tarjeta').val();

  $.ajax({
    url: `${base_path}/facturas/parciales/crearPago`,
    type: 'post',
    dataType: "json",
    data: {_token: CSRF_TOKEN,
      orden:ordenSeleccionada,
      sinpe : mtoSinpe,
      efectivo :mtoEfectivo ,
      tarjeta :mtoTarjeta}
  }).done(function (res) {
    if (!res['estado']) {
        iziToast.error({
          title: 'Crear Pago',
          message: res['mensaje'],
          position: 'topRight'
        });
    } else {
      iziToast.success({
        title: 'Crear Pago',
        message: 'Se creo el pago correctamente',
        position: 'topRight'
      });
      $('#mdl_pagos').modal('hide');
      abrirPagos(ordenSeleccionada);
    }
  }).fail(function (jqXHR, textStatus, errorThrown) {
    iziToast.error({
      title: 'Error!',
      message: 'Algo salio mal, reintentalo..',
      position: 'topRight'
    });
   // window.location.reload(true);
  });
}

