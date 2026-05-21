/**
 * Mapa de mesas en POS: seleccionar mesa y ver/abrir órdenes por mesa.
 */
var posPlanoDatos = null;
var posPlanoModo = 'seleccionar';

function abrirMapaMesas(modo) {
    posPlanoModo = modo || 'seleccionar';
    if (posPlanoModo === 'ordenes') {
        $('#tab-pos-plano-ordenes').tab('show');
    } else {
        $('#tab-pos-plano-seleccionar').tab('show');
    }
    $('#mdl-pos-plano-mesas').modal('show');
    cargarPlanoPos();
}

function setModoPlanoPos(modo) {
    posPlanoModo = modo;
    actualizarAyudaPlanoPos();
    if (posPlanoDatos) {
        renderizarPlanoPos();
        renderizarResumenPlanoPos();
    }
}

function actualizarAyudaPlanoPos() {
    var txt = posPlanoModo === 'ordenes'
        ? 'Mesas con borde naranja tienen cuenta por cobrar. Toque la mesa y elija la orden en el panel derecho. El número en la esquina indica cuántas órdenes hay en esta caja.'
        : 'Toque una mesa libre (verde) para asignarla a la orden actual. Si la mesa ya tiene órdenes, verá el detalle al tocarla.';
    $('#pos-plano-ayuda').text(txt);
}

function getOrdenesMesa(mesaId) {
    if (!posPlanoDatos || !posPlanoDatos.ordenes_por_mesa) return [];
    return posPlanoDatos.ordenes_por_mesa[mesaId] || posPlanoDatos.ordenes_por_mesa[String(mesaId)] || [];
}

function contarOrdenesPlano() {
    var pendientes = 0;
    var pagadas = 0;
    var mesasConPendiente = 0;
    var porMesa = posPlanoDatos.ordenes_por_mesa || {};

    Object.keys(porMesa).forEach(function (mesaId) {
        var lista = porMesa[mesaId] || [];
        var tienePend = false;
        lista.forEach(function (o) {
            if (o.pagado === 0) {
                pendientes++;
                tienePend = true;
            } else {
                pagadas++;
            }
        });
        if (tienePend) mesasConPendiente++;
    });

    var sinMesa = (posPlanoDatos.ordenes_sin_mesa || []).length;
    return { pendientes: pendientes, pagadas: pagadas, mesasConPendiente: mesasConPendiente, sinMesa: sinMesa };
}

function renderizarResumenPlanoPos() {
    var $box = $('#pos-plano-resumen');
    if (!posPlanoDatos) {
        $box.addClass('d-none');
        return;
    }
    var c = contarOrdenesPlano();
    if (c.pendientes === 0 && c.pagadas === 0 && c.sinMesa === 0) {
        $box.addClass('d-none');
        return;
    }
    var html = '<strong>Caja actual:</strong> ';
    if (c.pendientes > 0) {
        html += '<span class="text-warning">' + c.pendientes + ' orden(es) por cobrar</span>';
        html += ' en <span class="text-warning">' + c.mesasConPendiente + ' mesa(s)</span>';
    } else {
        html += 'sin cuentas pendientes';
    }
    if (c.pagadas > 0) {
        html += ' · <span class="text-success">' + c.pagadas + ' ya pagada(s)</span>';
    }
    if (c.sinMesa > 0) {
        html += ' · <span class="text-info">' + c.sinMesa + ' para llevar / sin mesa</span>';
    }
    if (posPlanoModo === 'ordenes') {
        html += '<br><span class="text-muted">Toque una mesa naranja para abrir su cuenta.</span>';
    }
    $box.html(html).removeClass('d-none');
}

function cargarPlanoPos() {
    $('#pos-plano-zonas, #pos-plano-mesas').html('');
    $('#pos-plano-sidebar').html('<p class="text-muted small p-2"><i class="fas fa-spinner fa-spin"></i> Cargando mapa…</p>');
    $.ajax({
        url: base_path + '/facturacion/mesas/cargar-plano',
        type: 'get',
        dataType: 'json'
    }).done(function (response) {
        if (!response.estado) {
            showError(response.mensaje || 'Error al cargar el mapa');
            return;
        }
        posPlanoDatos = response.datos;
        var ar = posPlanoDatos.ancho_referencia || 100;
        var al = posPlanoDatos.alto_referencia || 150;
        $('#pos-plano-canvas').css('aspect-ratio', ar + ' / ' + al);
        renderizarZonasPos(posPlanoDatos.zonas || []);
        renderizarPlanoPos();
        renderizarResumenPlanoPos();
        actualizarAyudaPlanoPos();
        renderizarSidebarPos(null);
    }).fail(function () {
        showError('No se pudo cargar el mapa. Configure el plano en Mobiliario → Plano de mesas.');
        $('#pos-plano-sidebar').html('<p class="text-danger small p-2">Error de conexión o falta script SQL del plano.</p>');
    });
}

