window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

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
    initModalEstadisticasPreparacion();
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
        actualizarResumenTiempos(response['datos']);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        setError('Recargar Comandas', 'Algo salió mal..');
    });
}

function parseMySqlDatetime(value) {
    if (value === null || value === undefined || value === '') return null;
    // MySQL datetime typically comes as: "YYYY-MM-DD HH:MM:SS"
    // Convert to ISO-like format for more consistent JS parsing.
    const s = String(value).replace(' ', 'T');
    const d = new Date(s);
    return isNaN(d.getTime()) ? null : d;
}

function fmtNumberES(n, decimals) {
    const num = Number(n);
    if (!isFinite(num)) return (0).toLocaleString('es-CR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    return num.toLocaleString('es-CR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function actualizarResumenTiempos(comandasRes) {
    const slaMin = 15;
    const comandas = Array.isArray(comandasRes) ? comandasRes : (comandasRes ? Object.values(comandasRes) : []);
    const ahora = new Date();

    const filtroDesdeVista = $('#tp_comanda_filtro_texto').val();
    const filtroComandaTexto = filtroDesdeVista || ((idComanda === '' || idComanda == null) ? 'General (todas las comandas)' : ('#' + idComanda));
    $('#tp_filtro_comanda').text(filtroComandaTexto);

    const totPendientes = { count: 0, sumMin: 0, slaCount: 0 };
    const totPreparados = { count: 0 };
    const porComanda = {}; // key: idComanda

    const asegurarGrupo = (comandaId) => {
        const key = String(comandaId ?? '0');
        if (!porComanda[key]) {
            porComanda[key] = { pendientesCount: 0, pendientesSumMin: 0, pendientesSlaCount: 0, preparadosCount: 0 };
        }
        return porComanda[key];
    };

    comandas.forEach(p => {
        if (!p || !Array.isArray(p.detalles)) return;

        p.detalles.forEach(d => {
            const ingreso = parseMySqlDatetime(d.fecha_ingreso_comanda);
            if (!ingreso) return;

            const fin = parseMySqlDatetime(d.fecha_fin_comanda);
            const comandaId = d.comanda;
            const g = asegurarGrupo(comandaId);

            const endDate = fin ? fin : ahora;
            const diffMin = (endDate - ingreso) / 60000;
            if (!isFinite(diffMin) || diffMin < 0) return;

            const dentroSla = diffMin <= slaMin;

            if (fin) {
                totPreparados.count += 1;
                g.preparadosCount += 1;
            } else {
                totPendientes.count += 1;
                g.pendientesCount += 1;
                totPendientes.sumMin += diffMin;
                g.pendientesSumMin += diffMin;
                if (dentroSla) {
                    totPendientes.slaCount += 1;
                    g.pendientesSlaCount += 1;
                }
            }
        });
    });

    const promPend = totPendientes.count > 0 ? totPendientes.sumMin / totPendientes.count : 0;
    const pctSlaPend = totPendientes.count > 0 ? (100 * totPendientes.slaCount / totPendientes.count) : 0;

    $('#tp_pendientes_count').text(totPendientes.count.toLocaleString('es-CR'));
    $('#tp_pendientes_prom_min').text(fmtNumberES(promPend, 1));
    $('#tp_lineas_sla_pct').text(fmtNumberES(pctSlaPend, 1));
    $('#tp_prep_count').text(totPreparados.count.toLocaleString('es-CR'));

    const showPorComanda = (idComanda === '' || idComanda === null || idComanda === undefined);
    if (!showPorComanda) {
        $('#tp_table_wrap').hide();
        return;
    }

    const ids = Object.keys(porComanda);
    const tbody = $('#tp_tbody').empty();

    if (!ids.length) {
        $('#tp_table_wrap').hide();
        return;
    }

    // Orden numérico por id, si aplica.
    ids.sort((a, b) => {
        const ai = parseInt(a, 10);
        const bi = parseInt(b, 10);
        if (isFinite(ai) && isFinite(bi)) return ai - bi;
        return String(a).localeCompare(String(b), 'es-CR');
    });

    ids.forEach(cid => {
        const g = porComanda[cid];
        const prom = g.pendientesCount > 0 ? (g.pendientesSumMin / g.pendientesCount) : 0;
        const pct = g.pendientesCount > 0 ? (100 * g.pendientesSlaCount / g.pendientesCount) : 0;
        tbody.append(`
            <tr>
                <td class="text-center">${cid}</td>
                <td class="text-center">${g.pendientesCount.toLocaleString('es-CR')}</td>
                <td class="text-center">${fmtNumberES(prom, 1)}</td>
                <td class="text-center">${fmtNumberES(pct, 1)}%</td>
            </tr>
        `);
    });

    $('#tp_table_wrap').show();
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
                let headerClass = p.mesa != null ? 'bg-primary' : 'bg-success';

                let cardHtml = `
                <div class="col-md-6 col-xs-12 col-sm-12 col-xl-4 mb-3">
                    <div class="card shadow-sm">
                        <div onclick="imprimirTicket(${p.id})" class="card-header ${headerClass} text-white" style="padding: 10px !important; cursor:pointer;">
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
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

                p.detalles.forEach(d => {
                    const idDoc = d.id_detalle_orden_comanda != null ? d.id_detalle_orden_comanda : '';
                    const yaPreparado =  d.fecha_fin_comanda != null;
                    const btnLinea = yaPreparado
                        ? '<span class="text-success"><i class="fas fa-check-circle"></i> Listo</span>'
                        : `<button type="button" class="btn btn-sm btn-outline-success" onclick="event.stopPropagation(); marcarLineaPreparada(${idDoc});" title="Marcar como preparado"><i class="fas fa-check"></i> Listo</button>`;
                    cardHtml += `
                                                <tr  style="cursor: pointer;"
                                                    onclick="mostrarReceta(\`${d.receta || ''}\`,\`${d.composicion || ''}\`,\`${d.nombre_producto || ''}\`)">
                                                    <td>${d.nombre_producto || ''}</td>
                                                    <td>${d.cantidad_comanda || '0'}</td>
                                                    <td><strong>${d.observacion || ''}</strong></td>
                                                    <td class="text-center">${btnLinea}</td>
                                                </tr>`;

                    if (d.tieneExtras) {
                        cardHtml += `
                                                <tr>
                                                    <td colspan="4" class="p-0">
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


function marcarLineaPreparada(id_detalle_orden_comanda) {
    if (id_detalle_orden_comanda == null || id_detalle_orden_comanda < 1) return;
    swal({
        type: 'warning',
        text: '¿Confirmar que esta línea está preparada?',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function (result) {
        if (result) {
            $('#loader').fadeIn();
            $.ajax({
                url: `${base_path}/comandas/preparacion/comanda/marcarLineaPreparada`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_detalle_orden_comanda: id_detalle_orden_comanda
                }
            }).done(function (res) {
                $('#loader').fadeOut();
                if (!res['estado']) {
                    setError('Marcar línea preparada', res['mensaje']);
                } else {
                    setSuccess('Línea preparada', 'Se marcó como preparado.');
                    recargarOrdenes();
                    var datos = res['datos'] || {};
                    if (datos.orden_completa && datos.id_orden_comanda) {
                        terminarPreparacion(datos.id_orden_comanda);
                    }
                }
            }).fail(function () {
                $('#loader').fadeOut();
                setError('Marcar línea preparada', 'Algo salió mal.');
            });
        }
    });
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

function initModalEstadisticasPreparacion() {
    var $modal = $('#mdlEstadisticasPrep');
    if (!$modal.length) {
        return;
    }
    if ($modal.parent()[0] !== document.body) {
        $modal.appendTo('body');
    }

    $modal.on('show.bs.modal', function () {
        var filtro = $('#tp_comanda_filtro_texto').val() || '';
        $('#est_filtro_comanda_modal').text(filtro || '-');
        cargarEstadisticasPreparacion();
    });

    $('#est_btn_actualizar').on('click', function () {
        cargarEstadisticasPreparacion();
    });
}

function cargarEstadisticasPreparacion() {
    var $modal = $('#mdlEstadisticasPrep');
    var url = $modal.attr('data-url-estadisticas');
    if (!url) {
        return;
    }
    $('#est_cargando').show();
    $('#est_error').hide();
    $('#est_contenido').hide();

    var payload = {
        _token: CSRF_TOKEN,
        idComanda: (idComanda === '' || idComanda == null) ? null : idComanda
    };

    $.ajax({
        url: url,
        type: 'POST',
        data: payload,
        dataType: 'json'
    }).done(function (data) {
        $('#est_cargando').hide();
        if (!data || !data.estado || !data.datos) {
            var msg = (data && data.mensaje) ? data.mensaje : 'No se pudieron cargar las estadísticas.';
            $('#est_error').text(msg).show();
            return;
        }
        var d = data.datos;
        var sla = d.sla_minutos != null ? d.sla_minutos : 15;
        $('#est_sla_texto').text(sla);

        var r = d.resumen || {};
        $('#est_res_lineas').text((r.lineas_terminadas || 0).toLocaleString('es-CR'));
        $('#est_res_prom').text(r.promedio_min != null ? fmtNumberES(r.promedio_min, 1) : '—');
        $('#est_res_sla').text(r.pct_sla != null ? fmtNumberES(r.pct_sla, 1) : '—');
        $('#est_res_max').text(r.max_min != null ? String(r.max_min) : '—');

        var tbProd = $('#est_tbody_productos').empty();
        var productos = d.productos || [];
        if (productos.length === 0) {
            tbProd.append('<tr><td colspan="5" class="text-center text-muted small">Sin datos de productos hoy.</td></tr>');
        } else {
            productos.forEach(function (p) {
                var nom = (p.nombre_producto || '-').replace(/</g, '&lt;');
                var pct = p.pct_sobre_sla != null ? fmtNumberES(p.pct_sobre_sla, 1) + '%' : '—';
                tbProd.append(
                    '<tr><td class="text-truncate" style="max-width:220px" title="' + nom + '">' + nom + '</td>' +
                    '<td class="text-center">' + (p.veces || 0).toLocaleString('es-CR') + '</td>' +
                    '<td class="text-center">' + (p.promedio_min != null ? fmtNumberES(p.promedio_min, 1) : '—') + '</td>' +
                    '<td class="text-center">' + (p.max_min != null ? p.max_min : '—') + '</td>' +
                    '<td class="text-center">' + pct + '</td></tr>'
                );
            });
        }

        var tbRec = $('#est_tbody_record').empty();
        var lineas = d.lineas_mas_largas || [];
        if (lineas.length === 0) {
            tbRec.append('<tr><td colspan="4" class="text-center text-muted small">Sin líneas terminadas hoy.</td></tr>');
        } else {
            lineas.forEach(function (x) {
                var nom = (x.nombre_producto || '-').replace(/</g, '&lt;');
                tbRec.append(
                    '<tr><td class="text-center">' + (x.numero_orden != null ? x.numero_orden : '—') + '</td>' +
                    '<td class="text-truncate" style="max-width:200px" title="' + nom + '">' + nom + '</td>' +
                    '<td class="text-center"><strong>' + (x.duracion_min != null ? x.duracion_min : '—') + '</strong></td>' +
                    '<td class="text-center small">' + (x.fecha_fin_legible || '—') + '</td></tr>'
                );
            });
        }

        $('#est_contenido').show();
    }).fail(function (xhr) {
        $('#est_cargando').hide();
        var msg = 'Error al cargar estadísticas. ';
        if (xhr && xhr.responseJSON && xhr.responseJSON.mensaje) {
            msg += xhr.responseJSON.mensaje;
        } else {
            msg += 'Verifique conexión.';
        }
        $('#est_error').text(msg).show();
    });
}