window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

function esc(s) {
    if (s == null) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

let anteriorCantidadDetalle = null;
// Luego, configura un intervalo para seguir consultando cada 5 segundos
const intervalo = setInterval(recargarOrdenes, 10000);

// Live timer — update every second without a full reload
setInterval(function () {
    document.querySelectorAll('.cmd-timer[data-inicio]').forEach(function (el) {
        var iso = el.getAttribute('data-inicio');
        if (!iso) return;
        var d = new Date(iso);
        if (isNaN(d.getTime())) return;
        var mins = Math.floor((new Date() - d) / 60000);
        var valEl = el.querySelector('.cmd-timer-val');
        if (valEl) valEl.textContent = calcularTiempoTranscurrido(d);
        el.classList.remove('timer-ok', 'timer-warn', 'timer-alert');
        el.classList.add(mins < 10 ? 'timer-ok' : (mins < 15 ? 'timer-warn' : 'timer-alert'));
        // Also update header bg
        var header = el.closest('.cmd-header');
        if (header) {
            header.style.background = mins < 10 ? '#4338ca' : (mins < 15 ? '#b45309' : '#b91c1c');
        }
    });
}, 1000);

let usuarioInteraccion = false; // Variable para indicar si el usuario ya ha interactuado
/** Últimas métricas recibidas (para el modal de peores líneas). */
var metricasTiempoActual = null;

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

function escapeHtmlMetricas(str) {
    if (str == null || str === '') return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatearDateTimeMetrica(isoStr) {
    if (!isoStr) return '—';
    var d = new Date(isoStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return escapeHtmlMetricas(String(isoStr));
    return escapeHtmlMetricas(d.toLocaleString());
}

function truncarObsMetrica(s, maxLen) {
    if (s == null || s === '') return '';
    s = String(s).trim();
    if (s.length <= maxLen) return escapeHtmlMetricas(s);
    return escapeHtmlMetricas(s.slice(0, maxLen)) + '…';
}

function abrirModalPeoresLineasPrep() {
    var m = metricasTiempoActual;
    var sla = m && m.sla_minutos != null ? m.sla_minutos : 15;
    var lista = (m && Array.isArray(m.peores_lineas_detalle)) ? m.peores_lineas_detalle : [];
    var $tbody = $('#tbody_peores_lineas_prep');
    var $vacio = $('#peores_lineas_vacio');
    var $leyenda = $('#peores_lineas_leyenda');

    $tbody.empty();
    $leyenda.text(
        'Ordenadas de mayor a menor tiempo de preparación (ingreso → fin). SLA de referencia: ' + sla + ' min. Solo el día actual.'
    );

    if (!lista.length) {
        $vacio.removeClass('d-none');
        $('#mdl_peores_lineas_prep').modal('show');
        return;
    }
    $vacio.addClass('d-none');

    lista.forEach(function (row) {
        var obs = row.observacion ? '<br><small class="text-muted">' + truncarObsMetrica(row.observacion, 120) + '</small>' : '';
        var vsSla = row.excede_sla
            ? '<span class="badge badge-danger">+' + (row.minutos - sla) + ' min</span>'
            : '<span class="badge badge-success">OK</span>';
        var trClass = row.excede_sla ? 'table-warning' : '';
        $tbody.append(
            '<tr class="' + trClass + '">'
            + '<td>' + escapeHtmlMetricas(row.numero_orden != null ? String(row.numero_orden) : '—') + '</td>'
            + '<td>' + escapeHtmlMetricas(row.num_comanda != null ? String(row.num_comanda) : '—') + '</td>'
            + '<td>' + escapeHtmlMetricas(row.producto != null ? String(row.producto) : '—') + obs + '</td>'
            + '<td class="text-center">' + escapeHtmlMetricas(String(row.cantidad != null ? row.cantidad : '')) + '</td>'
            + '<td>' + formatearDateTimeMetrica(row.fecha_ingreso) + '</td>'
            + '<td>' + formatearDateTimeMetrica(row.fecha_fin) + '</td>'
            + '<td class="text-center font-weight-bold">' + escapeHtmlMetricas(String(row.minutos != null ? row.minutos : '')) + '</td>'
            + '<td>' + escapeHtmlMetricas(row.estacion != null ? String(row.estacion) : '—') + '</td>'
            + '<td class="text-center">' + vsSla + '</td>'
            + '</tr>'
        );
    });

    $('#mdl_peores_lineas_prep').modal('show');
}

function actualizarMetricasTiempo(m) {
    var $wrap = $('#fila_metricas_tiempo');
    var $box = $('#contenedor_metricas_tiempo');
    var $alcance = $('#metricas_alcance_txt');

    if (!m || typeof m !== 'object') {
        metricasTiempoActual = null;
        $box.empty();
        $alcance.text('');
        return;
    }

    metricasTiempoActual = m;

    var sla = m.sla_minutos != null ? m.sla_minutos : 15;
    var fechaDia = m.fecha_dia != null ? m.fecha_dia : '';
    $alcance.text(fechaDia ? '(solo el día actual · ' + fechaDia + ')' : '(solo el día actual)');

    var esGeneral = !!m.es_vista_general;
    var cid = m.comanda_filtro_id;
    var cnombre = m.comanda_filtro_nombre;
    var bodyComanda;
    if (esGeneral) {
        bodyComanda = '<span class="text-dark font-weight-bold">Vista general</span>'
            + '<small class="d-block text-muted mt-1">Métricas de todas las comandas de la sucursal</small>';
    } else {
        var lineaNombre = cnombre ? escapeHtmlMetricas(cnombre) : 'Sin nombre en catálogo';
        bodyComanda = '<span class="text-dark font-weight-bold">' + lineaNombre + '</span>' 
            + '<small class="d-block text-muted mt-1">Solo líneas de esta estación</small>';
    }

    var total = parseInt(m.total_lineas_terminadas, 10) || 0;
    var prom = m.promedio_min_por_linea;
    var pct = m.pct_dentro_sla;
    var maxM = m.max_minutos_una_linea;
    var dentro = parseInt(m.lineas_dentro_sla, 10) || 0;

    var pctClass = 'bg-secondary';
    if (pct != null) {
        if (pct >= 80) pctClass = 'bg-success';
        else if (pct >= 60) pctClass = 'bg-warning';
        else pctClass = 'bg-danger';
    }

    var bodyProm = total > 0 && prom != null ? prom + ' min' : '—';
    var bodySlaPct = total > 0 && pct != null ? pct + '%' : '—';
    var bodyMax = total > 0 && maxM != null ? maxM + ' min' : '—';
    var motivacionSla = '';
    if (total > 0 && pct != null) {
        var iconoMot = '🎯';
        var txtMot = 'Mantengamos este ritmo.';
        var claseMot = 'sla-ok';
        if (pct >= 90) {
            iconoMot = '🏆';
            txtMot = 'Excelente trabajo, sigamos asi.';
            claseMot = 'sla-top';
        } else if (pct >= 75) {
            iconoMot = '💪';
            txtMot = 'Buen avance, vamos por mas.';
            claseMot = 'sla-ok';
        } else if (pct >= 60) {
            iconoMot = '⚡';
            txtMot = 'Estamos cerca de la meta.';
            claseMot = 'sla-warning';
        } else {
            iconoMot = '🚨';
            txtMot = 'Enfoque en tiempos para recuperar SLA.';
            claseMot = 'sla-alert';
        }
        motivacionSla = '<small class="sla-indicador ' + claseMot + '">'
            + '<span class="sla-emoji" aria-hidden="true">' + iconoMot + '</span>'
            + '<span>' + escapeHtmlMetricas(txtMot) + '</span>'
            + '</small>';
    }
    var puedeVerDetalle = total > 0 && maxM != null;
    var clasesPeor = 'card card-statistic-1 h-100 card-metrica-clic' + (puedeVerDetalle ? '' : ' card-metrica-sin-datos');
    var hintPeor = puedeVerDetalle
        ? '<small class="d-block text-muted mt-1">Toca para ver el detalle</small>'
        : '<small class="d-block text-muted mt-1">Sin datos hoy</small>';

    var html = ''
        + '<div class="col-6 col-md-6 col-lg-3 mb-2">'
        + '  <div class="card card-statistic-1 h-100">'
        + '    <div class="card-icon bg-info"><i class="fas fa-filter"></i></div>'
        + '    <div class="card-wrap">'
        + '      <div class="card-header"><h4>Comanda en pantalla</h4></div>'
        + '      <div class="card-body">' + bodyComanda + '</div>'
        + '    </div>'
        + '  </div>'
        + '</div>'
        + '<div class="col-6 col-md-6 col-lg-3 mb-2">'
        + '  <div class="card card-statistic-1 h-100">'
        + '    <div class="card-icon bg-primary"><i class="fas fa-list-alt"></i></div>'
        + '    <div class="card-wrap">'
        + '      <div class="card-header"><h4>Tiempo prom. por línea</h4></div>'
        + '      <div class="card-body">' + bodyProm + '</div>'
        + '    </div>'
        + '  </div>'
        + '</div>'
        + '<div class="col-6 col-md-6 col-lg-3 mb-2">'
        + '  <div class="card card-statistic-1 h-100">'
        + '    <div class="card-icon ' + pctClass + '"><i class="fas fa-bullseye"></i></div>'
        + '    <div class="card-wrap">'
        + '      <div class="card-header"><h4>% dentro SLA (≤ ' + sla + ' min)</h4></div>'
        + '      <div class="card-body">' + bodySlaPct
        + (total > 0 ? '<small class="d-block text-muted">' + dentro + ' de ' + total + '</small>' : '')
        + motivacionSla
        + '      </div>'
        + '    </div>'
        + '  </div>'
        + '</div>'
        + '<div class="col-6 col-md-6 col-lg-3 mb-2">'
        + '  <div class="' + clasesPeor + '" id="card_metrica_peor_linea" role="button" tabindex="0" '
        + (puedeVerDetalle ? 'aria-label="Ver detalle de las líneas más lentas"' : '') + '>'
        + '    <div class="card-icon bg-dark"><i class="fas fa-arrow-up"></i></div>'
        + '    <div class="card-wrap">'
        + '      <div class="card-header"><h4>Peor línea (máx.)</h4></div>'
        + '      <div class="card-body">' + bodyMax + hintPeor + '</div>'
        + '    </div>'
        + '  </div>'
        + '</div>';

    $box.html('<div class="row">' + html + '</div>');

    var $peor = $('#card_metrica_peor_linea');
    $peor.off('click keydown');
    if (puedeVerDetalle) {
        $peor.on('click', function (e) {
            e.preventDefault();
            abrirModalPeoresLineasPrep();
        });
        $peor.on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                abrirModalPeoresLineasPrep();
            }
        });
    }

    $wrap.show();
}