function renderizarZonasPos(zonas) {
    var html = '';
    zonas.forEach(function (z) {
        html += '<div class="plano-zona" style="'
            + 'left:' + z.x + '%;top:' + z.y + '%;width:' + z.w + '%;height:' + z.h + '%;'
            + 'background:' + (z.color || '#eee') + ';">'
            + '<span class="plano-zona-label">' + escHtmlPos(z.nombre || z.id) + '</span></div>';
    });
    $('#pos-plano-zonas').html(html);
}

function tienePosicionPlano(m) {
    return m.plano_x !== null && m.plano_x !== '' && m.plano_y !== null && m.plano_y !== '';
}

function resumenOrdenesMesa(ordenes) {
    var pend = ordenes.filter(function (o) { return o.pagado === 0; });
    var pag = ordenes.filter(function (o) { return o.pagado === 1; });
    var saldoPend = pend.reduce(function (s, o) { return s + (o.saldo != null ? o.saldo : o.total || 0); }, 0);
    return { pendientes: pend.length, pagadas: pag.length, saldoPend: saldoPend };
}

function tooltipOrdenesMesa(m, ordenes) {
    if (!ordenes.length) {
        return 'Mesa ' + m.numero_mesa + ' — sin órdenes en esta caja';
    }
    var r = resumenOrdenesMesa(ordenes);
    var lines = ['Mesa ' + m.numero_mesa];
    if (r.pendientes) {
        lines.push(r.pendientes + ' pendiente(s) — saldo ₡' + r.saldoPend.toLocaleString('es-CR'));
    }
    if (r.pagadas) {
        lines.push(r.pagadas + ' pagada(s)');
    }
    ordenes.slice(0, 4).forEach(function (o) {
        lines.push('#' + o.numero_orden + ' ' + etiquetaEstadoOrden(o) + ' ₡' + (o.total || 0).toLocaleString('es-CR'));
    });
    if (ordenes.length > 4) {
        lines.push('… y ' + (ordenes.length - 4) + ' más');
    }
    return lines.join('\n');
}

function renderizarPlanoPos() {
    var mesas = posPlanoDatos.mesas || [];
    var idxSin = 0;
    var mesaActual = typeof ordenGestion !== 'undefined' ? String(ordenGestion.mesa) : '-1';
    var html = '';

    mesas.forEach(function (m) {
        var x, y, w, h;
        if (tienePosicionPlano(m)) {
            x = parseFloat(m.plano_x);
            y = parseFloat(m.plano_y);
            w = parseFloat(m.plano_ancho) || 7;
            h = parseFloat(m.plano_alto) || 7;
        } else {
            w = 6;
            h = 6;
            x = 42 + (idxSin % 5) * 10;
            y = 6 + Math.floor(idxSin / 5) * 9;
            idxSin++;
        }

        var forma = getFormaMesa(m);
        var ordenes = getOrdenesMesa(m.id);
        var res = resumenOrdenesMesa(ordenes);
        var extras = [];
        extras.push(m.estado_codigo === 'MESA_OCUPADA' ? 'ocupada' : 'disponible');
        if (res.pendientes > 0) extras.push('con-orden-pendiente');
        else if (res.pagadas > 0) extras.push('con-orden-pagada');
        if (String(m.id) === String(mesaActual) && mesaActual !== '-1') extras.push('mesa-actual');

        var clases = ['pos-plano-mesa', 'forma-' + forma].concat(extras);
        var badge = '';
        if (ordenes.length > 0) {
            var badgeTxt = res.pendientes > 0 ? res.pendientes : ordenes.length;
            var badgeCls = res.pendientes > 0 ? 'pos-plano-mesa-badge pendiente' : 'pos-plano-mesa-badge pagada';
            badge = '<span class="' + badgeCls + '" title="' + escAttrPos(tooltipOrdenesMesa(m, ordenes)) + '">' + badgeTxt + '</span>';
        }

        var hint = res.pendientes > 0
            ? '<span class="pos-plano-mesa-hint">₡' + Math.round(res.saldoPend).toLocaleString('es-CR') + '</span>'
            : '<span class="pos-plano-mesa-hint">' + (m.capacidad || 0) + ' p.</span>';

        html += '<div class="' + clases.join(' ') + '" data-mesa-id="' + m.id + '" data-forma="' + forma + '"'
            + ' style="' + estiloPosicionMesa(forma, x, y, w, h) + '"'
            + ' title="' + escAttrPos(tooltipOrdenesMesa(m, ordenes)) + '">'
            + badge
            + '<span class="mesa-plano-superficie" aria-hidden="true"></span>'
            + '<span class="pos-plano-mesa-num">' + escHtmlPos(m.numero_mesa) + '</span>'
            + hint
            + '</div>';
    });

    $('#pos-plano-mesas').html(html);
    $('#pos-plano-mesas .pos-plano-mesa').on('click', onClickMesaPlanoPos);
}

