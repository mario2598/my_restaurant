window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_generico tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});


function limpiarPromocion() {
    promocionSeleccionada = {
        "id": 0,
        "dscTipo": "",
        "cod_general": "",
        "descuento": "",
        "fecha_inicio": "",
        "fecha_fin": "",
        "descripcion": "",
        "codigo": "",
        "activo": false,
        "cant_codigos": ""
    };
}

function abrirModalNuevaPromo() {
    limpiarPromocion();
    cargarPromoModal();
    $("#mdl_generico").modal("show");
}

function abrirModalEditarPromo(idPromo) {
    promocionSeleccionada = promociones.find(element => element.id == idPromo);
    if (promocionSeleccionada == null) {
        showError('Vacío!', 'No se encontró la promoción');
        return;
    }
    cargarPromoModal();
    $("#mdl_generico").modal("show");
}


function cargarPromoModal() {
    $('#fec_inicio').val(promocionSeleccionada.fecha_inicio);
    $('#fec_fin').val(promocionSeleccionada.fecha_fin);
    $('#descuento').val(promocionSeleccionada.descuento);
    $('#cod_descuento').val(promocionSeleccionada.codigo);
    $('#cod_rest').val(promocionSeleccionada.cant_codigos);
    $('#descripcion').val(promocionSeleccionada.descripcion);
    $("#tipo_descuento").val(promocionSeleccionada.cod_general);
    var activo = false;
    if (promocionSeleccionada.activo == "1") {
        activo = true;
    }
    $("#activo").prop("checked", activo);

}

function cargarModalPromo() {
    promocionSeleccionada.fecha_inicio = $('#fec_inicio').val();
    promocionSeleccionada.fecha_fin = $('#fec_fin').val();
    promocionSeleccionada.descuento = $('#descuento').val();
    promocionSeleccionada.codigo = $('#cod_descuento').val();
    promocionSeleccionada.cant_codigos = $('#cod_rest').val();
    promocionSeleccionada.descripcion = $('#descripcion').val();
    var select = document.getElementById("tipo_descuento");
    promocionSeleccionada.cod_general = select.value;
    var checkbox = document.getElementById("activo");
    promocionSeleccionada.activo = checkbox.checked;
}


function guardarPromocion() {
    cargarModalPromo();
    $.ajax({
        url: `${base_path}/mant/guardarPromocion`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            promocion: promocionSeleccionada
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }

        location.reload();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}


/** modales  */
/**
 * Abre el modal y carga los datos correspondientes

 */
function editarGenerico(id, desc, porcentaje) {
    $('#mdl_generico_ipt_descripcion').val(desc);
    $('#mdl_generico_ipt_porcentaje').val(porcentaje);
    $('#mdl_generico_ipt_id').val(id);
    $('#mdl_generico').modal('show');
}

/**
 * Cierra el modal 
 */
function cerrarModalGenerico() {
    $('#mdl_generico').modal('hide');
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevoGenerico() {
    $('#mdl_generico_ipt_descripcion').val("");
    $('#mdl_generico_ipt_porcentaje').val("0");
    $('#mdl_generico_ipt_id').val('-1');
    $('#mdl_generico').modal('show');
}

function eliminarGenerico(id) {
    swal({
            title: 'Seguro de inactivar el impuesto?',
            text: 'No podra deshacer esta acción!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                swal.close();
                $('#idGenericoEliminar').val(id);
                $('#frmEliminarGenerico').submit();

            } else {
                swal.close();
            }
        });


}
