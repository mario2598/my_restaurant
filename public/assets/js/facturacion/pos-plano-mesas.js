/**
 * Mapa de mesas en POS: seleccionar mesa, órdenes por mesa y listado general (solo pendientes de cobro).
 */
var posPlanoDatos = null;
var posPlanoModo = 'seleccionar';

function abrirMapaMesas(modo) {
    posPlanoModo = modo || 'seleccionar';
    if (posPlanoModo === 'ordenes') {
        $('#tab-pos-plano-ordenes').tab('show');
    } else if (posPlanoModo === 'generales') {
        $('#tab-pos-plano-generales').tab('show');
    } else {
        $('#tab-pos-plano-seleccionar').tab('show');
    }
    actualizarLayoutPlanoPos();
    actualizarAyudaPlanoPos();
    $('#mdl-pos-plano-mesas').modal('show');
    cargarPlanoPos();
}

function setModoPlanoPos(modo) {
    posPlanoModo = modo;
    actualizarLayoutPlanoPos();
    actualizarAyudaPlanoPos();
    if (posPlanoDatos) {
        if (posPlanoModo !== 'generales') {
            renderizarPlanoPos();
        }
        renderizarResumenPlanoPos();
        if (posPlanoModo === 'generales') {
            renderizarListaGeneralesPos();
        }
    }
}

function actualizarLayoutPlanoPos() {
    var esGeneral = posPlanoModo === 'generales';
    $('#pos-plano-layout-mapa').toggleClass('d-none', esGeneral);
    $('#pos-plano-layout-generales').toggleClass('d-none', !esGeneral);
    $('#pos-plano-ayuda').toggleClass('d-none', esGeneral);
}

function actualizarAyudaPlanoPos() {
    var txt;
    if (posPlanoModo === 'ordenes') {
        txt = 'Mesas con borde naranja tienen cuenta por cobrar. Toque la mesa y elija la orden en el panel derecho.';
    } else if (posPlanoModo === 'seleccionar') {
        txt = 'Toque una mesa libre (verde) para asignarla a la orden actual. Si la mesa ya tiene cuentas pendientes, use la pestaña «Órdenes por mesa».';
    } else {
        txt = '';
    }
    $('#pos-plano-ayuda').text(txt);
}

function getOrdenesMesa(mesaId) {
    if (!posPlanoDatos || !posPlanoDatos.ordenes_por_mesa) return [];
    return posPlanoDatos.ordenes_por_mesa[mesaId] || posPlanoDatos.ordenes_por_mesa[String(mesaId)] || [];
}

function getOrdenesPendientesPlano() {
    if (!posPlanoDatos) return [];
    if (posPlanoDatos.ordenes_pendientes && posPlanoDatos.ordenes_pendientes.length) {
        return posPlanoDatos.ordenes_pendientes;
    }
    var todas = [];
    var porMesa = posPlanoDatos.ordenes_por_mesa || {};
    Object.keys(porMesa).forEach(function (mesaId) {
        (porMesa[mesaId] || []).forEach(function (o) { todas.push(o); });
    });
    (posPlanoDatos.ordenes_sin_mesa || []).forEach(function (o) { todas.push(o); });
    return todas;
}

function contarOrdenesPlano() {
    var pendientes = getOrdenesPendientesPlano().length;
    var mesasConPendiente = 0;
    var porMesa = posPlanoDatos.ordenes_por_mesa || {};
    Object.keys(porMesa).forEach(function (mesaId) {
        if ((porMesa[mesaId] || []).length > 0) mesasConPendiente++;
    });
    return {
        pendientes: pendientes,
        mesasConPendiente: mesasConPendiente,
        sinMesa: (posPlanoDatos.ordenes_sin_mesa || []).length
    };
}

function renderizarResumenPlanoPos() {
    var $box = $('#pos-plano-resumen');
    if (!posPlanoDatos) {
        $box.addClass('d-none');
        return;
    }
    var c = contarOrdenesPlano();
    if (c.pendientes === 0) {
        $box.addClass('d-none');
        return;
    }
    var html = '<strong>Caja actual:</strong> ';
    html += '<span class="text-warning">' + c.pendientes + ' cuenta(s) por cobrar</span>';
    if (c.mesasConPendiente > 0) {
        html += ' en <span class="text-warning">' + c.mesasConPendiente + ' mesa(s)</span>';
    }
    if (c.sinMesa > 0) {
        html += ' · <span class="text-info">' + c.sinMesa + ' sin mesa / para llevar</span>';
    }
    if (posPlanoModo === 'ordenes') {
        html += '<br><span class="text-muted">Solo se muestran órdenes no pagadas.</span>';
    } else if (posPlanoModo === 'generales') {
        html += '<br><span class="text-muted">Listado de todas las cuentas pendientes de esta caja.</span>';
    }
    $box.html(html).removeClass('d-none');
}