function onClickMesaPlanoPos() {
    var mesaId = $(this).data('mesa-id');
    var mesa = (posPlanoDatos.mesas || []).find(function (m) { return String(m.id) === String(mesaId); });
    if (!mesa) return;

    var ordenes = getOrdenesMesa(mesaId);
    renderizarSidebarPos(mesa, ordenes);

    if (posPlanoModo === 'seleccionar') {
        if (ordenes.length > 0) {
            var res = resumenOrdenesMesa(ordenes);
            if (res.pendientes > 0) {
                showError('Mesa ' + mesa.numero_mesa + ' tiene ' + res.pendientes + ' cuenta(s) pendiente(s). Use la pestaña «Ver órdenes por mesa» para abrirlas, o asigne otra mesa.');
                return;
            }
        }
        seleccionarMesaDesdeMapa(mesaId);
        return;
    }

    if (ordenes.length === 0) {
        showError('Mesa ' + mesa.numero_mesa + ' no tiene órdenes en la caja abierta.');
        return;
    }
    var pendientes = ordenes.filter(function (o) { return o.pagado === 0; });
    var abrir = pendientes.length ? pendientes : ordenes;
    if (abrir.length === 1) {
        abrirOrdenDesdeMapa(abrir[0].id);
    }
}

function seleccionarMesaDesdeMapa(mesaId) {
    if (window.POS_CONFIG && window.POS_CONFIG.modo === 'barra' && typeof seleccionarMesaDesdeMapaBarra === 'function') {
        seleccionarMesaDesdeMapaBarra(mesaId);
        $('#mdl-pos-plano-mesas').modal('hide');
        return;
    }
    if (typeof infoEnvio !== 'undefined' && infoEnvio.incluye_envio) {
        showError('Desactive PARA LLEVAR / envío para asignar mesa.');
        return;
    }
    $('#select_mesa').val(mesaId);
    if (typeof cambiarMesa === 'function') {
        cambiarMesa();
    } else {
        ordenGestion.mesa = mesaId;
    }
    $('#mdl-pos-plano-mesas').modal('hide');
    showSuccess('Mesa asignada desde el mapa.');
}

function abrirOrdenDesdeMapa(idOrden) {
    $('#mdl-pos-plano-mesas').modal('hide');
    if (typeof cargarOrdenGestion === 'function') {
        cargarOrdenGestion(idOrden);
    }
}

