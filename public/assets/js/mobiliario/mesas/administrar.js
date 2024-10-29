
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var mesasGeneral;
var mesaGestion;

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbodyMesas tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    cargarMesas();
});

function cargarMesas() {
    $('#loader').fadeIn();
    mesasGeneral = {};
    $.ajax({
        url: `${base_path}/mobiliario/mesas/cargar`,
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

        generarHTMLMesas(response['datos']);
        $('#loader').fadeOut();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
        $('#loader').fadeOut();
    });
    $('#loader').fadeOut();
}

function generarHTMLMesas(mesas) {
    var texto = "";
    mesasGeneral = mesas;
    mesasGeneral.forEach(mesa => {
        texto += `
            <tr > 
              <td class="text-center">${mesa.numero_mesa ?? "S/A"}</td>
              <td class="text-center">${mesa.capacidad ?? "S/A"}</td>
               <td class="text-center"><a title="Eliminar" 
                    class="btn btn-primary" onclick="eliminarMesaAction(${mesa.id ?? null})"  style="color:white;cursor:pointer;">
                    <i class="fas fa-trash"></i></a>
                    <a title="Editar" 
                    class="btn btn-primary"  onclick="cargarMesaModal(${mesa.id ?? null})" style="color:white;cursor:pointer;">
                    <i class="fas fa-cog"></i></a>
                    </td></tr> `;
    });

    $('#tbodyMesas').html(texto);

}

function cargarMesaModal(idMesa) {
    mesaGestion = mesasGeneral.find(element => element.id == idMesa);
    if (mesaGestion == null) {
        showError("Error cargando la mesa.");
        return;
    }
    $('#mdl_gestiona_mesa_label').text('Editar Mesa');
    $('#numeroMesa').val(mesaGestion.numero_mesa);
    $('#capacidadMesa').val(mesaGestion.capacidad);
    $('#mdl_gestiona_mesa').modal('show');
}

function addMesaModal() {
    mesaGestion = null;
    $('#mdl_gestiona_mesa_label').text('Agregar Nueva Mesa');
    $('#numeroMesa').val("");
    $('#capacidadMesa').val("");
    $('#mdl_gestiona_mesa').modal('show');
}

function cerrarMesaModal() {
    mesaGestion = null;
    $('#mdl_gestiona_mesa_label').text('Agregar Nueva Mesa');
    $('#numeroMesa').val("");
    $('#capacidadMesa').val("");
    $('#mdl_gestiona_mesa').modal('hide');
}

function guardarMesa() {
    const mesaGuardar = {
        "id": mesaGestion == null ? -1 : mesaGestion.id,
        "numero_mesa": $('#numeroMesa').val(),
        "sucursal": $('#select_sucursal').val(),
        "capacidad": $('#capacidadMesa').val()
    }

    $('#loader').fadeIn();

    $.ajax({
        url: `${base_path}/mobiliario/mesas/guardarMesa`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            mesa: mesaGuardar
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se guardo la mesa correctamente.");
        $('#loader').fadeOut();
        cargarMesas();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
        $('#loader').fadeOut();
    });
    cerrarMesaModal();
    $('#loader').fadeOut();
}

function eliminarMesaAction(idMesa) {
    swal({
        title: 'Seguro de remover la mesa?',
        text: 'No podras deshacer el cambio!',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                swal.close();
                eliminarMesa(idMesa);

            } else {
                swal.close();
            }
        });
}
function eliminarMesa(idMesa) {

    $('#loader').fadeIn();

    $.ajax({
        url: `${base_path}/mobiliario/mesas/eliminarMesa`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id: idMesa
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se elimino la mesa correctamente.");
        $('#loader').fadeOut();
        cargarMesas();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
        $('#loader').fadeOut();
    });
    cerrarMesaModal();
    $('#loader').fadeOut();
}
