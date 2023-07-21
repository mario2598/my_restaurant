window.addEventListener("load", initialice, false);


function initialice() {
    window.Echo.channel('ordenesChanel').listen('ordenesEvent',(e)=>{
        recargarOrdenes();
      });
}


function recargarOrdenes(){
    let estado = $('#estado_factura').val();
    if(estado == undefined || estado == null){
        estado = 'T';
    }
    iziToast.info({
        title: 'Actualizar Ordenes',
        message: 'Ordenes refrescadas',
        position: 'topCenter'
    });
    $('#contenedor_comandas').html("");
    $.ajax({
        url: `${base_path}/cocina/ordenes/todo/recargar`,
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            "estado_orden" : estado
        }
    }).done(function (comandas) {
        $('#contenedor_comandas').html(comandas);
    }).fail(function (jqXHR, textStatus, errorThrown) {       
        setError('Recargar Ordenes', 'Algo salió mal..');
        window.setTimeout(function () {
            window.location.href = window.location.url;
        }, 1000);
    });
}


function preTickete(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/pretiquete/${id}`);
    document.getElementById('btn-pdf').click();
}

function tickete(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}

function redirigirCobro(id) {
    $("#ipt_id_orden").val(id);
    $("#frm-factrar-orden").submit();
}