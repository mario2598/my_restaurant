/**
 * Mapa de mesas en POS: toque mesa → desglose de cuentas + asignar mesa / abrir factura.
 */
var posPlanoDatos = null;
var posPlanoModo = 'mapa';
var posPlanoMesaSeleccionadaId = null;

/**
 * Acceso directo: abre el modal del mapa sin pasar por el panel de opciones.
 */
function abrirMapaDirectoPos() {
    pulsarBotonMapaMesaPos();
    abrirMapaMesas('seleccionar');
}

/**
 * Panel flotante del mapa (abajo), estilo FAB de mesas.
 * @param {boolean|undefined} forzarAbrir true=abrir, false=cerrar, undefined=toggle
 */
function togglePanelMapaFab(forzarAbrir) {
    var panel = document.getElementById('panel-mapa-fab');
    var btn = document.getElementById('btn-toggle-mapa-fab');
    var icon = document.getElementById('icon-toggle-mapa');
    if (!panel || !btn) {
        return;
    }
    var abrir = forzarAbrir === true
        ? true
        : (forzarAbrir === false ? false : !panel.classList.contains('mostrar'));

    if (abrir) {
        panel.classList.add('mostrar');
        btn.setAttribute('aria-expanded', 'true');
        if (icon) {
            var arriba = window.matchMedia('(max-width: 991.98px)').matches;
            icon.className = arriba ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
        }
        actualizarBotonMapaMesaPos();
    } else {
        panel.classList.remove('mostrar');
        btn.setAttribute('aria-expanded', 'false');
        if (icon) {
            icon.className = 'fas fa-ellipsis-h';
        }
    }
}

/**
 * Refleja en el FAB del mapa la mesa actual del selector (badge + colores).
 */
function actualizarBotonMapaMesaPos() {
    var $btn = $('#btn-mapa-directo');
    var $badge = $('#mapa-fab-badge');
    var $info = $('#mapa-fab-mesa-actual');
    if (!$btn.length) {
        return;
    }
    var val = String($('#select_mesa').val() || '-1');
    var $opt = $('#select_mesa option:selected');
    var esMesa = val !== '' && val !== '-1';

    $btn.removeClass('pos-fab-mapa--con-mesa pos-fab-mapa--para-llevar');

    if (esMesa) {
        $btn.addClass('pos-fab-mapa--con-mesa');
        var etiqueta = ($opt.attr('title') || $opt.text() || '').trim();
        var match = etiqueta.match(/Mesa\s*:\s*([^,]+)/i);
        var num = match ? match[1].trim() : val;
        if (num.length > 8) {
            num = num.substring(0, 8);
        }
        $badge.text(num).show();
        $btn.attr('title', 'Mesa ' + num + ' — toque para abrir el mapa');
        if ($info.length) {
            $info.html('<i class="fas fa-chair text-success"></i> Mesa asignada: <strong>' + escHtmlPos(num) + '</strong>');
        }
    } else {
        $btn.addClass('pos-fab-mapa--para-llevar');
        $badge.hide().text('');
        $btn.attr('title', 'Abrir el plano del local de inmediato');
        if ($info.length) {
            $info.html('<i class="fas fa-shopping-bag"></i> Sin mesa — PARA LLEVAR');
        }
    }
}

function pulsarBotonMapaMesaPos() {
    var $btn = $('#btn-mapa-directo');
    if (!$btn.length) {
        return;
    }
    $btn.addClass('pos-fab-mapa--click');
    setTimeout(function () {
        $btn.removeClass('pos-fab-mapa--click');
    }, 220);
}

function abrirMapaMesas(modo) {
    if (typeof togglePanelMapaFab === 'function') {
        togglePanelMapaFab(false);
    }
    posPlanoModo = (modo === 'generales') ? 'generales' : 'mapa';
    posPlanoMesaSeleccionadaId = null;

    if (posPlanoModo === 'generales') {
        $('#tab-pos-plano-generales').tab('show');
    } else {
        $('#tab-pos-plano-mapa').tab('show');
    }
    actualizarLayoutPlanoPos();
    actualizarAyudaPlanoPos();
    $('#mdl-pos-plano-mesas').modal('show');
    cargarPlanoPos();
}

