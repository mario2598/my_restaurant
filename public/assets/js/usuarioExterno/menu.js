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
                        <a href="#"><i class="fas fa-th"></i><span>Todas las categorías</span></a>
                    </li> `;
    for (var index in tipos) {
        var tipo = tipos[index];
        clase = "";
        if (indexCategoriaSeleccionada == index) {
            clase = "class='active'";
        }
        var icono = tipo.logo || "fas fa-utensils";
        texto = texto + `<li ${clase} onclick="seleccionarTipo(${index})" style="cursor:pointer;">
                             <a href="#"><i class="${icono}"></i> 
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
    var defaultImage = typeof DEFAULT_IMAGE_URL !== 'undefined' ? DEFAULT_IMAGE_URL : (base_path + '/assets/images/default-logo.png');

    for (var index in tipos) {
        var tipo = tipos[index];
        var urlImagen = tipo.url_imagen || '';
        // Validar que la URL no sea solo 'storage' o vacía o mal formada
        if (!urlImagen || urlImagen.trim() === '' || 
            urlImagen === base_path + '/storage' || 
            urlImagen === '/storage' ||
            (urlImagen.includes('/storage') && !urlImagen.match(/\/storage\/.+/))) {
            urlImagen = defaultImage;
        }
        
        texto = texto + `<div class="col-lg-3 col-md-4 col-sm-6 col-6" 
         onclick="seleccionarTipo(${index})">
            <div class="card-mario">
                <div class="card-image-wrapper">
                    <img class="img-responsive thumbnail imagen-cuadrada"
                        src="${urlImagen}"
                        alt="${tipo.categoria || 'Categoría'}"
                        loading="lazy"
                        onerror="this.src='${defaultImage}'; this.onerror=null;">
                </div>
                <div class="card-content">
                    <p>
                        <small>${tipo.categoria || 'Sin nombre'}</small>
                    </p>
                </div>
            </div>
        </div>`;
    }

    $('#aniimated-thumbnials').html(texto);
}

function generarHTMLProductos() {
    var texto = "";
    var defaultImage = typeof DEFAULT_IMAGE_URL !== 'undefined' ? DEFAULT_IMAGE_URL : (base_path + '/assets/images/default-logo.png');

    for (var index in tipoSeleccionado.productos) {
        var prod = tipoSeleccionado.productos[index];
        var descripcion = prod.descripcion || '';
        var nombre = prod.nombre || 'Producto';
        var precio = prod.precio || 0;
        var urlImagen = prod.url_imagen || '';
        
        // Validar que la URL no sea solo 'storage' o vacía o mal formada
        if (!urlImagen || urlImagen.trim() === '' || 
            urlImagen === base_path + '/storage' || 
            urlImagen === '/storage' ||
            (urlImagen.includes('/storage') && !urlImagen.match(/\/storage\/.+/))) {
            urlImagen = defaultImage;
        }
        
        // Escapar HTML para evitar problemas
        var nombreEscapado = $('<div>').text(nombre).html();
        var descripcionEscapada = $('<div>').text(descripcion).html();
        
        texto = texto + `<div class="col-lg-3 col-md-4 col-sm-6 col-6">
            <div class="card-mario">
                <div class="card-image-wrapper">
                    <a href="${urlImagen}"
                        data-sub-html="${nombreEscapado} | ${currencyCRFormat(precio)} | ${descripcionEscapada}">
                        <img class="img-responsive thumbnail imagen-cuadrada"
                            src="${urlImagen}"
                            alt="${nombreEscapado}"
                            loading="lazy"
                            onerror="this.src='${defaultImage}'; this.onerror=null;">
                    </a>
                </div>
                <div class="card-content">
                    <p>
                        <small>${nombreEscapado}</small>
                        <strong>${currencyCRFormat(precio)}</strong>
                    </p>
                </div>
            </div>
        </div>`;
    }

    $('#aniimated-thumbnials').html(texto);
    cargarScriptsJavaScript();

}


function cargarScriptsJavaScript() {
    // Destruir lightGallery si existe de forma segura
    try {
        var lgInstance = $('#aniimated-thumbnials').data('lightGallery');
        if (lgInstance) {
            // Verificar si está en fullscreen antes de destruir
            try {
                if (document.fullscreenElement || document.webkitFullscreenElement || 
                    document.mozFullScreenElement || document.msFullscreenElement) {
                    // Salir de fullscreen primero si está activo
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(function() {});
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen().catch(function() {});
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen().catch(function() {});
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen().catch(function() {});
                    }
                }
            } catch (e) {
                // Ignorar errores de fullscreen
            }
            
            // Esperar un momento antes de destruir
            setTimeout(function() {
                try {
                    lgInstance.destroy(true);
                } catch (e) {
                    // Si falla la destrucción, simplemente remover los datos
                    $('#aniimated-thumbnials').removeData('lightGallery');
                }
            }, 100);
        }
    } catch (e) {
        // Si hay algún error, simplemente limpiar los datos
        $('#aniimated-thumbnials').removeData('lightGallery');
    }

    // Inicializar lightGallery con un pequeño delay para asegurar que el DOM esté listo
    setTimeout(function() {
        try {
            $('#aniimated-thumbnials').lightGallery({
                thumbnail: true,
                selector: 'a',
                mode: 'lg-fade',
                speed: 300,
                enableSwipe: true,
                enableDrag: true
            });
        } catch (e) {
            console.log('Error al inicializar lightGallery:', e);
        }
    }, 200);
}
