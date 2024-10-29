window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');



function initialice() {


}

function filtrarGastosPendientesAdmin(value){
  $.ajax({
    url: `${base_path}/filtrarGastosPendientes`,
    type: 'post',
    data: {_token: CSRF_TOKEN,texto:value}
  }).done(function( filtro ) {
    $('#contenedor_gastos_sin_aprobar').html(filtro);

  }).fail(function (jqXHR, textStatus, errorThrown){
    iziToast.error({
      title: 'Error!',
      message: 'Algo salio mal, reintentalo..',
      position: 'topRight'
    });
  });
}

function filtrarGastosPendientesUsuario(value){
  $.ajax({
    url: `${base_path}/filtrarGastosPendientesUsuario`,
    type: 'post',
    data: {_token: CSRF_TOKEN,texto:value}
  }).done(function( filtro ) {
    $('#contenedor_gastos_sin_aprobar').html(filtro);

  }).fail(function (jqXHR, textStatus, errorThrown){
    iziToast.error({
      title: 'Error!',
      message: 'Algo salio mal, reintentalo..',
      position: 'topRight'
    });
  });
}

