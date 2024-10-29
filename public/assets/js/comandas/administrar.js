
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var comandasGeneral;
var comandaGestion;

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_pagos tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    cargarComandas();
});

function cargarComandas() {
    $('#loader').fadeIn();
    comandasGeneral = {};
    $.ajax({
        url: `${base_path}/comandas/administrar/cargar`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idSucursal: $('#select_sucursal').val()
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }

        generarHTMLComandas(response['datos']);
        $('#loader').fadeOut();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
        $('#loader').fadeOut();
    });
    $('#loader').fadeOut();
}

function generarHTMLComandas(comandas) {
    var texto = "";
    comandasGeneral = comandas;
    comandasGeneral.forEach(comanda => {
        texto += `
            <tr > 
              <td class="text-center">${comanda.id ?? "S/A"}</td>
               <td class="text-center">${comanda.nombre ?? "S/A"}</td>
               <td class="text-center"><a title="Eliminar" 
                    class="btn btn-primary" onclick="eliminarComandaAction(${comanda.id ?? null})"  style="color:white;cursor:pointer;">
                    <i class="fas fa-trash"></i></a>
                    <a title="Editar" 
                    class="btn btn-primary"  onclick="cargarComandaModal(${comanda.id ?? null})" style="color:white;cursor:pointer;">
                    <i class="fas fa-cog"></i></a>
                    </td></tr> `;
    });

    $('#tbodyComandas').html(texto);

}

function cargarComandaModal(idComanda) {
    comandaGestion = comandasGeneral.find(element => element.id == idComanda);
    if (comandaGestion == null) {
        showError("Error cargando la comanda.");
        return;
    }
    $('#mdl_gestiona_comanda_label').text('Editar Comanda');
    $('#nombre_comanda').val(comandaGestion.nombre);

    $('#mdl_gestiona_comanda').modal('show');
}

function addComandaModal() {
    comandaGestion = null;
    $('#mdl_gestiona_comanda_label').text('Agregar Nueva Comanda');
    $('#nombre_comanda').val("");

    $('#mdl_gestiona_comanda').modal('show');
}

function cerrarComandaModal() {
    comandaGestion = null;
    $('#mdl_gestiona_comanda_label').text('Agregar Nueva Comanda');
    $('#nombre_comanda').val("");

    $('#mdl_gestiona_comanda').modal('hide');
}

function guardarComanda() {
    const comandaGuardar = {
        "id": comandaGestion == null ? -1 : comandaGestion.id,
        "nombre": $('#nombre_comanda').val(),
        "sucursal": $('#select_sucursal').val()
    }

    $('#loader').fadeIn();

    $.ajax({
        url: `${base_path}/comandas/administrar/guardarComanda`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            comanda: comandaGuardar
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se guardo la comanda correctamente.");
        $('#loader').fadeOut();
        cargarComandas();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
        $('#loader').fadeOut();
    });
    cerrarComandaModal();
    $('#loader').fadeOut();
}

function eliminarComandaAction(idComanda) {
    swal({
        title: 'Seguro de remover la comanda?',
        text: 'No podras deshacer el cambio!',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                swal.close();
                eliminarComanda(idComanda);

            } else {
                swal.close();
            }
        });
}
function eliminarComanda(idComanda) {

    $('#loader').fadeIn();

    $.ajax({
        url: `${base_path}/comandas/administrar/eliminarComanda`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id: idComanda
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se elimino la comanda correctamente.");
        $('#loader').fadeOut();
        cargarComandas();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
        $('#loader').fadeOut();
    });
    cerrarComandaModal();
    $('#loader').fadeOut();
}