function cargarPlanoPos() {
    $('#pos-plano-zonas, #pos-plano-mesas').html('');
    $('#pos-plano-sidebar').html('<p class="text-muted small p-2"><i class="fas fa-spinner fa-spin"></i> Cargando mapa…</p>');
    $('#pos-plano-lista-generales').html('<p class="text-muted small mb-0"><i class="fas fa-spinner fa-spin"></i> Cargando…</p>');
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
        var mesas = posPlanoDatos.mesas || [];
        var ar = posPlanoDatos.ancho_referencia || 100;
        var al = posPlanoDatos.alto_referencia || 150;
        $('#pos-plano-canvas').css('aspect-ratio', ar + ' / ' + al);
        renderizarZonasPos(posPlanoDatos.zonas || []);
        actualizarLayoutPlanoPos();
        if (posPlanoModo !== 'generales') {
            renderizarPlanoPos();
        }
        renderizarResumenPlanoPos();
        actualizarAyudaPlanoPos();
        renderizarListaGeneralesPos();
        if (!mesas.length && posPlanoModo !== 'generales') {
            $('#pos-plano-sidebar').html(
                '<p class="text-warning small p-2 mb-0"><i class="fas fa-exclamation-triangle"></i> '
                + 'No hay mesas en esta sucursal. Créelas en Mobiliario → Administrar mesas y ubíquelas en el plano.</p>'
            );
        } else if (posPlanoModo !== 'generales') {
            renderizarSidebarPos(null);
        }
    }).fail(function (jqXHR) {
        var msg = 'No se pudo cargar el mapa.';
        if (jqXHR.responseJSON && jqXHR.responseJSON.mensaje) {
            msg = jqXHR.responseJSON.mensaje;
        }
        showError(msg + ' Configure el plano en Mobiliario → Plano de mesas.');
        $('#pos-plano-sidebar').html('<p class="text-danger small p-2">' + escHtmlPos(msg) + '</p>');
        $('#pos-plano-lista-generales').html('<p class="text-danger small mb-0">' + escHtmlPos(msg) + '</p>');
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
    var saldoPend = ordenes.reduce(function (s, o) {
        return s + (o.saldo != null ? o.saldo : o.total || 0);
    }, 0);
    return { pendientes: ordenes.length, saldoPend: saldoPend };
}

function tooltipOrdenesMesa(m, ordenes) {
    if (!ordenes.length) {
        return 'Mesa ' + m.numero_mesa + ' — libre en esta caja';
    }
    var r = resumenOrdenesMesa(ordenes);
    var lines = ['Mesa ' + m.numero_mesa];
    lines.push(r.pendientes + ' por cobrar — saldo ₡' + r.saldoPend.toLocaleString('es-CR'));
    ordenes.slice(0, 4).forEach(function (o) {
        lines.push('#' + o.numero_orden + ' ' + etiquetaEstadoOrden(o) + ' ₡' + (o.saldo || o.total || 0).toLocaleString('es-CR'));
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
        if (String(m.id) === String(mesaActual) && mesaActual !== '-1') extras.push('mesa-actual');

        var clases = ['pos-plano-mesa', 'forma-' + forma].concat(extras);
        var badge = '';
        if (res.pendientes > 0) {
            badge = '<span class="pos-plano-mesa-badge pendiente" title="' + escAttrPos(tooltipOrdenesMesa(m, ordenes)) + '">'
                + res.pendientes + '</span>';
        }

        var hint = res.pendientes > 0
            ? '<span class="pos-plano-mesa-hint">₡' + Math.round(res.saldoPend).toLocaleString('es-CR') + '</span>'
            : '<span class="pos-plano-mesa-hint">' + (m.capacidad || 0) + ' p.</span>';

        var estiloFn = typeof estiloPosicionMesaPos === 'function' ? estiloPosicionMesaPos : estiloPosicionMesa;
        var etiqueta = typeof etiquetaCortaMesa === 'function' ? etiquetaCortaMesa(m.numero_mesa) : m.numero_mesa;

        html += '<div class="' + clases.join(' ') + '" data-mesa-id="' + m.id + '" data-forma="' + forma + '"'
            + ' style="' + estiloFn(forma, x, y, w, h) + '"'
            + ' title="' + escAttrPos(tooltipOrdenesMesa(m, ordenes)) + '">'
            + badge
            + '<span class="mesa-plano-superficie" aria-hidden="true"></span>'
            + '<span class="pos-plano-mesa-num">' + escHtmlPos(etiqueta) + '</span>'
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
            showError('Mesa ' + mesa.numero_mesa + ' tiene ' + ordenes.length + ' cuenta(s) por cobrar. Use «Órdenes por mesa» para abrirlas.');
            return;
        }
        seleccionarMesaDesdeMapa(mesaId);
        return;
    }

    if (ordenes.length === 0) {
        showError('Mesa ' + mesa.numero_mesa + ' no tiene cuentas pendientes en esta caja.');
        return;
    }
    if (ordenes.length === 1) {
        abrirOrdenDesdeMapa(ordenes[0].id);
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

function renderizarListaGeneralesPos() {
    var $lista = $('#pos-plano-lista-generales');
    if (!$lista.length) return;
    if (!posPlanoDatos) {
        $lista.html('<p class="text-muted small mb-0">Sin datos.</p>');
        return;
    }
    var ordenes = getOrdenesPendientesPlano();
    if (!ordenes.length) {
        $lista.html('<p class="text-muted small mb-0"><i class="fas fa-check-circle text-success"></i> No hay cuentas pendientes de cobro en esta caja.</p>');
        return;
    }
    var html = '<p class="small font-weight-bold mb-2">' + ordenes.length + ' cuenta(s) por cobrar — toque para abrir</p>';
    ordenes.forEach(function (o) {
        html += ordenItemHtml(o, true);
    });
    $lista.html(html);
    $lista.find('.pos-plano-orden-item').on('click', function () {
        abrirOrdenDesdeMapa($(this).data('orden-id'));
    });
}

function renderizarSidebarPos(mesa, ordenes) {
    var $sb = $('#pos-plano-sidebar');
    ordenes = ordenes || [];

    if (!mesa) {
        var sinMesa = (posPlanoDatos && posPlanoDatos.ordenes_sin_mesa) || [];
        var html = '<p class="small text-muted px-2 mb-2">Toque una mesa en el mapa.</p>';
        if (sinMesa.length > 0) {
            html += '<p class="small font-weight-bold px-2 mb-1 text-info"><i class="fas fa-shopping-bag"></i> Sin mesa (' + sinMesa.length + ')</p>';
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
        titulo += '<p class="small mb-2">'
            + '<span class="badge badge-warning">' + res.pendientes + ' por cobrar</span> '
            + '<span class="text-muted">Saldo ₡' + res.saldoPend.toLocaleString('es-CR') + '</span></p>';
    } else {
        titulo += '<p class="text-muted small mb-2">Sin cuentas pendientes en esta caja.</p>';
    }
    titulo += '</div>';

    if (posPlanoModo === 'seleccionar') {
        var body = titulo
            + '<div class="px-2 pb-2">'
            + '<button type="button" class="btn btn-primary btn-sm btn-block" onclick="seleccionarMesaDesdeMapa(' + mesa.id + ')">'
            + '<i class="fas fa-check"></i> Usar esta mesa en la orden</button>';
        if (ordenes.length) {
            body += '<p class="small text-muted mt-2 mb-1">Cuentas pendientes:</p>' + ordenes.map(function (o) { return ordenItemHtml(o); }).join('');
        }
        body += '</div>';
        $sb.html(body);
    } else {
        if (!ordenes.length) {
            $sb.html(titulo + '<p class="text-muted small px-2">Elija otra mesa con cuenta pendiente.</p>');
            return;
        }
        $sb.html(titulo
            + '<p class="small px-2 mb-1"><strong>Toque una orden para abrirla:</strong></p>'
            + '<div class="px-2 pb-2">' + ordenes.map(function (o) { return ordenItemHtml(o); }).join('') + '</div>');
    }

    $sb.find('.pos-plano-orden-item').on('click', function () {
        abrirOrdenDesdeMapa($(this).data('orden-id'));
    });
}

function etiquetaEstadoOrden(o) {
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

function ordenItemHtml(o, conMesa) {
    var estado = etiquetaEstadoOrden(o);
    var hora = formatoHoraOrden(o.fecha_inicio);
    var monto = o.saldo > 0 ? o.saldo : (o.total || 0);
    var montoLabel = (o.mto_pagado || 0) > 0
        ? 'Saldo ₡' + monto.toLocaleString('es-CR')
        : '₡' + monto.toLocaleString('es-CR');
    var mesaTxt = '';
    if (conMesa) {
        mesaTxt = o.numero_mesa
            ? '<span class="badge badge-secondary mr-1">Mesa ' + escHtmlPos(o.numero_mesa) + '</span>'
            : '<span class="badge badge-info mr-1">Sin mesa</span>';
    }

    return '<div class="pos-plano-orden-item pendiente" data-orden-id="' + o.id + '">'
        + '<div class="d-flex justify-content-between align-items-start flex-wrap">'
        + '<div>' + mesaTxt + '<strong>#' + escHtmlPos(o.numero_orden) + '</strong></div>'
        + '<span class="badge badge-warning">' + estado + '</span>'
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
    $('a[data-toggle="tab"][href="#pos-plano-tab-generales"]').on('shown.bs.tab', function () {
        setModoPlanoPos('generales');
    });
});
