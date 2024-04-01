var idOrdenAnular = 0;
var idInfoFeGen = 0;
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
        "sucursal": $('#select_sucursal').val()
    }

    $.ajax({
        url: `${base_path}/fe/filtrarFacturas`,
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
            `<tr style="border-bottom: 1px solid grey;">
                <td class="text-center" onclick="imprimirTicket( ${orden.id})" style="cursor:pointer; text-decoration : underline; ">
                    ${orden.numero_orden}
                </td> 
                <td class="text-center">
                ${orden.nombreSucursal}
            </td>
                <td class="text-center">
                    ${orden.fechaFormat}
                </td>
                <td class="text-center">
                    ${orden.cedFe ?? ""}
                </td>
                <td class="text-center">
                ${orden.nombreFe ?? 0}
            </td> 
            <td class="text-center">
            ${orden.correoFe ?? 0}
        </td> 
        <td class="text-center">
        ${orden.comprobanteFe ?? "PENDIENTE ENVÍAR"}
    </td>
            <td class="text-center">
            ${orden.estadoFe ?? ""}
        </td>
       
    </td>`;
        if (orden.cod_general == 'FE_ORDEN_PEND') {
            texto = texto + `<td class="text-center">
            <button type="button" class="btn btn-success px-2"
             onclick='abrirModalEnvia("${orden.id ?? 0}","${orden.idFe ?? 0}")' 
                title="Marcar como envíada">
                <i class="fas fa-file-contract" aria-hidden="true"></i>
            </button>
        </td> `;
        }

        texto = texto + `</tr>`;
    });

    $('#tbody-ordenes').html(texto);
}


function imprimirTicket(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}


function abrirModalEnvia(idOrden, idInfoFe) {

    idOrdenAnular = idOrden;
    idInfoFeGen = idInfoFe;
    $('#num_comprobante').val("");
    $('#mdl-envia').modal('show');
}

function cerrarMdlEnvia() {
    $('#mdl-envia').modal('hide');
}

function enviarOrden() {
    if (idOrdenAnular == null || idOrdenAnular == 0) {
        showError("No existe la orden");
        return;
    }
    var numComprobante = $('#num_comprobante').val();

    $.ajax({
        url: `${base_path}/fe/enviarFe`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idOrden: idOrdenAnular,
            idInfoFe: idInfoFeGen,
            numComprobante: numComprobante
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        cerrarMdlEnvia();
        showSuccess("Se marcó como envíada la factura");
        filtrar();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}