function renderizarSidebarPos(mesa, ordenes) {
    var $sb = $('#pos-plano-sidebar');
    ordenes = ordenes || [];

    if (!mesa) {
        var sinMesa = (posPlanoDatos && posPlanoDatos.ordenes_sin_mesa) || [];
        var html = '<p class="small text-muted px-2 mb-2">Toque una mesa en el mapa.</p>';
        if (sinMesa.length > 0) {
            html += '<p class="small font-weight-bold px-2 mb-1 text-info"><i class="fas fa-shopping-bag"></i> Para llevar / sin mesa (' + sinMesa.length + ')</p>';
            sinMesa.forEach(function (o) {
                html += ordenItemHtml(o);
            });
        }
        $sb.html(html);
        $sb.find('.pos-plano-orden-item').on('click', function () {
            abrirOrdenDesdeMapa($(this).data('orden-id'));
        });
        return;
    }

    var res = resumenOrdenesMesa(ordenes);
    var titulo = '<div class="pos-plano-sidebar-mesa px-2 pt-2">'
        + '<p class="mb-1"><strong>Mesa ' + escHtmlPos(mesa.numero_mesa) + '</strong>'
        + ' <span class="badge badge-' + (mesa.estado_codigo === 'MESA_OCUPADA' ? 'danger' : 'success') + '">'
        + escHtmlPos(mesa.estado_nombre || '') + '</span></p>';

    if (ordenes.length) {
        titulo += '<p class="small mb-2">';
        if (res.pendientes) {
            titulo += '<span class="badge badge-warning">' + res.pendientes + ' por cobrar</span> ';
            titulo += '<span class="text-muted">Saldo ₡' + res.saldoPend.toLocaleString('es-CR') + '</span>';
        }
        if (res.pagadas) {
            titulo += ' <span class="badge badge-success">' + res.pagadas + ' pagada(s)</span>';
        }
        titulo += '</p>';
    } else {
        titulo += '<p class="text-muted small mb-2">Sin órdenes en la caja actual.</p>';
    }
    titulo += '</div>';

    if (posPlanoModo === 'seleccionar') {
        var body = titulo
            + '<div class="px-2 pb-2">'
            + '<button type="button" class="btn btn-primary btn-sm btn-block" onclick="seleccionarMesaDesdeMapa(' + mesa.id + ')">'
            + '<i class="fas fa-check"></i> Usar esta mesa en la orden</button>';
        if (ordenes.length) {
            body += '<p class="small text-muted mt-2 mb-1">Órdenes en esta caja:</p>' + ordenes.map(ordenItemHtml).join('');
        }
        body += '</div>';
        $sb.html(body);
    } else {
        if (!ordenes.length) {
            $sb.html(titulo + '<p class="text-muted small px-2">Elija otra mesa o cree una orden en el POS.</p>');
            return;
        }
        var pendientes = ordenes.filter(function (o) { return o.pagado === 0; });
        var lista = pendientes.length ? pendientes : ordenes;
        var hint = pendientes.length
            ? '<p class="small px-2 mb-1"><strong>Toque una orden para abrirla y cobrar:</strong></p>'
            : '<p class="small px-2 mb-1 text-muted">Solo hay órdenes ya pagadas en esta mesa:</p>';
        $sb.html(titulo + hint + '<div class="px-2 pb-2">' + lista.map(ordenItemHtml).join('') + '</div>');
    }

    $sb.find('.pos-plano-orden-item').on('click', function () {
        abrirOrdenDesdeMapa($(this).data('orden-id'));
    });
}

function etiquetaEstadoOrden(o) {
    if (o.pagado === 1) return 'Pagada';
    var saldo = o.saldo != null ? o.saldo : (o.total || 0) - (o.mto_pagado || 0);
    if ((o.mto_pagado || 0) > 0 && saldo > 0.01) {
        return 'Pago parcial';
    }
    return 'Por cobrar';
}

function formatoHoraOrden(fecha) {
    if (!fecha) return '';
    try {
        var d = new Date(fecha.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
    } catch (e) {
        return '';
    }
}

function ordenItemHtml(o) {
    var cls = o.pagado === 0 ? 'pendiente' : 'pagada';
    var estado = etiquetaEstadoOrden(o);
    var hora = formatoHoraOrden(o.fecha_inicio);
    var monto = o.pagado === 0 && o.saldo > 0 ? o.saldo : (o.total || 0);
    var montoLabel = o.pagado === 0 && (o.mto_pagado || 0) > 0
        ? 'Saldo ₡' + monto.toLocaleString('es-CR')
        : '₡' + monto.toLocaleString('es-CR');

    return '<div class="pos-plano-orden-item ' + cls + '" data-orden-id="' + o.id + '">'
        + '<div class="d-flex justify-content-between align-items-start">'
        + '<strong>#' + escHtmlPos(o.numero_orden) + '</strong>'
        + '<span class="badge badge-' + (o.pagado === 0 ? 'warning' : 'success') + '">' + estado + '</span>'
        + '</div>'
        + '<div class="text-truncate">' + escHtmlPos(o.nombre_cliente || 'Sin nombre') + '</div>'
        + '<div class="d-flex justify-content-between small text-muted mt-1">'
        + '<span>' + (hora ? hora : '') + '</span>'
        + '<span class="font-weight-bold text-dark">' + montoLabel + '</span>'
        + '</div>'
        + '</div>';
}

function escHtmlPos(s) {
    return String(s == null ? '' : s).replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function escAttrPos(s) {
    return String(s == null ? '' : s).replace(/"/g, '&quot;').replace(/\n/g, '&#10;');
}

$(document).ready(function () {
    $('a[data-toggle="tab"][href="#pos-plano-tab-ordenes"]').on('shown.bs.tab', function () {
        setModoPlanoPos('ordenes');
    });
    $('a[data-toggle="tab"][href="#pos-plano-tab-seleccionar"]').on('shown.bs.tab', function () {
        setModoPlanoPos('seleccionar');
    });
});
