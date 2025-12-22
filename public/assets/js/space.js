// Variables globales
var CSRF_TOKEN = '';
var currencyFormat, currencyCRFormat, amountFormat;

// Inicializar CSRF_TOKEN cuando jQuery esté disponible
$(document).ready(function() {
    CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
});

var currencyFormat = amount => {
    return dollarUSLocale.format(parseFloat(amount));
};

var currencyCRFormat = amount => {
    return "CRC " + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,");
};

var amountFormat = amount => {
    return "CRC " + parseFloat(amount.replace("CRC ", "")).toFixed(2);
};

function initialice() {
    // Función de inicialización
}

function editarGastoUsuario(id) {
    $('#idGastoEditar').val(id);
    $('#formGastoEditar').submit();
}

function eliminarGastoUsuario(id) {

    swal({
            title: 'Confirmar?',
            text: 'Deseas eliminar este gasto ? ',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $('#idGastoEliminar').val(id);
                $('#formGastoEliminar').submit();
            } else {
                swal('No se elimino el gasto!');
            }
        });
}

function eliminarGastoAdmin(id) {

    swal({
            title: 'Confirmar?',
            text: 'Deseas eliminar este gasto ? ',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $('#idGastoAdminEliminar').val(id);
                $('#formGastoAdminEliminar').submit();
            } else {
                swal('No se elimino el gasto!');
            }
        });
}

function rechazarGastoUsuario(id) {

    swal({
            title: 'Confirmar?',
            text: 'Deseas rechazar este gasto ? ',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $('#idGastoRechazar').val(id);
                $('#formGastoRechazar').submit();
            } else {
                swal('No se rechazo el gasto!');
            }
        });
}

function confirmarGasto(id, node, total) {
    parent = $(node).parent().parent();
    swal({
            title: 'Confirmar?',
            text: 'Deseas confirmar este gasto por CRC ' + total,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {

                $.ajax({
                    url: `${base_path}/confirmarGasto`,
                    type: 'post',
                    data: {
                        _token: CSRF_TOKEN,
                        gasto: id
                    }
                }).done(function (confirmado) {
                    if (confirmado == "500") {
                        iziToast.success({
                            title: 'Confirmado!',
                            message: 'Se confirmo el gasto correctamente!',
                            position: 'topRight'
                        });
                        $(parent).remove();
                    } else if (confirmado == "-1") {
                        iziToast.error({
                            title: 'Error!',
                            message: 'No tienes permisos para realizar esta acción!',
                            position: 'topRight'
                        });

                    } else if (confirmado == "404") {
                        iziToast.error({
                            title: 'Error!',
                            message: 'No se encontro el comprobante!',
                            position: 'topRight'
                        });

                    } else if (confirmado == "400") {
                        iziToast.error({
                            title: 'Error!',
                            message: 'Algo salio mal, reintentalo..',
                            position: 'topRight'
                        });

                    }
                    window.location.href = `${base_path}/gastos/pendientes`;
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    iziToast.error({
                        title: 'Error!',
                        message: 'Algo salio mal, reintentalo..',
                        position: 'topRight'
                    });
                    window.location.href = `${base_path}/gastos/pendientes`;
                });

            } else {
                swal('No se confirmo el gasto!');
            }
        });

}

function clickGasto(id) {
    $('#idGasto').val(id);
    $('#formGasto').submit();
}

function clickIngreso(id) {
    $('#idIngreso').val(id);
    $('#formIngreso').submit();
}

function verFotoComprobanteGasto(id) {
    $.ajax({
        url: `${base_path}/gasto/fotoBase64`,
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            gasto: id
        }
    }).done(function (base64) {
        if (base64 == "-1") {
            iziToast.error({
                title: 'Error!',
                message: 'No tienes permisos para realizar esta acción!',
                position: 'topRight'
            });

        } else {
            let data = "data:image/jpg;base64," + base64;
            let w = window.open('about:blank');
            let image = new Image();
            image.src = data;
            setTimeout(function () {
                w.document.write(image.outerHTML);
            }, 0);
        }

    }).fail(function (jqXHR, textStatus, errorThrown) {
        iziToast.error({
            title: 'Error!',
            message: 'Algo salio mal, reintentalo..',
            position: 'topRight'
        });
    });

}

function setError(titulo, detalle) {
    iziToast.error({
        title: titulo,
        message: detalle,
        position: 'topRight'
    });
}

function setSuccess(titulo, detalle) {
    iziToast.success({
        title: titulo,
        message: detalle,
        position: 'topRight'
    });
}

function cancelarMovimiento(id) {
    let detalle = $('#detalle_movimiento_generado').val();
    $('#idMovimientoCancelar').val(id);
    $('#detalleMovimientoCancelar').val(detalle);
    $('#formCancelarMovimiento').submit();
}

function goMovimientoInv(mov) {
    $("#idMov").val(mov);
    $("#formVerMovimiento").submit();
}



function soundNewOrder() {
    var audio = new Audio(`${base_path}/assets/sounds/not.mp3`);
    audio.play();
}

