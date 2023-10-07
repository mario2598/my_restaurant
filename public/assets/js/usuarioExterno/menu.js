var tipos = [];
var tipoSeleccionado = null;

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    cargarTiposGeneral();
});


function cargarTiposGeneral() {
    $.ajax({
        url: `${base_path}/usuarioExterno/menu/cargarTiposGeneral`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(res['mensaje']);
            return;
        }
        tipos = response['datos'];
        generarHTMLTipos();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}


function generarHTMLTipos() {
    var texto = "";
    for (var index in tipos) {
        var tipo = tipos[index];
        texto = texto + `<li class="nav-item" onclick="seleccionarTipo(${index})" style="cursor:pointer;">
            <a class="nav-link"><i class="${tipo.logo}"></i> <span>${tipo.categoria}</span></a>
        </li>`;
    }

    $('#navMnu').html(texto);
}

function seleccionarTipo(index) {
    tipoSeleccionado = tipos[index];

    generarHTMLProductos();
}

function generarHTMLProductos() {
    var texto = "";

    for (var index in tipoSeleccionado.productos) {
        var prod = tipoSeleccionado.productos[index];
        texto = texto + `<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12" style="padding: 10px;">
            <div class="card-mario" style="padding: 10px;">
                <a href="${prod.url_imagen}"
                    data-sub-html="${prod.nombre} | CRC  ${ currencyCRFormat(prod.precio)} | ${ prod.descripcion} ">
                    <img class="img-responsive thumbnail imagen-cuadrada"
                        src="${prod.url_imagen}"
                        alt="${ prod.descripcion}">
                </a>
                <p> <small>${prod.nombre}</small> <br>
                    <strong>CRC  ${ currencyCRFormat(prod.precio)}</strong>
                </p>
            </div>
        </div>`;
    }
    $('#aniimated-thumbnials').html(texto);
}
