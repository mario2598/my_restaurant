window.addEventListener("load", initialice, false);

function agregarComanda(comanda){

}

function recargarOrdenes(){
    $.ajax({
        url: `${base_path}/cocina/cocina/comandas/recargar`,
        type: 'post',
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (comandas) {
        $('#contenedor_comandas').html(comandas);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        
        setError('Recargar Ordenes', 'Algo salió mal..');
       
    });
}

function terminarOrdenComida(orden,fecha) {
    swal({
        type: 'warning',
        text: 'Indicar pedido finalizado?',
        showCancelButton: false,
        confirmButtonText: "Confirmar",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $.ajax({
                url: `${base_path}/cocina/cocina/comandas/terminarPreparacionOrdenCocina`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_orden: orden,
                    fecha_detalle_orden: fecha
                }
            }).done(function (res) {
                if (!res['estado']) {
                    setError('Terminar Preparación Orden', res['mensaje']);
                    window.setTimeout(function () {
                        window.location.href = window.location.url;
                    }, 1000);
                } else {
                    setSuccess('Terminar Preparación Orden.', 'Orden terminada correctamente.');
                    recargarOrdenes();
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                setError('Terminar Preparación Orden', 'Algo salió mal..');
                window.setTimeout(function () {
                    window.location.href = window.location.url;
                }, 1000);
            });
        }
    });

}
