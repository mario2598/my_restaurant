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
    }
}

function actualizarAyudaPlanoPos() {
    var txt = posPlanoModo === 'ordenes'
        ? 'Toque una mesa con orden para abrirla. Las naranjas tienen cuenta pendiente.'
        : 'Toque una mesa para asignarla a la orden actual.';
    $('#pos-plano-ayuda').text(txt);
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

function getOrdenesMesa(mesaId) {
    if (!posPlanoDatos || !posPlanoDatos.ordenes_por_mesa) return [];
    return posPlanoDatos.ordenes_por_mesa[mesaId] || posPlanoDatos.ordenes_por_mesa[String(mesaId)] || [];
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

        var ordenes = getOrdenesMesa(m.id);
        var tienePendiente = ordenes.some(function (o) { return o.pagado === 0; });
        var tienePagada = ordenes.some(function (o) { return o.pagado === 1; });
        var clases = ['pos-plano-mesa'];
        clases.push(m.estado_codigo === 'MESA_OCUPADA' ? 'ocupada' : 'disponible');
        if (tienePendiente) clases.push('con-orden-pendiente');
        else if (tienePagada) clases.push('con-orden-pagada');
        if (String(m.id) === String(mesaActual) && mesaActual !== '-1') clases.push('mesa-actual');

        var forma = (m.forma || 'rectangular').toLowerCase();
        var badge = ordenes.length > 0
            ? '<span class="pos-plano-mesa-badge" title="' + ordenes.length + ' orden(es)">' + ordenes.length + '</span>'
            : '';

        html += '<div class="' + clases.join(' ') + ' forma-' + forma + '" data-mesa-id="' + m.id + '"'
            + ' style="left:' + x + '%;top:' + y + '%;width:' + w + '%;height:' + h + '%;"'
            + ' title="Mesa ' + escAttrPos(m.numero_mesa) + '">'
            + badge
            + '<span>' + escHtmlPos(m.numero_mesa) + '</span>'
            + '<span style="font-size:8px;font-weight:400">' + (m.capacidad || 0) + 'p</span>'
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
        seleccionarMesaDesdeMapa(mesaId);
        return;
    }

    if (ordenes.length === 0) {
        showError('Mesa ' + mesa.numero_mesa + ' sin órdenes en esta caja.');
        return;
    }
    if (ordenes.length === 1) {
        abrirOrdenDesdeMapa(ordenes[0].id);
        return;
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
    if (!mesa) {
        var sinMesa = (posPlanoDatos && posPlanoDatos.ordenes_sin_mesa) || [];
        var html = '<p class="small text-muted px-2">Toque una mesa en el mapa.</p>';
        if (sinMesa.length > 0 && posPlanoModo === 'ordenes') {
            html += '<p class="small font-weight-bold px-2 mb-1">PARA LLEVAR / sin mesa</p>';
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

    var titulo = '<p class="mb-2"><strong>Mesa ' + escHtmlPos(mesa.numero_mesa) + '</strong>'
        + ' <span class="badge badge-' + (mesa.estado_codigo === 'MESA_OCUPADA' ? 'danger' : 'success') + '">'
        + (mesa.estado_nombre || '') + '</span></p>';

    if (posPlanoModo === 'seleccionar') {
        $sb.html(titulo
            + '<button type="button" class="btn btn-primary btn-sm btn-block" onclick="seleccionarMesaDesdeMapa(' + mesa.id + ')">'
            + '<i class="fas fa-check"></i> Usar esta mesa</button>'
            + (ordenes.length ? '<hr class="my-2"><p class="small text-muted mb-1">Órdenes en caja:</p>' : '')
            + ordenes.map(ordenItemHtml).join(''));
    } else {
        if (!ordenes.length) {
            $sb.html(titulo + '<p class="text-muted small">Sin órdenes activas en esta mesa.</p>');
            return;
        }
        $sb.html(titulo + '<p class="small mb-1">Seleccione una orden:</p>' + ordenes.map(ordenItemHtml).join(''));
    }

    $sb.find('.pos-plano-orden-item').on('click', function () {
        abrirOrdenDesdeMapa($(this).data('orden-id'));
    });
}

function ordenItemHtml(o) {
    var cls = o.pagado === 0 ? 'pendiente' : 'pagada';
    var estado = o.pagado === 0 ? 'Pendiente' : 'Pagada';
    return '<div class="pos-plano-orden-item ' + cls + '" data-orden-id="' + o.id + '">'
        + '<strong>#' + escHtmlPos(o.numero_orden) + '</strong> — ' + estado
        + '<br><span class="text-muted">' + escHtmlPos(o.nombre_cliente || 'Sin nombre') + '</span>'
        + ' · ₡' + (o.total || 0).toLocaleString('es-CR')
        + '</div>';
}

function escHtmlPos(s) {
    return String(s == null ? '' : s).replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function escAttrPos(s) {
    return String(s == null ? '' : s).replace(/"/g, '&quot;');
}

$(document).ready(function () {
    $('a[data-toggle="tab"][href="#pos-plano-tab-ordenes"]').on('shown.bs.tab', function () {
        setModoPlanoPos('ordenes');
    });
    $('a[data-toggle="tab"][href="#pos-plano-tab-seleccionar"]').on('shown.bs.tab', function () {
        setModoPlanoPos('seleccionar');
    });
});