function soundClic() {
    var audio = new Audio(`${base_path}/assets/sounds/clic.mp3`);
    audio.play();
}

function showError(error){
    iziToast.error({
        title: 'Error!',
        message: error,
        position: 'topRight'
    });
}

function showSuccess(msj){
    iziToast.success({
        title: 'Exito!',
        message: msj,
        position: 'topRight'
    });
}


function showInfo(msj){
    iziToast.info({
        title: 'Información!',
        message: msj,
        position: 'topRight'
    });
}

/**
 * Función para detectar si el dispositivo es móvil/tablet
 */
function isMobileDevice() {
    return /iPad|Android|Tablet|Mobile|iPhone|iPod/i.test(navigator.userAgent);
}

/**
 * Función para detectar específicamente si es un iPad
 */
function isIPad() {
    // Detectar iPad específicamente (incluye iPad Pro, iPad Air, etc.)
    return /iPad|Macintosh.*Safari.*Mobile/i.test(navigator.userAgent) || 
           (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
}

/**
 * Función para detectar si es un dispositivo iOS (iPhone, iPad, iPod)
 */
function isIOSDevice() {
    return /iPad|iPhone|iPod/i.test(navigator.userAgent) || 
           (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
}

/**
 * Función para obtener timeout apropiado según el dispositivo
 */
function getAjaxTimeout() {
    if (isIPad()) {
        return 45000; // Timeout más largo para iPads
    } else if (isMobileDevice()) {
        return 30000; // Timeout para otros dispositivos móviles
    } else {
        return 15000; // Timeout para PC
    }
}

/**
 * Función para obtener headers específicos según el dispositivo
 */
function getDeviceSpecificHeaders() {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest'
    };
    
    if (isIPad()) {
        headers['X-Device-Type'] = 'ipad';
        headers['X-Platform'] = 'ios';
        headers['X-Touch-Support'] = 'true';
        headers['X-Viewport-Width'] = window.innerWidth;
        headers['X-Viewport-Height'] = window.innerHeight;
        headers['X-Pixel-Ratio'] = window.devicePixelRatio || 1;
    } else if (isIOSDevice()) {
        headers['X-Device-Type'] = 'ios';
        headers['X-Platform'] = 'ios';
        headers['X-Touch-Support'] = 'true';
    } else if (isMobileDevice()) {
        headers['X-Device-Type'] = 'mobile';
        headers['X-Platform'] = 'android';
    } else {
        headers['X-Device-Type'] = 'desktop';
        headers['X-Platform'] = 'web';
    }
    
    return headers;
}

/**
 * Función para manejar errores AJAX con mensajes específicos
 */
function handleAjaxError(jqXHR, textStatus, errorThrown, operation = 'operación') {
    // Log detallado del error para debugging
    console.error(`Error en ${operation}:`, {
        status: jqXHR.status,
        statusText: jqXHR.statusText,
        responseText: jqXHR.responseText,
        textStatus: textStatus,
        errorThrown: errorThrown,
        isMobile: isMobileDevice(),
        isIPad: isIPad(),
        isIOS: isIOSDevice(),
        userAgent: navigator.userAgent,
        platform: navigator.platform,
        maxTouchPoints: navigator.maxTouchPoints,
        timestamp: new Date().toISOString()
    });
    
    // Mensajes de error más específicos
    if (textStatus === 'timeout') {
        if (isIPad()) {
            showError(`La ${operation} tardó demasiado en el iPad. Verifique su conexión WiFi.`);
        } else {
            showError(`La ${operation} tardó demasiado. Verifique su conexión a internet.`);
        }
    } else if (jqXHR.status === 0) {
        if (isIPad()) {
            showError(`Error de conexión en iPad. Verifique su conexión WiFi y reinicie Safari si es necesario.`);
        } else {
            showError(`Error de conexión en ${operation}. Verifique su conexión a internet.`);
        }
    } else if (jqXHR.status === 401) {
        showError("Sesión expirada. Por favor, inicie sesión nuevamente.");
    } else if (jqXHR.status === 403) {
        showError("No tiene permisos para realizar esta operación.");
    } else if (jqXHR.status === 404) {
        showError("Recurso no encontrado. Contacte al administrador.");
    } else if (jqXHR.status === 500) {
        showError("Error interno del servidor. Contacte al administrador.");
    } else if (jqXHR.status === 503) {
        showError("Servicio temporalmente no disponible. Intente más tarde.");
    } else {
        showError(`Error en ${operation}: ${jqXHR.status} - ${textStatus}`);
    }
}

/**
 * Función para configurar AJAX con opciones optimizadas para móviles
 */
function configureAjaxForDevice(options = {}) {
    const defaultOptions = {
        timeout: getAjaxTimeout(),
        cache: false,
        headers: getDeviceSpecificHeaders()
    };
    
    return { ...defaultOptions, ...options };
}

// Inicializar cuando la ventana se cargue completamente
window.addEventListener("load", initialice, false);