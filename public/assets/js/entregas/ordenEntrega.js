var idOrdenAnular = 0;
var detallesAnular = [];
var ordenesGen = [];
var entregaGestion = null;
$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-ordenes tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $("#input_buscar_generico3").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-detallesAnular tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    var currentDate = new Date();

    var year = currentDate.getFullYear();
    var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    var day = currentDate.getDate().toString().padStart(2, '0');
    var formattedDate = `${year}-${month}-${day}`;

    document.getElementById('desde').value = formattedDate;
    document.getElementById('hasta').value = formattedDate;

    filtrar();
});


function filtrar() {
    var filtro = {
        "desde": $('#desde').val(),
        "hasta": $('#hasta').val(),
        "estadoEntrega": $('#select_estado').val()
    }

    $.ajax({
        url: `${base_path}/entregas/filtrarOrdenesEntrega`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            filtro: filtro
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        generarHTMLOrdenes(response['datos']);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function generarHTMLOrdenes(ordenes) {
    var texto = "";
    ordenesGen = ordenes;

    ordenes.forEach(orden => {
        var lineas = "";
        var tablaDetalles = "";
        texto = texto +
            ` 
            <tr style="border-bottom: 1px solid grey;">
                <td class="text-center">
                    <button type="button" class="btn btn-success px-2" onclick='abrirModalEntrega("${orden.id ?? 0}")' 
                        title="Contactar cliente">
                        <i class="fas fa-truck" aria-hidden="true"> Información</i>
                    </button>
                </td>
                <td class="text-center" onclick="imprimirTicket( ${orden.id})" style="cursor:pointer; text-decoration : underline; ">
                    ${orden.numero_orden}
                </td> 
                `;
        if (orden.cod_general != 'ORD_ANULADA') {
            texto = texto + `<td class="text-center">
                    ${orden.entrega.estadoOrden ?? ""}
                </td>`;

        } else {
            texto = texto + `<td class="text-center">
                    Orden Anulada
                </td>`;
        }
        texto = texto + `</tr>`;
    });

    $('#tbody-ordenes').html(texto);
}


function imprimirTicket(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}


function abrirModalEntrega(idOrden) {
    idOrdenAnular = idOrden;
    var tablaDetalles = "";
    var texto = "";
    ordenesGen.forEach(orden => {
        if (orden.id == idOrden) {
            $("#nOrden").val(orden.numero_orden);
            $("#ocliente").val(orden.nombre_cliente);
            $("#ipt_contacto_entrega").val(orden.entrega.contacto);
            $("#ipt_lugar_entrega").val(orden.entrega.descripcion_lugar);
            $('#msjWhatsapp').attr('href', generarMensajeWhatsApp(orden.nombre_cliente, orden.numero_orden, orden.entrega.contacto));
            $("#nEstado").val(orden.entrega.estadoOrden);
            texto = texto + `
            <button type="button" class="btn btn-success px-2" onclick='imprimirTicket("${orden.id ?? 0}")' 
                title="Ver detalle de la orden">
                <i class="fas fa-bill" aria-hidden="true">Ver factura</i>
            </button>`;
            if (orden.cod_general != 'ORD_ANULADA') {
                $("#nEstado").val(orden.entrega.estadoOrden);
                if (orden.entrega.cod_general == 'ENTREGA_PEND_SALIDA_LOCAL') {
                    texto = texto + `
                        <button type="button" class="btn btn-warning px-2" onclick='iniciarRutaEntrega("${orden.id ?? 0}")' 
                            title="Iniciar ruta de entrega de orden">
                            <i class="fas fa-truck" aria-hidden="true">Iniciar ruta</i>
                        </button>`;
                } else if (orden.entrega.cod_general == 'ENTREGA_EN_RUTA') {
                    texto = texto + ` 
                        <button type="button" class="btn btn-success px-2" onclick='entregarOrden("${orden.id ?? 0}")' 
                            title="Marcar como entregada">
                            <i class="fas fa-shipping-fast" aria-hidden="true">Marcar como entregada (Entregar)</i>
                        </button>
                    `;
                }
            } else {
                $("#nEstado").val(orden.estadoOrden);
            }

            $("#botonesEntregaContainer").html(texto);

        }

    });
    $('#mdl-entrega').modal('show');
}

function iniciarRutaEntrega(idOrden) {
    $('#mdl-entrega').modal('hide');
    swal({
        type: 'warning',
        text: 'Iniciar ruta de entrega de pedido ?',
        showCancelButton: false,
        confirmButtonText: "Confirmar",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $.ajax({
                url: `${base_path}/entregas/iniciarRutaEntrega`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_orden: idOrden
                }
            }).done(function (res) {
                if (!res['estado']) {
                    setError('Iniciar ruta de entrega de  Orden', res['mensaje']);

                } else {
                    setSuccess('Iniciar ruta de entrega de  Orden.', 'Se inicio la ruta correctamente.');
                    filtrar();
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                setError('Iniciar ruta de entrega de  Orden.', 'Algo salió mal..');

            });
        }
    });

}

function entregarOrden(idOrden) {
    $('#mdl-entrega').modal('hide');
    swal({
        type: 'warning',
        text: 'Marcar la orden como entregada?',
        showCancelButton: false,
        confirmButtonText: "Confirmar",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $.ajax({
                url: `${base_path}/entregas/entregarOrden`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_orden: idOrden
                }
            }).done(function (res) {
                if (!res['estado']) {
                    setError('Entrega de  Orden', res['mensaje']);

                } else {
                    setSuccess('Entrega de  Orden.', 'Se entregó correctamente.');
                    filtrar();
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                setError('Entrega de  Orden.', 'Algo salió mal..');

            });
        }
    });

}

function generarMensajeWhatsApp(nombreUsuario, numeroOrden, telefono) {
    // Formatear el mensaje con los datos proporcionados
    var mensaje = "Hola " + nombreUsuario + ", tu pedido con número de orden " + numeroOrden + " llegó a tu destino. ¿Podemos continuar con la entrega?";

    // Formatear el enlace con el mensaje y el número de teléfono del usuario
    var enlaceWhatsApp = "https://api.whatsapp.com/send?phone=506".telefono + "&text=" + encodeURIComponent(mensaje);

    // Retornar el enlace generado
    return enlaceWhatsApp;
}

function cerrarMdlAnular() {
    $('#mdl-detallesAnular').modal('hide');
}

function anularOrden() {
    if (idOrdenAnular == null || idOrdenAnular == 0) {
        showError("No existe la orden");
        return;
    }
    var checkboxes = document.querySelectorAll('.elemento');
    var detallesAnular = "";
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            detallesAnular = detallesAnular + checkboxes[i].value + ",";
        }
    }
    detallesAnular = detallesAnular.slice(0, -1);
    var arrayDeCadenas = detallesAnular.split(',');
    var detallesAux = arrayDeCadenas.map(function (numero) {
        return parseInt(numero, 10);
    });

    $.ajax({
        url: `${base_path}/facturacion/pos/anularOrden`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idOrden: idOrdenAnular,
            lineas: detallesAux
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        cerrarMdlAnular();
        showSuccess("Se anulo correctamente la orden");
        filtrar();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}
