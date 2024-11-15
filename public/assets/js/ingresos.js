window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() { }

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-ventasRel tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});



function rechazarIngresoGasto(gasto, ingreso) {

    swal({
        title: 'Confirmar?',
        text: 'Deseas rechazar este gasto ? ',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $('#idIngreso').val(ingreso);
                $('#idIngresoGastoRechazar').val(gasto);
                $('#formIngresoGastoRechazar').submit();
            } else {
                swal('No se rechazo el gasto!');
            }
        });
}

function eliminarIngresoAdmin(ingreso) {

    swal({
        title: 'Eliminar Ingreso?',
        text: 'Se eliminaran los gastos relacionados. ',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $('#idIngresoEliminar').val(ingreso);
                $('#formEliminarIngreso').submit();
            } else {
                swal('No se elimino el ingreso!');
            }
        });
}



function confirmarIngreso(ingreso) {

    var pago_sinpe = parseFloat($('#monto_sinpe').val()); // Supongo que txt-sinpe es el campo para el pago con SINPE
    var pago_tarjeta = parseFloat($('#monto_tarjeta').val()); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
    var pago_efectivo = parseFloat($('#monto_efectivo').val()); // Supongo que txt-efectivo es el campo para el pago en efectivo

    if (isNaN(pago_tarjeta)) {
        $('#monto_tarjeta').val("0");
        pago_tarjeta = 0;
    }

    if (isNaN(pago_efectivo)) {
        $('#monto_efectivo').val("0");
        pago_efectivo = 0;
    }
    if (isNaN(pago_sinpe)) {
        $('#monto_sinpe').val("0");
        pago_sinpe = 0;
    }

    swal({
        title: 'Confirmar Ingreso?',
        text: 'Los montos indicados ya no podrán ser modificados.',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: `${base_path}/ingresos/aprobar`,
                    type: 'post',
                    dataType: "json",
                    data: {
                        _token: CSRF_TOKEN,
                        idIngreso: ingreso,
                        pago_tarjeta: pago_tarjeta,
                        pago_efectivo: pago_efectivo,
                        pago_sinpe: pago_sinpe
                    }
                }).done(function (response) {
                    if (!response['estado']) {
                        showError(res['mensaje']);
                        return;
                    }
                    showSuccess("Se Aprobó el ingreso");
                    window.location.href = document.referrer;
                    window.location.reload(true);

                }).fail(function (jqXHR, textStatus, errorThrown) {
                    showError("Algo salió mal");
                });
            } else {
                swal('No se aprobo el ingreso!');
            }
        });
}


function rechazarIngreso(ingreso) {

    swal({
        title: 'Rechazar Ingreso?',
        text: 'Se rechazaran los gastos relacionados. ',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $('#idIngresoRechazar').val(ingreso);
                $('#formRechazarIngreso').submit();
            } else {
                swal('No se aprobo el ingreso!');
            }
        });
}


function tickete(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}

function ticketeParcial(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/ruta/parcial/${id}`);
    document.getElementById('btn-pdf').click();
}
