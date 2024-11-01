var tipos = [];
var tipoSeleccionado = null;
var indexCategoriaSeleccionada = -1;

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
    var clase = "";
    if (indexCategoriaSeleccionada == -1) {
        clase = "class='active'";
    }
    texto = texto + `<li ${clase} onclick="seleccionarCategorias()">
                        <a href="#" ><span>Categorías</span></a>
                    </li> `;
    for (var index in tipos) {
        var tipo = tipos[index];
        clase = "";
        if (indexCategoriaSeleccionada == index) {
            clase = "class='active'";
        }
        texto = texto + `<li ${clase} onclick="seleccionarTipo(${index})" style="cursor:pointer;">
                             <a href="#" ><i class="${tipo.logo}"></i> 
                             <span>${tipo.categoria}</span></a>
                        </li> `;
    }

    $('#categoriasNav').html(texto);
}

function seleccionarTipo(index) {
    indexCategoriaSeleccionada = index;
    tipoSeleccionado = tipos[index];
    $("#lblNombreCategiriaSeleccionada").html(tipoSeleccionado.categoria);
    generarHTMLProductos();
    generarHTMLTipos();
}

function seleccionarCategorias() {
    indexCategoriaSeleccionada = -1;
    generarHTMLCategorias();
    $("#lblNombreCategiriaSeleccionada").html("Todas las categorías");
}

function generarHTMLCategorias() {
    var texto = "";

    for (var index in tipos) {
        var tipo = tipos[index];
        texto = texto + `<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12" 
         onclick="seleccionarTipo(${index})"
        style="padding: 10px;">
            <div class="card-mario" style="padding: 10px;">
                    <img class="img-responsive thumbnail imagen-cuadrada"
                        src="${tipo.url_imagen}"
                        alt="${ tipo.categoria}">
                </a>
                <p style="text-align: center;"> <small>${tipo.categoria}</small> <br>
                </p>
            </div>
        </div>`;
    }

    $('#aniimated-thumbnials').html(texto);
}

function generarHTMLProductos() {
    var texto = "";

    for (var index in tipoSeleccionado.productos) {
        var prod = tipoSeleccionado.productos[index];
        texto = texto + `<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12" style="padding: 10px;">
            <div class="card-mario" style="padding: 10px;">
                <a href="${prod.url_imagen}"
                    data-sub-html="${prod.nombre} | ${ currencyCRFormat(prod.precio)} | ${ prod.descripcion} ">
                    <img class="img-responsive thumbnail imagen-cuadrada"
                        src="${prod.url_imagen}"
                        alt="${ prod.descripcion}">
                </a>
                <p> <small>${prod.nombre}</small> <br>
                    <strong> ${ currencyCRFormat(prod.precio)}</strong>
                </p>
            </div>
        </div>`;
    }

    $('#aniimated-thumbnials').html(texto);
    cargarScriptsJavaScript();

}


function cargarScriptsJavaScript() {
    $('#aniimated-thumbnials').data('lightGallery').destroy(true);

    $('#aniimated-thumbnials').lightGallery({
        thumbnail: true,
        selector: 'a'
    });
}
