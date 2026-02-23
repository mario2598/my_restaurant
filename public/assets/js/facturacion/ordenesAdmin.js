var idOrdenAnular = 0;
var detallesAnular = [];
var ordenesGen = [];
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
        "sucursal": $('#select_sucursal').val(),
        "cliente": $('#select_cliente').val()
    }

    $.ajax({
        url: `${base_path}/facturacion/filtrarOrdenesAdmin`,
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
        var incidentes = orden.incidentes || [];
        var tieneIncidentes = orden.tiene_incidentes || incidentes.length > 0;
        var celdaIncidentes = "";
        if (tieneIncidentes) {
            var n = incidentes.length;
            var totalRebaja = incidentes.reduce(function (sum, inc) { return sum + (parseFloat(inc.monto_afectado) || 0); }, 0);
            celdaIncidentes = `<td class="text-center">
                <i class="fas fa-exclamation-triangle text-warning" title="Orden con incidente(s)"></i>
                <a href="javascript:void(0)" onclick="verIncidentesOrden(${orden.id}, '${(orden.numero_orden || '').toString().replace(/'/g, "\\'")}')" class="ml-1" title="Ver detalle de incidentes">
                    Sí (${n})
                </a>
            </td>`;
        } else {
            celdaIncidentes = `<td class="text-center text-muted">—</td>`;
        }
        texto = texto +
            `<tr style="border-bottom: 1px solid grey;">
                <td class="text-center" onclick="imprimirTicket( ${orden.id})" style="cursor:pointer; text-decoration : underline; ">
                    ${orden.numero_orden}
                </td> 
                <td class="text-center">
                ${orden.nombreSucursal}
            </td>
                <td class="text-center">
                    ${orden.fecha_inicio}
                </td>
                <td class="text-center">
                    ${orden.nombre_cliente ?? ""}
                </td>
                <td class="text-center">
                ${orden.total_con_descuento ?? 0}
            </td> <td class="text-center">
            ${orden.estadoOrden ?? ""}
        </td>
        ${celdaIncidentes}
        <td class="text-center">
         <a href="${base_path}/tracking/orden/${orden.idOrdenEnc ?? ''}" style="display: block;width: 100%;" target="_blank">
               Link Rastreó                      
        </a>
    </td>`;
        if (orden.cod_general != 'ORD_ANULADA') {
            texto = texto + `<td class="text-center">
                <button type="button" class="btn btn-danger px-2" onclick='abrirModalAnularOrden("${orden.id ?? 0}")' 
                    title="Anular Orden">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                </button>
            </td>`;
        } else {
            texto = texto + `<td class="text-center"></td>`;
        }
        texto = texto + `</tr>`;
    });

    $('#tbody-ordenes').html(texto);
}

function verIncidentesOrden(idOrden, numeroOrden) {
    var orden = ordenesGen.find(function (o) { return o.id == idOrden; });
    if (!orden || !orden.incidentes || orden.incidentes.length === 0) {
        return;
    }
    var incidentes = orden.incidentes;
    $('#mdl-incidentes-orden-numero').text('Orden #' + (numeroOrden || idOrden));
    var filas = '';
    var totalRebaja = 0;
    incidentes.forEach(function (inc) {
        var monto = parseFloat(inc.monto_afectado) || 0;
        totalRebaja += monto;
        var fecha = (inc.fecha || '').substring(0, 19).replace('T', ' ');
        var usuario = (inc.usuario_nombre || inc.usuario_login || '—');
        var desc = (inc.descripcion || '').substring(0, 500);
        filas += '<tr><td>' + fecha + '</td><td>' + usuario + '</td><td>' + desc + '</td><td class="text-right">' + (monto).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' }) + '</td></tr>';
    });
    $('#tbody-incidentes-orden').html(filas);
    $('#mdl-incidentes-total-rebaja').html('<strong>Total a rebajar:</strong> ' + (totalRebaja).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' }));
    $('#mdl-incidentes-orden').modal('show');
}


function imprimirTicket(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}


function abrirModalAnularOrden(idOrden) {
    idOrdenAnular = idOrden;
    var tablaDetalles = "";
    ordenesGen.forEach(orden => {
        if (orden.id == idOrden) {
            orden.detalles.forEach(detalle => {
                tablaDetalles = tablaDetalles + `<tr style='border-bottom: 1px solid grey;'>
                                                    <td class="text-center" >
                                                        ${detalle.nombre_producto}
                                                    </td> 
                                                    <td class="text-center">
                                                        ${detalle.cantidad}
                                                    </td>
                                                    <td class="text-center">
                                                        ${detalle.total ?? 0}
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" id="elemento${detalle.id ?? 0}" class="elemento" value="${detalle.id ?? 0}"> 
                                                    </td>
                                                </tr>`;
            });
        }

    });
    $('#mdl-detallesAnular').modal('show');
    $('#tbody-detallesAnular').html(tablaDetalles);
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
    $('#loader').fadeIn();
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
    }).always(function () {
        $('#loader').fadeOut();
    });
}