function setModoPlanoPos(modo) {
    posPlanoModo = modo;
    if (posPlanoModo === 'mapa') {
        posPlanoMesaSeleccionadaId = null;
    }
    actualizarLayoutPlanoPos();
    actualizarAyudaPlanoPos();
    if (posPlanoDatos) {
        if (posPlanoModo !== 'generales') {
            renderizarPlanoPos();
            renderizarSidebarPos(null);
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

function esVistaPlanoMovil() {
    return window.matchMedia('(max-width: 991.98px)').matches;
}

function actualizarEstadoSidebarMovil(mesa) {
    var activo = !!(mesa && esVistaPlanoMovil());
    $('#pos-plano-layout-mapa').toggleClass('pos-plano-mesa-elegida', activo);
    $('#pos-plano-col-sidebar').toggleClass('pos-plano-sidebar--activo', activo);
    if (posPlanoDatos) {
        aplicarProporcionCanvasPos();
    }
    var $hint = $('#pos-plano-detalle-hint');
    if (!$hint.length) {
        return;
    }
    $hint.toggleClass('d-none', !activo);
    if (activo) {
        $hint.find('.pos-plano-detalle-hint__mesa').text('Mesa ' + (mesa.numero_mesa || ''));
    }
}

function scrollDetalleMesaPlanoPos() {
    if (!esVistaPlanoMovil()) {
        return;
    }
    var target = document.getElementById('pos-plano-detalle-hint')
        || document.getElementById('pos-plano-col-sidebar');
    if (!target) {
        return;
    }
    setTimeout(function () {
        try {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) {
            target.scrollIntoView(true);
        }
        $('#pos-plano-col-sidebar').addClass('pos-plano-sidebar-flash');
        setTimeout(function () {
            $('#pos-plano-col-sidebar').removeClass('pos-plano-sidebar-flash');
        }, 1400);
    }, 100);
}

function actualizarAyudaPlanoPos() {
    if (posPlanoModo === 'generales') {
        $('#pos-plano-ayuda').addClass('d-none');
        return;
    }
    if (esVistaPlanoMovil()) {
        $('#pos-plano-ayuda').removeClass('d-none').html(
            '<i class="fas fa-hand-pointer"></i> Toque una mesa: el detalle aparecerá <strong>debajo del mapa</strong> '
            + '(la pantalla bajará sola).'
        );
        return;
    }
    $('#pos-plano-ayuda').removeClass('d-none').html(
        '<i class="fas fa-hand-pointer"></i> <strong>Paso 1:</strong> toque una mesa en el plano. '
        + '<strong>Paso 2:</strong> en el panel derecho abra una cuenta o asigne la mesa a su orden.'
    );
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
    var html = '<i class="fas fa-receipt text-warning"></i> <strong>' + c.pendientes + ' cuenta(s) por cobrar</strong>';
    if (c.mesasConPendiente > 0) {
        html += ' en ' + c.mesasConPendiente + ' mesa(s)';
    }
    if (c.sinMesa > 0) {
        html += ' · ' + c.sinMesa + ' sin mesa';
    }
    $box.html(html).removeClass('d-none');
}

function marcarMesaSeleccionadaPlano(mesaId) {
    posPlanoMesaSeleccionadaId = mesaId ? String(mesaId) : null;
    $('#pos-plano-mesas .pos-plano-mesa').removeClass('mesa-seleccionada-mapa');
    if (posPlanoMesaSeleccionadaId) {
        $('#pos-plano-mesas .pos-plano-mesa[data-mesa-id="' + posPlanoMesaSeleccionadaId + '"]')
            .addClass('mesa-seleccionada-mapa');
    }
}

/**
 * Mantiene la proporción ancho_referencia × alto_referencia sin deformar el canvas.
 * En móvil calcula ancho/alto en px para caber en el modal sin romper el % de mesas/zonas.
 */
function aplicarProporcionCanvasPos() {
    if (!posPlanoDatos) {
        return;
    }
    var ar = parseInt(posPlanoDatos.ancho_referencia, 10) || 100;
    var al = parseInt(posPlanoDatos.alto_referencia, 10) || 150;
    var $scaler = $('#pos-plano-canvas-scaler');
    var $c = $('#pos-plano-canvas');
    if (!$c.length) {
        return;
    }

    $c.css({
        '--plano-ar': ar,
        '--plano-al': al
    });

    if (!esVistaPlanoMovil()) {
        $scaler.css({ width: '', height: '', maxWidth: '', maxHeight: '' });
        $c.css({
            aspectRatio: ar + ' / ' + al,
            width: '100%',
            maxWidth: '900px',
            height: 'auto',
            minHeight: '0',
            maxHeight: 'none'
        });
        return;
    }

    var mesaElegida = $('#pos-plano-layout-mapa').hasClass('pos-plano-mesa-elegida');
    var maxVh = mesaElegida ? 34 : 48;
    var $col = $('#pos-plano-col-mapa');
    var pad = 24;
    var availW = Math.max(200, ($col.innerWidth() || $scaler.parent().innerWidth() || 320) - pad);
    var maxH = Math.max(160, (window.innerHeight * maxVh) / 100);
    var ratio = ar / al;
    var w = Math.min(availW, maxH * ratio, 900);
    var h = w / ratio;

    $scaler.css({
        width: '100%',
        maxWidth: '900px',
        margin: '0 auto',
        display: 'flex',
        justifyContent: 'center'
    });
    $c.css({
        aspectRatio: 'unset',
        width: Math.round(w) + 'px',
        height: Math.round(h) + 'px',
        maxWidth: '100%',
        minHeight: '0',
        maxHeight: 'none'
    });
}

function cargarPlanoPos() {
    posPlanoMesaSeleccionadaId = null;
    $('#pos-plano-zonas, #pos-plano-mesas').html('');
    renderizarSidebarPos(null);
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
        renderizarZonasPos(posPlanoDatos.zonas || []);
        actualizarLayoutPlanoPos();
        if (posPlanoModo !== 'generales') {
            renderizarPlanoPos();
            renderizarSidebarPos(null);
        }
        renderizarResumenPlanoPos();
        actualizarAyudaPlanoPos();
        renderizarListaGeneralesPos();
        aplicarProporcionCanvasPos();
        setTimeout(aplicarProporcionCanvasPos, 80);
        setTimeout(aplicarProporcionCanvasPos, 350);
    }).fail(function (jqXHR) {
        var msg = 'No se pudo cargar el mapa.';
        if (jqXHR.responseJSON && jqXHR.responseJSON.mensaje) {
            msg = jqXHR.responseJSON.mensaje;
        }
        showError(msg);
        $('#pos-plano-sidebar').html('<p class="text-danger small p-3">' + escHtmlPos(msg) + '</p>');
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
        return 'Mesa ' + m.numero_mesa + ' — libre';
    }
    var r = resumenOrdenesMesa(ordenes);
    return 'Mesa ' + m.numero_mesa + ': ' + r.pendientes + ' cuenta(s), saldo ₡' + r.saldoPend.toLocaleString('es-CR');
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
        if (String(m.id) === posPlanoMesaSeleccionadaId) extras.push('mesa-seleccionada-mapa');

        var badge = '';
        if (res.pendientes > 0) {
            badge = '<span class="pos-plano-mesa-badge pendiente">' + res.pendientes + '</span>';
        }

        var hint = res.pendientes > 0
            ? '<span class="pos-plano-mesa-hint">₡' + Math.round(res.saldoPend).toLocaleString('es-CR') + '</span>'
            : '<span class="pos-plano-mesa-hint">' + (m.capacidad || 0) + ' p.</span>';

        html += '<div class="pos-plano-mesa forma-' + forma + ' ' + extras.join(' ') + '" data-mesa-id="' + m.id + '" data-forma="' + forma + '"'
            + ' style="' + estiloPosicionMesa(forma, x, y, w, h) + '"'
            + ' title="' + escAttrPos(tooltipOrdenesMesa(m, ordenes)) + '">'
            + (typeof htmlContenidoMesaPlanoPos === 'function'
                ? htmlContenidoMesaPlanoPos(m, hint)
                : '')
            + badge
            + '</div>';
    });

    $('#pos-plano-mesas').html(html);
    $('#pos-plano-mesas .pos-plano-mesa').on('click', onClickMesaPlanoPos);
}

