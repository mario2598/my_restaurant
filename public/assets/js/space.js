window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

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

/**
 * Función para detectar si el dispositivo es móvil/tablet
 */
function isMobileDevice() {
    return /iPad|Android|Tablet|Mobile|iPhone|iPod/i.test(navigator.userAgent);
}

/**
 * Función para obtener timeout apropiado según el dispositivo
 */
function getAjaxTimeout() {
    return isMobileDevice() ? 30000 : 15000;
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
        userAgent: navigator.userAgent,
        timestamp: new Date().toISOString()
    });
    
    // Mensajes de error más específicos
    if (textStatus === 'timeout') {
        showError(`La ${operation} tardó demasiado. Verifique su conexión a internet.`);
    } else if (jqXHR.status === 0) {
        showError(`Error de conexión en ${operation}. Verifique su conexión a internet.`);
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
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    return { ...defaultOptions, ...options };
}