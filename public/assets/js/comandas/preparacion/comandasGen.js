window.addEventListener("load", initialice, false);


let anteriorCantidadDetalle = null;
// Luego, configura un intervalo para seguir consultando cada 5 segundos
const intervalo = setInterval(recargarOrdenes, 10000);

let usuarioInteraccion = false; // Variable para indicar si el usuario ya ha interactuado

// Escuchar el evento de clic en el documento para detectar la primera interacción
document.addEventListener('click', () => {
    usuarioInteraccion = true;
});

function reproducirSonidoNotificacionNuevaOrden() {
    if (!usuarioInteraccion) {
        // Si el usuario no ha interactuado, no se puede reproducir el sonido
        console.warn('No se puede reproducir el sonido hasta que el usuario interactúe con la página.');
        return;
    }
    soundNewOrder();
}

function reproducirSonidoNotificacionMenosOrden() {
    if (!usuarioInteraccion) {
        // Si el usuario no ha interactuado, no se puede reproducir el sonido
        console.warn('No se puede reproducir el sonido hasta que el usuario interactúe con la página.');
        return;
    }
    soundClic();
}

function initialice() {
    recargarOrdenes();
}

function recargarOrdenes() {
    $.ajax({
        url: `${base_path}/comandas/preparacion/recargarComandas`,
        type: 'post',
        data: {
            _token: CSRF_TOKEN, idComanda: (idComanda == '') ? null : idComanda
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }

        crearHtmlComanda(response['datos'])
    }).fail(function (jqXHR, textStatus, errorThrown) {
        setError('Recargar Comandas', 'Algo salió mal..');
    });
}

function imprimirTicket(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}

function crearHtmlComanda(comandasRes) {
    let contenedor = $('#contenedor_comandas');
    contenedor.empty(); // Limpiar el contenedor
    let nuevaCantidadDetalle = 0;

    // Verificar si response.datos es un objeto y convertirlo en un arreglo si es necesario
    let comandas = Array.isArray(comandasRes) ? comandasRes : Object.values(comandasRes);

    // Verificar si comandas es un arreglo y tiene elementos
    if (Array.isArray(comandas) && comandas.length > 0) {

        comandas.forEach(p => {
            nuevaCantidadDetalle += p.detalles.length;
            if (p.detalles && p.detalles.length > 0) {
                let mesaInfo = p.mesa != null ? `Mesa: ${p.numero_mesa}` : 'Para llevar';
                let fechaInicio = p.fecha_inicio ? new Date(p.fecha_inicio) : null;
                let tiempoTranscurrido = calcularTiempoTranscurrido(fechaInicio);

                let cardHtml = `
                <div class="col-md-6 col-xs-12 col-sm-12 col-xl-4 mb-3">
                    <div class="card shadow-sm">
                        <div onclick="imprimirTicket(${p.id})" class="card-header bg-primary text-white" style="padding: 10px !important; cursor:pointer;">
                            <h4 class="mb-0 text-white">${p.numero_orden} : ${p.nombre_cliente || ''}</h4>
                            <small class="text-light">Estado: ${p.descEstado || ''}</small>
                        </div>
                        <div class="card-body p-2">
                            <div class="row">
                                <div class="col-12">
                                    <p><strong>${mesaInfo}</strong></p>
                                    <p><small>Fecha de Inicio: ${fechaInicio ? fechaInicio.toLocaleString() : ''}</small></p>
                                    <p><small>Tiempo Transcurrido: ${tiempoTranscurrido}</small></p>
                                </div>
                                <div class="col-12 mb-2">
                                    <button class="btn btn-outline-success btn-block"
                                        onclick="terminarPreparacion(${p.id_orden_comanda})">
                                        <i class="fas fa-check"></i> Terminar Preparación
                                    </button>
                                </div>
                                <div class="col-12">
                                    <h5>Detalle de la Orden</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

                p.detalles.forEach(d => {
                    cardHtml += `
                    <tr  style="cursor: pointer;"
                        onclick="mostrarReceta(\`${d.receta || ''}\`,\`${d.composicion || ''}\`,\`${d.nombre_producto || ''}\`)">
                        <td>${d.nombre_producto || ''}</td>
                        <td>${d.cantidad_comanda || '0'}</td>
                        <td>${d.observacion || ''}</td>
                    </tr>`;

                    if (d.tieneExtras) {
                        cardHtml += `
                    <tr>
                        <td colspan="3" class="p-0">
                            <div class="bg-light p-2">
                                <strong>Extras:</strong>
                                <ul class="list-unstyled mb-0">`;
                        d.extras.forEach(e => {
                            cardHtml += `
                                    <li>${e.descripcion_extra || ''}</li>`;
                        });
                        cardHtml += `
                                </ul>
                            </div>
                        </td>
                    </tr>`;
                    }
                });


                cardHtml += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-muted" style="padding: 10px !important;">
                            <small>Iniciado: ${fechaInicio ? fechaInicio.toLocaleTimeString() : ''}</small>
                        </div>
                    </div>
                </div>`;

                // Añadir la tarjeta generada al contenedor
                contenedor.append(cardHtml);
            }

        });
    } else {
        // Mostrar un mensaje si no hay comandas o la variable no es un arreglo
        contenedor.append('<div class="alert alert-info">No hay comandas para mostrar.</div>');
    }
    if (anteriorCantidadDetalle != null) {
        if (nuevaCantidadDetalle > anteriorCantidadDetalle) {
            reproducirSonidoNotificacionNuevaOrden();
        } else if (nuevaCantidadDetalle < anteriorCantidadDetalle) {
            reproducirSonidoNotificacionMenosOrden();
        }
    }
    anteriorCantidadDetalle = nuevaCantidadDetalle;
}