/**
 * Estadísticas de tiempos/SLA: segunda petición para no retrasar el listado de comandas.
 */
function recargarMetricasTiempo() {
    $.ajax({
        url: `${base_path}/comandas/preparacion/recargarMetricasPreparacion`,
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            idComanda: (idComanda === '' || idComanda == null) ? '' : idComanda
        }
    }).done(function (response) {
        if (!response || !response['estado']) {
            return;
        }
        var datos = response['datos'] || {};
        actualizarMetricasTiempo(datos.metricas_tiempo || {});
    }).fail(function () {
        // El listado principal ya trae metricas_tiempo; esta petición es opcional.
    });
}

function recargarOrdenes() {
    $.ajax({
        url: `${base_path}/comandas/preparacion/recargarComandas`,
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            idComanda: (idComanda === '' || idComanda == null) ? '' : idComanda
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }

        var datos = response['datos'] || {};
        try {
            if (datos.comandas !== undefined) {
                crearHtmlComanda(datos.comandas);
            } else {
                crearHtmlComanda(datos);
            }
        } catch (e) {
            console.error('crearHtmlComanda', e);
        }

        if (datos.metricas_tiempo !== undefined && datos.metricas_tiempo !== null) {
            actualizarMetricasTiempo(datos.metricas_tiempo);
        } else {
            recargarMetricasTiempo();
        }
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
    contenedor.empty();
    let nuevaCantidadDetalle = 0;

    let comandas = (comandasRes == null || comandasRes === '')
        ? [] : (Array.isArray(comandasRes) ? comandasRes : Object.values(comandasRes));

    if (comandas.length === 0) {
        contenedor.append('<div class="col-12"><div class="alert alert-info"><i class="fas fa-utensils mr-2"></i>No hay comandas para mostrar.</div></div>');
        anteriorCantidadDetalle = 0;
        return;
    }

    comandas.forEach(p => {
        if (!p.detalles || p.detalles.length === 0) return;
        const lenDet = p.detalles.length;
        nuevaCantidadDetalle += lenDet;

        let fechaInicio = p.fecha_inicio ? new Date(p.fecha_inicio) : null;
        let mins = fechaInicio ? Math.floor((new Date() - fechaInicio) / 60000) : 0;
        let timerClass = mins < 10 ? 'timer-ok' : (mins < 15 ? 'timer-warn' : 'timer-alert');
        let tiempoStr = fechaInicio ? calcularTiempoTranscurrido(fechaInicio) : '—';
        let mesaInfo = p.mesa != null ? 'Mesa ' + esc(p.numero_mesa) : 'Para llevar';
        let clienteInfo = p.nombre_cliente ? ' &middot; ' + esc(p.nombre_cliente) : '';
        let headerBg = mins < 10 ? '#4338ca' : (mins < 15 ? '#b45309' : '#b91c1c');
        let isoInicio = fechaInicio ? fechaInicio.toISOString() : '';

        let itemsHtml = '';
        p.detalles.forEach(d => {
            const idDoc = d.id_detalle_orden_comanda != null ? d.id_detalle_orden_comanda : '';
            const yaListo = d.fecha_fin_comanda != null;

            let obsHtml = d.observacion
                ? '<div class="cmd-obs"><i class="fas fa-comment-alt"></i> ' + esc(d.observacion) + '</div>'
                : '';
            let extrasHtml = '';
            if (d.tieneExtras && d.extras && d.extras.length) {
                extrasHtml = '<div class="cmd-extras">'
                    + d.extras.map(e => '<span class="cmd-extra-tag">' + esc(e.descripcion_extra || '') + '</span>').join('')
                    + '</div>';
            }
            let accionHtml = yaListo
                ? '<div class="cmd-listo-done"><i class="fas fa-check-circle"></i></div>'
                : '<button type="button" class="cmd-btn-listo" onclick="event.stopPropagation();marcarLineaPreparada(' + idDoc + ')"><i class="fas fa-check"></i><span>Listo</span></button>';

            let rowCls = yaListo ? 'cmd-item cmd-item-done' : 'cmd-item';
            let receta = (d.receta || '').replace(/`/g,"'");
            let composicion = (d.composicion || '').replace(/`/g,"'");
            let nomProd = (d.nombre_producto || '').replace(/`/g,"'");

            itemsHtml += '<div class="' + rowCls + '" onclick="mostrarReceta(`' + receta + '`,`' + composicion + '`,`' + nomProd + '`)">'
                + '<div class="cmd-item-dot"></div>'
                + '<div class="cmd-item-body">'
                +   '<div class="cmd-nombre">' + esc(d.nombre_producto || '') + '</div>'
                +   obsHtml + extrasHtml
                + '</div>'
                + '<div class="cmd-qty">' + esc(String(d.cantidad_comanda || 1)) + '</div>'
                + '<div class="cmd-action">' + accionHtml + '</div>'
                + '</div>';
        });

        let cardHtml = '<div class="col-12 col-md-6 col-xl-4 mb-3">'
            + '<div class="card cmd-card shadow-sm">'
            + '<div class="cmd-header" style="background:' + headerBg + ';" onclick="imprimirTicket(' + p.id + ')">'
            +   '<div class="cmd-header-left">'
            +     '<div class="cmd-orden">' + esc(p.numero_orden || '') + '</div>'
            +     '<div class="cmd-meta">' + mesaInfo + clienteInfo + ' &nbsp;<small>' + esc(p.descEstado || '') + '</small></div>'
            +   '</div>'
            +   '<div class="cmd-timer ' + timerClass + '" data-inicio="' + isoInicio + '">'
            +     '<i class="fas fa-clock"></i> <span class="cmd-timer-val">' + tiempoStr + '</span>'
            +   '</div>'
            + '</div>'
            + '<div class="cmd-items">' + itemsHtml + '</div>'
            + '<div class="cmd-footer">'
            +   '<button class="cmd-btn-terminar" onclick="terminarPreparacion(' + p.id_orden_comanda + ')">'
            +     '<i class="fas fa-check-double"></i> Terminar Preparación'
            +   '</button>'
            + '</div>'
            + '</div></div>';

        contenedor.append(cardHtml);
    });

    if (anteriorCantidadDetalle != null) {
        if (nuevaCantidadDetalle > anteriorCantidadDetalle) reproducirSonidoNotificacionNuevaOrden();
        else if (nuevaCantidadDetalle < anteriorCantidadDetalle) reproducirSonidoNotificacionMenosOrden();
    }
    anteriorCantidadDetalle = nuevaCantidadDetalle;
}


function marcarLineaPreparada(id_detalle_orden_comanda) {
    if (id_detalle_orden_comanda == null || id_detalle_orden_comanda < 1) return;
    $.ajax({
        url: `${base_path}/comandas/preparacion/comanda/marcarLineaPreparada`,
        type: 'post',
        dataType: 'json',
        data: { _token: CSRF_TOKEN, id_detalle_orden_comanda: id_detalle_orden_comanda }
    }).done(function (res) {
        if (!res['estado']) {
            setError('Marcar línea', res['mensaje']);
        } else {
            recargarOrdenes();
            var datos = res['datos'] || {};
            if (datos.orden_completa && datos.id_orden_comanda) {
                terminarPreparacion(datos.id_orden_comanda);
            }
        }
    }).fail(function () { setError('Marcar línea', 'Algo salió mal.'); });
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
                data: $.extend({
                    _token: CSRF_TOKEN,
                    id_orden_comanda: id_orden_comanda
                }, (idComanda !== '' && idComanda != null && String(idComanda).trim() !== '')
                    ? { id_comanda: idComanda }
                    : {})
            }).done(function (res) {
                $('#loader').fadeOut();
                if (!res['estado']) {
                    setError('Terminar Preparación Orden', res['mensaje']);
                    window.setTimeout(function () {
                        window.location.href = window.location.url;
                    }, 1000);
                } else {
                    const msgOk = (res['mensaje'] && String(res['mensaje']).trim() !== '')
                        ? res['mensaje']
                        : 'Orden terminada correctamente.';
                    setSuccess('Terminar Preparación Orden.', msgOk);
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