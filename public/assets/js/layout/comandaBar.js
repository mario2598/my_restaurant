function recargarComandaBar() {
    let controlador = document.getElementById('controlador_comandaBar');
    //Si esta abierto no
    if (!controlador.classList.contains('showSettingPanel')) {
        $('#pne_comandaBar').html('');
        $.ajax({
            url: `${base_path}/comandaBar/recargar`,
            type: 'post',
            data: {
                _token: CSRF_TOKEN
            }
        }).done(function (response) {
            if (response == 0) {
                $('#pne_comandaBar').html('Error.');
            } else {
                $('#pne_comandaBar').html(response);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#pne_comandaBar').html('Error.');
        });
    }
}

function goFacturar(id) {
    $("#ipt_id_orden_fac").val(id);
    $("#frm-facturar-orden").submit();
}

function goFacturaOrden(id) {
    $("#ipt_id_orden_factura").val(id);
    $("#frm-go-orden").submit();
}
