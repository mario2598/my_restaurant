window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    $("#btn_buscar_gasto").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_generico tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});


function initialice() {

}

function filtrarGastosPendientesAdmin(value) {
    $.ajax({
        url: `${base_path}/filtrarGastosPendientes`,
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            texto: value
        }
    }).done(function (filtro) {
        $('#contenedor_gastos_sin_aprobar').html(filtro);

    }).fail(function (jqXHR, textStatus, errorThrown) {
        iziToast.error({
            title: 'Error!',
            message: 'Algo salio mal, reintentalo..',
            position: 'topRight'
        });
    });
}

function filtrarGastosPendientesUsuario(value) {
    $.ajax({
        url: `${base_path}/filtrarGastosPendientesUsuario`,
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            texto: value
        }
    }).done(function (filtro) {
        $('#contenedor_gastos_sin_aprobar').html(filtro);

    }).fail(function (jqXHR, textStatus, errorThrown) {
        iziToast.error({
            title: 'Error!',
            message: 'Algo salio mal, reintentalo..',
            position: 'topRight'
        });
    });
}


function clickGasto(id) {

    var form = document.createElement('form');
    form.setAttribute('method', 'post');
    form.setAttribute('action', `${base_path}/gastos/gasto`);
    form.style.display = 'none';

    // Agregamos el token CSRF
    var csrfField = document.createElement('input');
    csrfField.setAttribute('type', 'hidden');
    csrfField.setAttribute('name', '_token');
    csrfField.setAttribute('value', CSRF_TOKEN);
    form.appendChild(csrfField);

    // Agregamos el campo idUsuarioEditar
    var idField = document.createElement('input');
    idField.setAttribute('type', 'hidden');
    idField.setAttribute('name', 'idGasto');
    idField.setAttribute('value', id);
    form.appendChild(idField);

    // Agregamos el formulario al cuerpo del documento
    document.body.appendChild(form);

    // Enviamos el formulario
    form.submit();
}

