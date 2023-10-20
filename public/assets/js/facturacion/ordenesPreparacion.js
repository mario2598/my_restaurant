window.addEventListener("load", initialice, false);


  
  // Luego, configura un intervalo para seguir consultando cada 5 segundos
 // const intervalo = setInterval(recargarOrdenes, 5000);

function agregarComanda(comanda){

}

function recargarOrdenes(){
    $.ajax({
        url: `${base_path}/facturacion/ordenesPreparacion/recargar`,
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
function terminarPreparacion(orden) {
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
                url: `${base_path}/facturacion/ordenesPreparacion/terminarPreparacionOrden`,
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