function onClickMesaPlanoPos() {
    var mesaId = $(this).data('mesa-id');
    var mesa = (posPlanoDatos.mesas || []).find(function (m) { return String(m.id) === String(mesaId); });
    if (!mesa) return;

    marcarMesaSeleccionadaPlano(mesaId);
    renderizarSidebarPos(mesa, getOrdenesMesa(mesaId));
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
    } else if (typeof ordenGestion !== 'undefined') {
        ordenGestion.mesa = mesaId;
    }
    $('#mdl-pos-plano-mesas').modal('hide');
    actualizarBotonMapaMesaPos();
    showSuccess('Mesa asignada correctamente.');
}

function abrirOrdenDesdeMapa(idOrden) {
    $('#mdl-pos-plano-mesas').modal('hide');
    if (typeof cargarOrdenGestion === 'function') {
        cargarOrdenGestion(idOrden);
    }
}

function puedeAsignarMesaPlano() {
    if (window.POS_CONFIG && window.POS_CONFIG.modo === 'barra') {
        return true;
    }
    if (typeof infoEnvio !== 'undefined' && infoEnvio.incluye_envio) {
        return false;
    }
    return true;
}

function htmlSidebarWelcome() {
    var sinMesa = (posPlanoDatos && posPlanoDatos.ordenes_sin_mesa) || [];
    var html = '<div class="pos-plano-sidebar-welcome">'
        + '<div class="pos-plano-welcome-icon"><i class="fas fa-hand-pointer"></i></div>'
        + '<p class="mb-1 font-weight-bold">Seleccione una mesa</p>'
        + '<p class="small text-muted mb-2">En el plano verá el detalle de cuentas y podrá abrir cada factura o asignar la mesa.</p>'
        + '<ol class="small text-muted pl-3 mb-0">'
        + '<li>Toque la mesa en el mapa</li>'
        + '<li>Elija <em>Abrir cuenta</em> o <em>Asignar mesa</em></li>'
        + '</ol>'
        + '</div>';
    if (sinMesa.length) {
        html += '<div class="pos-plano-sin-mesa-block px-2 pb-2">'
            + '<p class="small font-weight-bold text-info mb-2"><i class="fas fa-shopping-bag"></i> Cuentas sin mesa (' + sinMesa.length + ')</p>';
        sinMesa.forEach(function (o) {
            html += ordenCuentaCardHtml(o);
        });
        html += '</div>';
    }
    return html;
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
        $lista.html('<div class="pos-plano-sidebar-welcome p-3">'
            + '<div class="pos-plano-welcome-icon text-success"><i class="fas fa-check-circle"></i></div>'
            + '<p class="mb-0 font-weight-bold">No hay cuentas pendientes</p>'
            + '<p class="small text-muted mb-0">Todas las órdenes de esta caja están cobradas.</p></div>');
        return;
    }
    var html = '<p class="small font-weight-bold mb-2 px-1">' + ordenes.length + ' cuenta(s) pendientes</p>';
    ordenes.forEach(function (o) {
        html += ordenCuentaCardHtml(o, true);
    });
    $lista.html(html);
    enlazarBotonesCuentaPlano($lista);
}

