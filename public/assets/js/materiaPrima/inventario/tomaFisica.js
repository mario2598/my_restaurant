window.addEventListener("load", initialice, false);
var sucursalSeleccionada = null;
function initialice() {
    $("#btn_buscar_producto_ayuda").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_productos tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
}

function filtrar() {
    sucursalSeleccionada = $('#sucursal').val();
    $.ajax({
        url: `${base_path}/materiaPrima/inventario/buscarMPTomaFisica`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            sucursal: sucursalSeleccionada
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
       
        generarTablaMateriaPrima(response['datos']);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}


function generarTablaMateriaPrima(materiaPrima) {
    $('#tbody_generico').html("");
    var texto = "";

    materiaPrima.forEach(mp => {
        texto = texto +
            `<tr style="border-bottom: 1px solid grey;">
              
                <td class="text-center">
                ${mp.nombre}
            </td>
                <td class="text-center">
                    ${mp.unidad_medida}
                </td>
                <td class="text-center">
                   <input type="number" value="0" id="iptNumero${mp.id}" >
                </td>
                <td class="text-center">
                <button onclick="crearTomaFisicaAction(${mp.id})" class="btn btn-primary btn-icon form-control"
                style="cursor: pointer;"><i class="fas fa-plus"> Crear Toma</i></button>
            </td> `;

        texto = texto + `</tr>`;
    });

    $('#tbody_generico').html(texto);
}


function crearTomaFisicaAction(idMp) {
    swal({
            title: 'Seguro de ingresar la toma física?',
            text: 'Esto cambiara la cantidad en el inventario!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                var cantAux = $("#iptNumero" + idMp).val();
                crearTomaFisica(idMp,cantAux);
            } else {
                swal.close();
            }
        });
}

function crearTomaFisica(idMp,cantidad) {
    $.ajax({
        url: `${base_path}/materiaPrima/inventario/creaMPTomaFisica`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            sucursal:sucursalSeleccionada,
            idMateriaPrima : idMp,
            cantidadAjuste : cantidad
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        $("#iptNumero" + idMp).val(0)
        showSuccess("Se ingresó la toma fisica");
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}