function calcularTiempoTranscurrido(fechaInicio) {
    if (!fechaInicio) return 'Sin información';

    let ahora = new Date();
    let diferencia = ahora - fechaInicio;

    let segundos = Math.floor(diferencia / 1000);
    let minutos = Math.floor(segundos / 60);
    let horas = Math.floor(minutos / 60);

    if (horas > 0) {
        return `${horas}h ${minutos % 60}m`;
    } else if (minutos > 0) {
        return `${minutos}m ${segundos % 60}s`;
    } else {
        return `${segundos}s`;
    }
}


function terminarPreparacion(id_orden_comanda) {
    swal({
        type: 'warning',
        text: 'Indicar comanda como finalizada ?',
        showCancelButton: false,
        confirmButtonText: "Confirmar",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $('#loader').fadeIn();
            $.ajax({
                url: `${base_path}/comandas/preparacion/comanda/terminarPreparacionComanda`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_orden_comanda: id_orden_comanda,
                    id_comanda: idComanda
                }
            }).done(function (res) {
                $('#loader').fadeOut();
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
                $('#loader').fadeOut();
                setError('Terminar Preparación Orden', 'Algo salió mal..');
              
            });
        }
    });

}

function mostrarReceta(receta, composicion, producto) {
    // Actualizar el título del modal
    $("#nombreProductoAux").text(producto);

    // Limpiar las listas antes de agregar contenido nuevo
    $("#listaReceta").empty();
    $("#listaComposicion").empty();

    // Variable para indicar si estamos en la sección de extras
    let enExtras = false;

    // Función para agregar un elemento a la lista con formato
    function agregarElementoALista(lista, item) {
        if (item.includes("Extras")) {
            // Añadir un separador de sección para los extras
            lista.append('<li class="list-group-item list-group-item-secondary text-center font-weight-bold">Extras</li>');
            enExtras = true; // Cambiar el estado a "en extras"
        } else {
            const partes = item.split(",");
            if (partes.length === 2) {
                const producto = partes[0].replace("[", "").trim();
                const cantidadUnidad = partes[1].replace("]", "").trim();
                lista.append(
                    `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${producto}</span>
                        <span class="badge badge-primary badge-pill">${cantidadUnidad}</span>
                    </li>`
                );
            } else {
                // Si no se puede dividir correctamente, mostrar el item tal cual
                lista.append('<li class="list-group-item">' + item + '</li>');
            }
        }
    }

    // Poblar la lista de receta
    receta.split("\n").forEach(function (item) {
        agregarElementoALista($("#listaReceta"), item);
    });

    // Poblar la lista de composición
    composicion.split("\n").forEach(function (item) {
        agregarElementoALista($("#listaComposicion"), item);
    });

    // Mostrar el modal
    $("#mdl_mostrar_receta").modal("show");
}


function ocultarReceta() {
    $("#mdl_mostrar_receta").modal("hide");
}