function renderizarSidebarPos(mesa, ordenes) {
    var $sb = $('#pos-plano-sidebar');
    ordenes = ordenes || [];

    if (!mesa) {
        $sb.html(htmlSidebarWelcome());
        enlazarBotonesCuentaPlano($sb);
        actualizarEstadoSidebarMovil(null);
        aplicarProporcionCanvasPos();
        return;
    }

    var res = resumenOrdenesMesa(ordenes);
    var estadoBadge = mesa.estado_codigo === 'MESA_OCUPADA' ? 'danger' : 'success';
    var mesaActual = typeof ordenGestion !== 'undefined' ? String(ordenGestion.mesa) : '';
    var esMesaActual = String(mesa.id) === mesaActual && mesaActual !== '' && mesaActual !== '-1';

    var html = '<div class="pos-plano-sidebar-header">'
        + '<button type="button" class="btn btn-link btn-sm pos-plano-btn-volver p-0 mb-1" onclick="volverSeleccionMesaPlano()">'
        + '<i class="fas fa-arrow-left"></i> Ver otra mesa</button>'
        + '<div class="d-flex justify-content-between align-items-start">'
        + '<div>'
        + '<h6 class="mb-0 font-weight-bold">Mesa ' + escHtmlPos(mesa.numero_mesa) + '</h6>'
        + '<span class="small text-muted">' + (mesa.capacidad || 0) + ' personas</span>'
        + '</div>'
        + '<span class="badge badge-' + estadoBadge + '">' + escHtmlPos(mesa.estado_nombre || (estadoBadge === 'danger' ? 'Ocupada' : 'Libre')) + '</span>'
        + '</div>';
    if (esMesaActual) {
        html += '<span class="badge badge-primary mt-1"><i class="fas fa-star"></i> Mesa de su orden actual</span>';
    }
    html += '</div>';

    if (ordenes.length) {
        html += '<div class="pos-plano-cuentas-titulo px-2 py-2">'
            + '<i class="fas fa-file-invoice-dollar text-warning"></i> '
            + '<strong>' + ordenes.length + ' cuenta(s)</strong>'
            + ' <span class="text-muted">· Saldo total ₡' + res.saldoPend.toLocaleString('es-CR') + '</span>'
            + '</div>'
            + '<div class="pos-plano-cuentas-lista px-2">';
        ordenes.forEach(function (o) {
            html += ordenCuentaCardHtml(o);
        });
        html += '</div>';
    } else {
        html += '<div class="px-3 py-2 text-center">'
            + '<p class="small text-muted mb-0"><i class="fas fa-check-circle text-success"></i> Sin cuentas pendientes en esta mesa.</p>'
            + '</div>';
    }

    html += '<div class="pos-plano-acciones-mesa">';
    if (puedeAsignarMesaPlano()) {
        html += '<button type="button" class="btn btn-success btn-block btn-sm" onclick="seleccionarMesaDesdeMapa(' + mesa.id + ')">'
            + '<i class="fas fa-chair"></i> Asignar esta mesa a mi orden</button>'
            + '<p class="small text-muted text-center mb-0 mt-1">La orden en pantalla quedará en esta mesa.</p>';
    } else {
        html += '<p class="small text-warning text-center mb-0">Desactive «Para llevar» para asignar mesa.</p>';
    }
    html += '</div>';

    $sb.html(html);
    enlazarBotonesCuentaPlano($sb);
    actualizarEstadoSidebarMovil(mesa);
    aplicarProporcionCanvasPos();
    scrollDetalleMesaPlanoPos();
}

