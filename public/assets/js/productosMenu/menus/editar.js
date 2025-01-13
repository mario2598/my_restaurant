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


