window.addEventListener("load", initialice, false);

  // Luego, configura un intervalo para seguir consultando cada 5 segundos
  const intervalo = setInterval(recargarOrdenes, 5000);

function recargarOrdenes(){
    $.ajax({
        url: `${base_path}/facturacion/ordenesEntrega/recargar`,
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

function terminarEntrega(orden) {
    swal({
        type: 'warning',
        text: 'Indicar pedido emtregado?',
        showCancelButton: false,
        confirmButtonText: "Confirmar",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $.ajax({
                url: `${base_path}/facturacion/ordenesPreparacion/terminarEntregaOrden`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_orden: orden
                }
            }).done(function (res) {
                if (!res['estado']) {
                    setError('Terminar Orden', res['mensaje']);
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

function marcarLineaEntregada(id_detalle_orden_comanda) {
    if (id_detalle_orden_comanda == null || id_detalle_orden_comanda < 1) return;

    swal({
        type: 'warning',
        text: '¿Marcar esta línea como entregada?',
        showCancelButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $.ajax({
                url: `${base_path}/facturacion/ordenesEntrega/marcarLineaEntregada`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_detalle_orden_comanda: id_detalle_orden_comanda
                }
            }).done(function (res) {
                if (!res['estado']) {
                    setError('Marcar línea entregada', res['mensaje']);
                } else {
                    setSuccess('Línea entregada', 'Se marcó como entregada.');
                    recargarOrdenes();
                }
            }).fail(function () {
                setError('Marcar línea entregada', 'Algo salió mal..');
            });
        }
    });
}