function volverSeleccionMesaPlano() {
    posPlanoMesaSeleccionadaId = null;
    marcarMesaSeleccionadaPlano(null);
    renderizarSidebarPos(null);
    renderizarPlanoPos();
    actualizarEstadoSidebarMovil(null);
}

function enlazarBotonesCuentaPlano($root) {
    $root.find('[data-abrir-orden]').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        abrirOrdenDesdeMapa($(this).data('abrir-orden'));
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

function ordenCuentaCardHtml(o, conMesa) {
    var estado = etiquetaEstadoOrden(o);
    var hora = formatoHoraOrden(o.fecha_inicio);
    var monto = o.saldo > 0 ? o.saldo : (o.total || 0);
    var montoLabel = (o.mto_pagado || 0) > 0
        ? 'Saldo ₡' + monto.toLocaleString('es-CR')
        : 'Total ₡' + monto.toLocaleString('es-CR');
    var badgeEstado = (o.mto_pagado || 0) > 0 ? 'badge-info' : 'badge-warning';
    var mesaLinea = '';
    if (conMesa) {
        mesaLinea = o.numero_mesa
            ? '<span class="pos-plano-cuenta-mesa"><i class="fas fa-chair"></i> ' + escHtmlPos(o.numero_mesa) + '</span>'
            : '<span class="pos-plano-cuenta-mesa"><i class="fas fa-shopping-bag"></i> Sin mesa</span>';
    }

    return '<div class="pos-plano-cuenta-card">'
        + '<div class="pos-plano-cuenta-card__head">'
        + '<div>'
        + (mesaLinea ? mesaLinea : '')
        + '<span class="pos-plano-cuenta-num">Factura #' + escHtmlPos(o.numero_orden) + '</span>'
        + (hora ? '<span class="pos-plano-cuenta-hora">' + hora + '</span>' : '')
        + '</div>'
        + '<span class="badge ' + badgeEstado + '">' + estado + '</span>'
        + '</div>'
        + '<div class="pos-plano-cuenta-cliente">' + escHtmlPos(o.nombre_cliente || 'Cliente general') + '</div>'
        + '<div class="pos-plano-cuenta-monto">' + montoLabel + '</div>'
        + '<button type="button" class="btn btn-warning btn-sm btn-block" data-abrir-orden="' + o.id + '">'
        + '<i class="fas fa-folder-open"></i> Abrir cuenta</button>'
        + '</div>';
}

function escHtmlPos(s) {
    return String(s == null ? '' : s).replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function escAttrPos(s) {
    return String(s == null ? '' : s).replace(/"/g, '&quot;').replace(/\n/g, '&#10;');
}

$(document).ready(function () {
    actualizarBotonMapaMesaPos();
    $('#select_mesa').on('change', actualizarBotonMapaMesaPos);

    $('#mdl-pos-plano-mesas').on('shown.bs.modal', function () {
        if (posPlanoDatos) {
            aplicarProporcionCanvasPos();
            setTimeout(aplicarProporcionCanvasPos, 60);
            setTimeout(aplicarProporcionCanvasPos, 320);
        }
    });

    $(window).on('resize', function () {
        if ($('#mdl-pos-plano-mesas').hasClass('show') && posPlanoDatos) {
            aplicarProporcionCanvasPos();
            actualizarAyudaPlanoPos();
            if (posPlanoMesaSeleccionadaId) {
                var mesaR = (posPlanoDatos.mesas || []).find(function (m) {
                    return String(m.id) === String(posPlanoMesaSeleccionadaId);
                });
                actualizarEstadoSidebarMovil(mesaR || null);
            }
        }
    });

    $(document).on('click', function (e) {
        var $wrap = $('#mapa-flotante-container');
        if (!$wrap.length || !$('#panel-mapa-fab').hasClass('mostrar')) {
            return;
        }
        if ($(e.target).closest('#mapa-flotante-container').length) {
            return;
        }
        togglePanelMapaFab(false);
    });

    $('a[data-toggle="tab"][href="#pos-plano-tab-mapa"]').on('shown.bs.tab', function () {
        setModoPlanoPos('mapa');
    });
    $('a[data-toggle="tab"][href="#pos-plano-tab-generales"]').on('shown.bs.tab', function () {
        setModoPlanoPos('generales');
    });
    // Compatibilidad con llamadas antiguas
    $('a[data-toggle="tab"][href="#pos-plano-tab-seleccionar"], a[data-toggle="tab"][href="#pos-plano-tab-ordenes"]')
        .on('shown.bs.tab', function () {
            setModoPlanoPos('mapa');
        });
});
