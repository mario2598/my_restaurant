window.addEventListener("load", initialice, false);


function initialice() {
    window.Echo.channel('ordenesChanel').listen('ordenesEvent', (e) => {
        let entrar = false;
        e['data'].destinatarios.forEach(element => {
            if (element == 'COM_PT') {
                entrar = true;
            }
        });
        if (entrar) {
            soundNewOrder();
            recargarOrdenes();
        }
    });
}

function agregarComanda(comanda) {

}

function recargarOrdenes() {
    $.ajax({
        url: `${base_path}/cocina/ordenesListas/comanda/recargar`,
        type: 'post',
        data: {
            _token: CSRF_TOKEN
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

function entregarOrdenComida(orden) {
    swal({
        type: 'warning',
        text: 'Entregar la orden #' + orden + ' ?',
        showCancelButton: false,
        confirmButtonText: "Confirmar",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $.ajax({
                url: `${base_path}/cocina/ordenesListas/comanda/entregarOrdenComida`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_orden: orden
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
