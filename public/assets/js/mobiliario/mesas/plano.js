var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var planoDatos = { mesas: [], zonas: [], areas_catalogo: [], ancho_referencia: 100, alto_referencia: 150 };
var mesaSeleccionadaId = null;
var zonaSeleccionadaId = null;
var arrastre = null;

var ZONAS_DEFAULT = [
    { id: 'cocina', nombre: 'Cocina', x: 2, y: 2, w: 40, h: 44, color: '#e9ecef' },
    { id: 'bano', nombre: 'Baño', x: 68, y: 36, w: 14, h: 18, color: '#f1f3f5' },
    { id: 'entrada', nombre: 'Entrada', x: 36, y: 80, w: 20, h: 18, color: '#fff3cd' },
    { id: 'jardin', nombre: 'Jardín', x: 70, y: 68, w: 28, h: 30, color: '#d4edda' }
];

var MIN_ZONA_PCT = 4;
var MIN_MESA_PCT = 4;
var MAX_MESA_PCT = 25;

$(document).ready(function () {
    $('input[name="modo_plano"]').on('change', aplicarModoPlano);
    if ($('#select_sucursal_plano').val()) {
        cargarPlano();
    }
    aplicarModoPlano();
});

function getModoPlano() {
    return $('input[name="modo_plano"]:checked').val() || 'mesas';
}

function aplicarModoPlano() {
    var modo = getModoPlano();
    var editarZonas = modo === 'zonas';
    $('#plano-wrapper')
        .toggleClass('modo-editar-zonas', editarZonas)
        .toggleClass('modo-editar-mesas', !editarZonas);
    $('#toolbar-mesas').toggleClass('d-none', editarZonas);
    $('#toolbar-zonas').toggleClass('d-none', !editarZonas);
    $('#card-panel-mesa').toggleClass('d-none', editarZonas);
    $('#card-panel-zona').toggleClass('d-none', !editarZonas);
    $('#card-config-areas').toggleClass('d-none', !editarZonas);
    $('#plano-ayuda').html(editarZonas
        ? '<strong>Áreas:</strong> configure las zonas en el panel derecho, arrástrelas en el plano y use las esquinas azules para el tamaño. Pulse <strong>Guardar</strong>.'
        : '<strong>Mesas:</strong> arrastre cada mesa y pulse Guardar. Referencia: <span id="plano-ref-dimensiones">'
        + (planoDatos.ancho_referencia || 100) + ' × ' + (planoDatos.alto_referencia || 150) + '</span>.');
    if (editarZonas) {
        $('.plano-mesa').css('outline', '');
        enlazarEventosZonas();
    } else {
        $('.plano-zona').removeClass('seleccionada');
        enlazarEventosMesas();
    }
}

function parsePct(val) {
    if (val == null || val === '') return 0;
    return parseFloat(String(val).replace('%', '')) || 0;
}

function aplicarEstiloZona($el, x, y, w, h) {
    $el.css({
        left: Math.max(0, Math.min(100 - MIN_ZONA_PCT, x)) + '%',
        top: Math.max(0, Math.min(100 - MIN_ZONA_PCT, y)) + '%',
        width: Math.max(MIN_ZONA_PCT, Math.min(100 - x, w)) + '%',
        height: Math.max(MIN_ZONA_PCT, Math.min(100 - y, h)) + '%'
    });
}

function leerZonaDesdeElemento($el) {
    return {
        id: $el.data('id'),
        area_id: $el.data('area-id') || null,
        nombre: $el.data('nombre') || $el.find('.plano-zona-label').text(),
        x: parsePct($el[0].style.left),
        y: parsePct($el[0].style.top),
        w: parsePct($el[0].style.width),
        h: parsePct($el[0].style.height),
        color: $el.data('color') || '#eeeeee'
    };
}

function getAreasCatalogo() {
    return planoDatos.areas_catalogo || [];
}

function renderListaAreasConfig() {
    var areas = getAreasCatalogo();
    if (!areas.length) {
        $('#lista-areas-config').html('<p class="text-muted small mb-0">Sin áreas. Pulse + para crear.</p>');
        return;
    }
    var html = '<ul class="list-group list-group-flush">';
    areas.forEach(function (a) {
        var enPlano = a.plano_x !== null && a.plano_x !== '' && a.plano_y !== null && a.plano_y !== '';
        html += '<li class="list-group-item px-1 py-2">'
            + '<div class="d-flex align-items-center">'
            + '<span class="area-color-badge mr-2" style="background:' + escAttr(a.color || '#eee') + '"></span>'
            + '<div class="flex-grow-1 min-w-0">'
            + '<strong class="d-block text-truncate small">' + escHtml(a.nombre) + '</strong>'
            + '<span class="text-muted" style="font-size:10px">' + escHtml(a.codigo) + (enPlano ? ' · en plano' : ' · sin ubicar') + '</span>'
            + '</div>'
            + '<div class="btn-group btn-group-sm flex-shrink-0">'
            + (enPlano ? '' : '<button type="button" class="btn btn-outline-primary btn-sm" onclick="colocarAreaEnPlano(' + a.id + ')" title="Ubicar"><i class="fas fa-map-pin"></i></button>')
            + '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="editarAreaConfig(' + a.id + ')" title="Editar"><i class="fas fa-pen"></i></button>'
            + '<button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarAreaConfig(' + a.id + ')" title="Eliminar"><i class="fas fa-trash"></i></button>'
            + '</div></div></li>';
    });
    html += '</ul>';
    $('#lista-areas-config').html(html);
}

function abrirFormNuevaArea() {
    $('#area_config_id').val('');
    $('#area_config_nombre').val('');
    $('#area_config_codigo').val('');
    $('#area_config_color').val('#e9ecef');
    $('#area_config_colocar').prop('checked', true);
    $('#form-area-config').removeClass('d-none');
}

function editarAreaConfig(id) {
    var a = getAreasCatalogo().find(function (x) { return String(x.id) === String(id); });
    if (!a) return;
    $('#area_config_id').val(a.id);
    $('#area_config_nombre').val(a.nombre);
    $('#area_config_codigo').val(a.codigo);
    $('#area_config_color').val(a.color || '#e9ecef');
    $('#area_config_colocar').prop('checked', false);
    $('#form-area-config').removeClass('d-none');
}

function cerrarFormAreaConfig() {
    $('#form-area-config').addClass('d-none');
}

function guardarAreaConfig() {
    var nombre = $('#area_config_nombre').val().trim();
    if (!nombre) {
        showError('Indique el nombre del área.');
        return;
    }
    $('#loader').fadeIn();
    $.ajax({
        url: base_path + '/mobiliario/mesas/guardar-area',
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            idSucursal: $('#select_sucursal_plano').val(),
            id: $('#area_config_id').val() || 0,
            nombre: nombre,
            codigo: $('#area_config_codigo').val().trim(),
            color: $('#area_config_color').val(),
            colocar_en_plano: $('#area_config_colocar').is(':checked') ? 1 : 0
        }
    }).done(function (r) {
        if (!r.estado) {
            showError(r.mensaje || 'Error');
            return;
        }
        showSuccess('Área guardada.');
        cerrarFormAreaConfig();
        cargarPlano();
    }).fail(function () {
        showError('Error al guardar el área');
    }).always(function () {
        $('#loader').fadeOut();
    });
}

function eliminarAreaConfig(id) {
    swal({
        title: '¿Eliminar esta área?',
        text: 'Se quitará del plano. Las mesas conservan su etiqueta de zona.',
        icon: 'warning',
        buttons: true
    }).then(function (ok) {
        if (!ok) return;
        $('#loader').fadeIn();
        $.ajax({
            url: base_path + '/mobiliario/mesas/eliminar-area',
            type: 'post',
            dataType: 'json',
            data: { _token: CSRF_TOKEN, id: id, idSucursal: $('#select_sucursal_plano').val() }
        }).done(function (r) {
            if (r.estado) {
                showSuccess('Área eliminada.');
                cargarPlano();
            } else showError(r.mensaje);
        }).fail(function () {
            showError('Error al eliminar');
        }).always(function () {
            $('#loader').fadeOut();
        });
    });
}

function colocarAreaEnPlano(id) {
    var a = getAreasCatalogo().find(function (x) { return String(x.id) === String(id); });
    if (!a) return;
    var existentes = (planoDatos.zonas || []).length;
    var z = {
        id: a.codigo,
        area_id: a.id,
        nombre: a.nombre,
        x: 10 + (existentes % 4) * 18,
        y: 10 + Math.floor(existentes / 4) * 15,
        w: parseFloat(a.plano_ancho) || 18,
        h: parseFloat(a.plano_alto) || 14,
        color: a.color || '#e9ecef'
    };
    planoDatos.zonas = planoDatos.zonas || [];
    var idx = planoDatos.zonas.findIndex(function (x) { return x.id === z.id; });
    if (idx >= 0) {
        planoDatos.zonas[idx] = z;
    } else {
        planoDatos.zonas.push(z);
    }
    renderizarZonas(planoDatos.zonas);
    seleccionarZona(z.id);
    showSuccess('Área colocada. Pulse Guardar para conservar.');
}

function optionsZonaMesaSelect(zonaActual) {
    var html = '<option value="">— Sin zona —</option>';
    getAreasCatalogo().forEach(function (a) {
        var sel = (zonaActual && (zonaActual === a.codigo || zonaActual === a.nombre)) ? ' selected' : '';
        html += '<option value="' + escAttr(a.codigo) + '"' + sel + '>' + escHtml(a.nombre) + '</option>';
    });
    if (zonaActual && !getAreasCatalogo().some(function (a) { return a.codigo === zonaActual || a.nombre === zonaActual; })) {
        html += '<option value="' + escAttr(zonaActual) + '" selected>' + escHtml(zonaActual) + ' (legacy)</option>';
    }
    return html;
}

function leerZonasDesdeDom() {
    var zonas = [];
    $('#plano-zonas .plano-zona').each(function () {
        zonas.push(leerZonaDesdeElemento($(this)));
    });
    return zonas;
}

function sincronizarZonasEnMemoria() {
    planoDatos.zonas = leerZonasDesdeDom();
}

function cargarPlano() {
    $('#loader').fadeIn();
    $.ajax({
        url: base_path + '/mobiliario/mesas/cargar-plano',
        type: 'get',
        dataType: 'json',
        data: { idSucursal: $('#select_sucursal_plano').val() }
    }).done(function (response) {
        $('#loader').fadeOut();
        if (!response.estado) {
            showError(response.mensaje || 'Error al cargar');
            return;
        }
        planoDatos = response.datos;
        var ar = planoDatos.ancho_referencia || 100;
        var al = planoDatos.alto_referencia || 150;
        $('#plano-ref-dimensiones').text(ar + ' × ' + al + ' (referencia)');
        $('#plano-canvas').css('aspect-ratio', ar + ' / ' + al);
        planoDatos.areas_catalogo = planoDatos.areas_catalogo || [];
        renderizarZonas(planoDatos.zonas || []);
        renderizarMesas(planoDatos.mesas || []);
        renderizarSinPosicion(planoDatos.mesas || []);
        renderListaAreasConfig();
        aplicarModoPlano();
    }).fail(function () {
        $('#loader').fadeOut();
        showError('No se pudo cargar el plano. ¿Ejecutó el script SQL 15?');
    });
}

function renderizarZonas(zonas) {
    var html = '';
    zonas.forEach(function (z) {
        html += '<div class="plano-zona" data-id="' + (z.id || '') + '" data-area-id="' + (z.area_id || '') + '" data-nombre="' + escAttr(z.nombre || z.id) + '"'
            + ' data-color="' + escAttr(z.color || '#eee') + '"'
            + ' style="left:' + z.x + '%;top:' + z.y + '%;width:' + z.w + '%;height:' + z.h + '%;'
            + 'background:' + (z.color || '#eee') + ';">'
            + '<span class="plano-zona-label">' + escHtml(z.nombre || z.id) + '</span>'
            + '<span class="plano-zona-handle plano-zona-handle-nw" data-handle="nw"></span>'
            + '<span class="plano-zona-handle plano-zona-handle-ne" data-handle="ne"></span>'
            + '<span class="plano-zona-handle plano-zona-handle-sw" data-handle="sw"></span>'
            + '<span class="plano-zona-handle plano-zona-handle-se" data-handle="se"></span>'
            + '</div>';
    });
    $('#plano-zonas').html(html);
    if (getModoPlano() === 'zonas') {
        enlazarEventosZonas();
    }
}

function escAttr(s) {
    return String(s).replace(/"/g, '&quot;');
}

function escHtml(s) {
    return String(s).replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function tienePosicion(m) {
    return m.plano_x !== null && m.plano_x !== '' && m.plano_y !== null && m.plano_y !== '';
}

function renderizarMesas(mesas) {
    var html = '';
    var idxSinPos = 0;
    mesas.forEach(function (m) {
        var x, y, w, h;
        if (tienePosicion(m)) {
            x = parseFloat(m.plano_x);
            y = parseFloat(m.plano_y);
            w = parseFloat(m.plano_ancho) || 7;
            h = parseFloat(m.plano_alto) || 7;
        } else {
            w = parseFloat(m.plano_ancho) || 6;
            h = parseFloat(m.plano_alto) || 6;
            x = 44 + (idxSinPos % 4) * 12;
            y = 8 + Math.floor(idxSinPos / 4) * 10;
            idxSinPos++;
        }
        var forma = getFormaMesa(m);
        var estado = m.estado_codigo === 'MESA_OCUPADA' ? 'ocupada' : 'disponible';
        var sinPosClass = tienePosicion(m) ? '' : ' sin-posicion-en-plano';
        var clases = construirClasesMesaPlano(m, [estado, sinPosClass]);
        html += '<div class="' + clases.join(' ') + '"'
            + ' data-id="' + m.id + '" data-forma="' + forma + '"'
            + ' style="' + estiloPosicionMesa(forma, x, y, w, h) + '"'
            + ' title="Mesa ' + m.numero_mesa + ' (' + etiquetaFormaMesa(forma) + ') — ' + (m.estado_nombre || '') + '">'
            + htmlContenidoMesaPlano(m.numero_mesa, m.capacidad)
            + (typeof htmlAsaRedimensionMesa === 'function' ? htmlAsaRedimensionMesa() : '')
            + '</div>';
    });
    $('#plano-mesas').html(html);
    if (getModoPlano() === 'mesas') {
        enlazarEventosMesas();
    }
}

function renderizarSinPosicion(mesas) {
    var sin = mesas.filter(function (m) { return !tienePosicion(m); });
    if (sin.length === 0) {
        $('#lista-mesas-sin-posicion').html('<p class="text-success small mb-0">Todas las mesas tienen posición.</p>');
        return;
    }
    var html = '';
    sin.forEach(function (m) {
        html += '<div class="mesa-sin-pos-item" data-id="' + m.id + '" onclick="ubicarMesaEnCentro(' + m.id + ')">'
            + 'Mesa <strong>' + m.numero_mesa + '</strong> (' + (m.capacidad || 0) + ' p.)'
            + '</div>';
    });
    $('#lista-mesas-sin-posicion').html(html);
}

function ubicarMesaEnCentro(id) {
    var el = $('.plano-mesa[data-id="' + id + '"]');
    if (!el.length) return;
    el.css({ left: '45%', top: '45%' });
    guardarPosicionElemento(el);
    showSuccess('Mesa ubicada en el centro del salón. Ajuste arrastrando.');
    cargarPlano();
}

function distribuirMesasSinPosicion() {
    var mesas = planoDatos.mesas || [];
    var col = 0;
    var promises = [];
    mesas.forEach(function (m) {
        if (tienePosicion(m)) return;
        var x = 44 + (col % 5) * 10;
        var y = 50 + Math.floor(col / 5) * 9;
        col++;
        promises.push(guardarPosicionAjax(m.id, x, y, m.plano_ancho || 7, m.plano_alto || 7, m.forma || 'rectangular', m.zona));
    });
    if (promises.length === 0) {
        showError('No hay mesas sin posición.');
        return;
    }
    $('#loader').fadeIn();
    $.when.apply($, promises).always(function () {
        $('#loader').fadeOut();
        showSuccess('Mesas distribuidas en el área del salón.');
        cargarPlano();
    });
}

function enlazarEventosZonas() {
    var canvas = document.getElementById('plano-canvas');
    $('.plano-zona').off('mousedown click');
    $('.plano-zona-handle').off('mousedown');

    $('.plano-zona').on('click', function (e) {
        if ($(e.target).hasClass('plano-zona-handle')) return;
        if (arrastre && arrastre.movio) return;
        seleccionarZona($(this).data('id'));
    });

    $('.plano-zona').on('mousedown', function (e) {
        if ($(e.target).hasClass('plano-zona-handle')) return;
        if (getModoPlano() !== 'zonas') return;
        iniciarArrastreZona.call(this, e, 'mover');
    });

    $('.plano-zona-handle').on('mousedown', function (e) {
        e.stopPropagation();
        if (getModoPlano() !== 'zonas') return;
        var zonaEl = $(this).closest('.plano-zona')[0];
        iniciarArrastreZona.call(zonaEl, e, 'resize', $(this).data('handle'));
    });

    function iniciarArrastreZona(evt, tipo, handle) {
        evt.preventDefault();
        var el = $(this);
        var canvasRect = canvas.getBoundingClientRect();
        var z = leerZonaDesdeElemento(el);
        seleccionarZona(el.data('id'));
        arrastre = {
            tipo: tipo,
            handle: handle,
            el: el,
            canvas: canvas,
            inicio: z,
            movio: false,
            startX: evt.clientX,
            startY: evt.clientY
        };
        el.addClass('arrastrando-zona');
        $(document).on('mousemove.planoZona', moverArrastreZona);
        $(document).on('mouseup.planoZona', finalizarArrastreZona);
    }

    function moverArrastreZona(evt) {
        if (!arrastre || arrastre.tipo === undefined) return;
        arrastre.movio = true;
        var rect = arrastre.canvas.getBoundingClientRect();
        var dx = ((evt.clientX - arrastre.startX) / rect.width) * 100;
        var dy = ((evt.clientY - arrastre.startY) / rect.height) * 100;
        var z = arrastre.inicio;
        var x = z.x, y = z.y, w = z.w, h = z.h;

        if (arrastre.tipo === 'mover') {
            x = z.x + dx;
            y = z.y + dy;
            x = Math.max(0, Math.min(100 - w, x));
            y = Math.max(0, Math.min(100 - h, y));
        } else {
            var handle = arrastre.handle;
            if (handle === 'se') {
                w = Math.max(MIN_ZONA_PCT, z.w + dx);
                h = Math.max(MIN_ZONA_PCT, z.h + dy);
            } else if (handle === 'sw') {
                w = Math.max(MIN_ZONA_PCT, z.w - dx);
                h = Math.max(MIN_ZONA_PCT, z.h + dy);
                x = z.x + (z.w - w);
            } else if (handle === 'ne') {
                w = Math.max(MIN_ZONA_PCT, z.w + dx);
                h = Math.max(MIN_ZONA_PCT, z.h - dy);
                y = z.y + (z.h - h);
            } else if (handle === 'nw') {
                w = Math.max(MIN_ZONA_PCT, z.w - dx);
                h = Math.max(MIN_ZONA_PCT, z.h - dy);
                x = z.x + (z.w - w);
                y = z.y + (z.h - h);
            }
            x = Math.max(0, x);
            y = Math.max(0, y);
            if (x + w > 100) w = 100 - x;
            if (y + h > 100) h = 100 - y;
        }
        aplicarEstiloZona(arrastre.el, x, y, w, h);
    }

    function finalizarArrastreZona() {
        if (!arrastre) return;
        arrastre.el.removeClass('arrastrando-zona');
        if (arrastre.movio) {
            sincronizarZonasEnMemoria();
            if (zonaSeleccionadaId) {
                seleccionarZona(zonaSeleccionadaId);
            }
        }
        $(document).off('mousemove.planoZona mouseup.planoZona');
        arrastre = null;
    }
}

function seleccionarZona(id) {
    zonaSeleccionadaId = id;
    mesaSeleccionadaId = null;
    $('.plano-zona').removeClass('seleccionada');
    var $el = $('.plano-zona[data-id="' + id + '"]');
    $el.addClass('seleccionada');
    var z = leerZonaDesdeElemento($el);
    var html = '<p><strong>' + escHtml(z.nombre) + '</strong></p>'
        + '<div class="form-group mb-2"><label class="small mb-0">Nombre</label>'
        + '<input type="text" id="zona_nombre" class="form-control form-control-sm" value="' + escAttr(z.nombre) + '"></div>'
        + '<div class="form-group mb-2"><label class="small mb-0">Color</label>'
        + '<input type="color" id="zona_color" class="form-control form-control-sm" value="' + (z.color || '#eeeeee') + '"></div>'
        + '<div class="row">'
        + '<div class="col-6"><label class="small">X %</label><input type="number" id="zona_x" class="form-control form-control-sm" min="0" max="100" step="0.5" value="' + z.x.toFixed(1) + '"></div>'
        + '<div class="col-6"><label class="small">Y %</label><input type="number" id="zona_y" class="form-control form-control-sm" min="0" max="100" step="0.5" value="' + z.y.toFixed(1) + '"></div>'
        + '<div class="col-6 mt-1"><label class="small">Ancho %</label><input type="number" id="zona_w" class="form-control form-control-sm" min="' + MIN_ZONA_PCT + '" max="100" step="0.5" value="' + z.w.toFixed(1) + '"></div>'
        + '<div class="col-6 mt-1"><label class="small">Alto %</label><input type="number" id="zona_h" class="form-control form-control-sm" min="' + MIN_ZONA_PCT + '" max="100" step="0.5" value="' + z.h.toFixed(1) + '"></div>'
        + '</div>'
        + '<button type="button" class="btn btn-sm btn-primary btn-block mt-2" onclick="aplicarDetalleZona(\'' + id + '\')">Aplicar al plano</button>';
    $('#panel-zona-detalle').html(html);
}

function aplicarDetalleZona(id) {
    var $el = $('.plano-zona[data-id="' + id + '"]');
    if (!$el.length) return;
    var nombre = $('#zona_nombre').val();
    var color = $('#zona_color').val();
    var x = parseFloat($('#zona_x').val()) || 0;
    var y = parseFloat($('#zona_y').val()) || 0;
    var w = parseFloat($('#zona_w').val()) || MIN_ZONA_PCT;
    var h = parseFloat($('#zona_h').val()) || MIN_ZONA_PCT;
    $el.data('nombre', nombre).data('color', color);
    $el.find('.plano-zona-label').text(nombre);
    aplicarEstiloZona($el, x, y, w, h);
    $el.css('background', color);
    sincronizarZonasEnMemoria();
    showSuccess('Área actualizada en el plano. Pulse Guardar para conservar en la sucursal.');
}

function restaurarZonasDefault() {
    swal({
        title: '¿Restaurar áreas al diseño inicial?',
        text: 'Se reemplazarán todas las áreas configuradas (cocina, baño, entrada, jardín).',
        icon: 'warning',
        buttons: true
    }).then(function (ok) {
        if (!ok) return;
        $('#loader').fadeIn();
        $.ajax({
            url: base_path + '/mobiliario/mesas/restaurar-areas-default',
            type: 'post',
            dataType: 'json',
            data: { _token: CSRF_TOKEN, idSucursal: $('#select_sucursal_plano').val() }
        }).done(function (r) {
            if (!r.estado) {
                showError(r.mensaje || 'Error');
                return;
            }
            planoDatos = r.datos;
            renderizarZonas(planoDatos.zonas || []);
            renderListaAreasConfig();
            if ((planoDatos.zonas || []).length) {
                seleccionarZona(planoDatos.zonas[0].id);
            }
            showSuccess('Áreas restauradas.');
        }).fail(function () {
            showError('Error al restaurar');
        }).always(function () {
            $('#loader').fadeOut();
        });
    });
}

function guardarZonasPlano() {
    sincronizarZonasEnMemoria();
    $('#loader').fadeIn();
    $.ajax({
        url: base_path + '/mobiliario/mesas/guardar-plano',
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            idSucursal: $('#select_sucursal_plano').val(),
            zonas: planoDatos.zonas,
            ancho_referencia: planoDatos.ancho_referencia || 100,
            alto_referencia: planoDatos.alto_referencia || 150
        }
    }).done(function (r) {
        if (r.estado) {
            showSuccess('Áreas del local guardadas correctamente.');
        } else {
            showError(r.mensaje || 'Error al guardar');
        }
    }).fail(function () {
        showError('Error al guardar las áreas');
    }).always(function () {
        $('#loader').fadeOut();
    });
}

function guardarCambiosPlano() {
    if (getModoPlano() === 'zonas') {
        guardarZonasPlano();
    } else {
        guardarTodasPosiciones();
    }
}

function enlazarEventosMesas() {
    var canvas = document.getElementById('plano-canvas');
    $('.plano-mesa').off('mousedown click');
    $('.mesa-plano-handle').off('mousedown');

    $('.plano-mesa').on('click', function (e) {
        if ($(e.target).hasClass('mesa-plano-handle')) return;
        if (arrastre && arrastre.movio) return;
        seleccionarMesa($(this).data('id'));
    });

    if (getModoPlano() !== 'mesas') return;

    $('.mesa-plano-handle').on('mousedown', function (e) {
        e.stopPropagation();
        if (getModoPlano() !== 'mesas') return;
        var mesaEl = $(this).closest('.plano-mesa')[0];
        iniciarRedimensionMesa.call(mesaEl, e);
    });

    $('.plano-mesa').on('mousedown', function (evt) {
        if ($(evt.target).hasClass('mesa-plano-handle')) return;
        var el = $(this);
        var canvasRect = canvas.getBoundingClientRect();
        var elRect = this.getBoundingClientRect();
        arrastre = {
            tipo: 'move',
            el: el,
            canvas: canvas,
            offsetX: evt.clientX - elRect.left,
            offsetY: evt.clientY - elRect.top,
            movio: false
        };
        el.addClass('arrastrando');
        $(document).on('mousemove.plano', moverArrastreMesa);
        $(document).on('mouseup.plano', finalizarArrastreMesa);
        evt.preventDefault();
    });

    function moverArrastreMesa(evt) {
        if (!arrastre || !arrastre.el) return;
        arrastre.movio = true;
        var rect = arrastre.canvas.getBoundingClientRect();
        var elW = arrastre.el[0].offsetWidth;
        var elH = arrastre.el[0].offsetHeight;
        var left = evt.clientX - rect.left - arrastre.offsetX;
        var top = evt.clientY - rect.top - arrastre.offsetY;
        left = Math.max(0, Math.min(left, rect.width - elW));
        top = Math.max(0, Math.min(top, rect.height - elH));
        arrastre.el.css({
            left: (left / rect.width) * 100 + '%',
            top: (top / rect.height) * 100 + '%'
        });
    }

    function finalizarArrastreMesa() {
        if (!arrastre || !arrastre.el) return;
        arrastre.el.removeClass('arrastrando');
        if (arrastre.movio && arrastre.tipo !== 'resize') {
            guardarPosicionElemento(arrastre.el);
        } else if (arrastre.movio && arrastre.tipo === 'resize') {
            sincronizarPanelTamanoDesdeMesa(arrastre.el.data('id'));
            guardarPosicionElemento(arrastre.el);
        }
        $(document).off('mousemove.plano mouseup.plano');
        arrastre = null;
    }

    function iniciarRedimensionMesa(evt) {
        evt.preventDefault();
        var el = $(this);
        var canvasRect = canvas.getBoundingClientRect();
        var pct = leerPorcentajesMesaEnCanvas(el, canvas);
        seleccionarMesa(el.data('id'));
        arrastre = {
            tipo: 'resize',
            el: el,
            canvas: canvas,
            inicio: pct,
            movio: false,
            startX: evt.clientX,
            startY: evt.clientY
        };
        $(document).on('mousemove.planoMesaResize', moverRedimensionMesa);
        $(document).on('mouseup.planoMesaResize', finalizarRedimensionMesa);
    }

    function moverRedimensionMesa(evt) {
        if (!arrastre || arrastre.tipo !== 'resize') return;
        arrastre.movio = true;
        var rect = arrastre.canvas.getBoundingClientRect();
        var dx = ((evt.clientX - arrastre.startX) / rect.width) * 100;
        var dy = ((evt.clientY - arrastre.startY) / rect.height) * 100;
        var forma = getFormaMesa({ forma: arrastre.el.data('forma') });
        var w = Math.max(MIN_MESA_PCT, Math.min(MAX_MESA_PCT, arrastre.inicio.w + dx));
        var h = Math.max(MIN_MESA_PCT, Math.min(MAX_MESA_PCT, arrastre.inicio.h + dy));
        if (esFormaCuadradaVisual(forma)) {
            var s = Math.max(w, h);
            w = s;
            h = s;
        }
        aplicarTamanoMesaEnDom(arrastre.el, forma, w, h);
        sincronizarPanelTamanoDesdeMesa(arrastre.el.data('id'));
    }

    function finalizarRedimensionMesa() {
        if (!arrastre || arrastre.tipo !== 'resize') return;
        if (arrastre.movio) {
            guardarPosicionElemento(arrastre.el);
        }
        $(document).off('mousemove.planoMesaResize mouseup.planoMesaResize');
        arrastre = null;
    }
}

function sincronizarPanelTamanoDesdeMesa(id) {
    var $el = $('.plano-mesa[data-id="' + id + '"]');
    if (!$el.length) return;
    var canvas = document.getElementById('plano-canvas');
    var pct = leerPorcentajesMesaEnCanvas($el, canvas);
    var forma = getFormaMesa({ forma: $el.data('forma') });
    if (esFormaCuadradaVisual(forma)) {
        if ($('#mesa_tamano_pct').length) {
            $('#mesa_tamano_pct').val(Math.round(pct.w * 10) / 10);
            $('#mesa_tamano_val').text(Math.round(pct.w * 10) / 10 + '%');
        }
    } else {
        if ($('#mesa_ancho_pct').length) $('#mesa_ancho_pct').val(pct.w.toFixed(1));
        if ($('#mesa_alto_pct').length) $('#mesa_alto_pct').val(pct.h.toFixed(1));
    }
}

function guardarPosicionElemento($el) {
    var id = $el.data('id');
    var canvas = document.getElementById('plano-canvas');
    var m = (planoDatos.mesas || []).find(function (x) { return x.id == id; });
    var forma = m ? getFormaMesa(m) : 'rectangular';
    var pct = leerPorcentajesMesaEnCanvas($el, canvas);
    if (esFormaCuadradaVisual(forma)) {
        pct.h = pct.w;
    }
    return guardarPosicionAjax(
        id,
        pct.x,
        pct.y,
        pct.w,
        pct.h,
        forma,
        m ? m.zona : null
    );
}

function guardarPosicionAjax(id, x, y, w, h, forma, zona) {
    return $.ajax({
        url: base_path + '/mobiliario/mesas/guardar-posicion',
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            id: id,
            plano_x: x.toFixed(2),
            plano_y: y.toFixed(2),
            plano_ancho: w.toFixed(2),
            plano_alto: h.toFixed(2),
            forma: forma || 'rectangular',
            zona: zona
        }
    });
}

function guardarTodasPosiciones() {
    var defs = [];
    $('.plano-mesa').each(function () {
        defs.push(guardarPosicionElemento($(this)));
    });
    if (defs.length === 0) {
        showError('No hay mesas en el plano.');
        return;
    }
    $('#loader').fadeIn();
    $.when.apply($, defs).done(function () {
        showSuccess('Posiciones de mesas guardadas.');
        cargarPlano();
    }).fail(function () {
        showError('Error al guardar algunas posiciones.');
    }).always(function () {
        $('#loader').fadeOut();
    });
}

function seleccionarMesa(id) {
    mesaSeleccionadaId = id;
    zonaSeleccionadaId = null;
    $('.plano-zona').removeClass('seleccionada');
    var m = (planoDatos.mesas || []).find(function (x) { return x.id == id; });
    if (!m) return;
    $('.plano-mesa').removeClass('seleccionada');
    $('.plano-mesa[data-id="' + id + '"]').addClass('seleccionada');

    var estado = m.estado_codigo === 'MESA_OCUPADA' ? 'Ocupada' : 'Disponible';
    var formaActual = getFormaMesa(m);
    var html = '<p><strong>Mesa ' + m.numero_mesa + '</strong>'
        + ' <span class="mesa-forma-badge forma-' + formaActual + '">' + etiquetaFormaMesa(formaActual) + '</span></p>'
        + '<p class="mb-1">Capacidad: ' + (m.capacidad || 0) + '</p>'
        + '<p class="mb-1">Estado: ' + estado + '</p>'
        + htmlSelectorFormaMesa(formaActual, 'detalle_forma')
        + htmlControlesTamanoMesa(m)
        + '<p class="mb-2 mt-2">Zona: <select id="detalle_zona" class="form-control form-control-sm">' + optionsZonaMesaSelect(m.zona || '') + '</select></p>'
        + '<button type="button" class="btn btn-sm btn-primary btn-block" onclick="aplicarDetalleMesa(' + id + ')">Guardar mesa</button>'
        + '<button type="button" class="btn btn-sm btn-outline-' + (m.estado_codigo === 'MESA_DISPONIBLE' ? 'danger' : 'success') + ' btn-block mt-1" onclick="toggleEstadoMesa(' + id + ', \'' + (m.estado_codigo === 'MESA_DISPONIBLE' ? 'MESA_OCUPADA' : 'MESA_DISPONIBLE') + '\')">'
        + (m.estado_codigo === 'MESA_DISPONIBLE' ? 'Marcar ocupada' : 'Marcar disponible') + '</button>';
    $('#panel-mesa-detalle').html(html);
    if (getModoPlano() === 'mesas') {
        enlazarEventosMesas();
    }
}

function leerTamanoDesdePanel(mesaId) {
    var $el = $('.plano-mesa[data-id="' + mesaId + '"]');
    var forma = $('#detalle_forma').val() || getFormaMesa({ forma: $el.data('forma') });
    var w, h;
    if (esFormaCuadradaVisual(forma)) {
        w = parseFloat($('#mesa_tamano_pct').val()) || 7;
        h = w;
    } else {
        w = parseFloat($('#mesa_ancho_pct').val()) || 7;
        h = parseFloat($('#mesa_alto_pct').val()) || 7;
    }
    w = Math.max(MIN_MESA_PCT, Math.min(MAX_MESA_PCT, w));
    h = Math.max(MIN_MESA_PCT, Math.min(MAX_MESA_PCT, h));
    return { forma: forma, w: w, h: h };
}

function vistaPreviaTamanoMesa(id) {
    var $el = $('.plano-mesa[data-id="' + id + '"]');
    if (!$el.length) return;
    var t = leerTamanoDesdePanel(id);
    if ($('#mesa_tamano_val').length) {
        $('#mesa_tamano_val').text(t.w + '%');
    }
    aplicarTamanoMesaEnDom($el, t.forma, t.w, t.h);
}

function aplicarDetalleMesa(id) {
    var $el = $('.plano-mesa[data-id="' + id + '"]');
    var canvas = document.getElementById('plano-canvas');
    var tam = leerTamanoDesdePanel(id);
    var zona = $('#detalle_zona').val();
    var pct = leerPorcentajesMesaEnCanvas($el, canvas);
    $el.removeClass('forma-rectangular forma-cuadrada forma-redonda').addClass('forma-' + tam.forma).attr('data-forma', tam.forma);
    aplicarTamanoMesaEnDom($el, tam.forma, tam.w, tam.h);
    pct.w = tam.w;
    pct.h = tam.h;
    guardarPosicionAjax(
        id,
        pct.x,
        pct.y,
        pct.w,
        pct.h,
        tam.forma,
        zona
    ).done(function (r) {
        if (r.estado) {
            showSuccess('Actualizado.');
            cargarPlano();
        } else showError(r.mensaje);
    });
}

function toggleEstadoMesa(id, nuevoEstado) {
    $.ajax({
        url: base_path + '/mobiliario/mesas/cambiar-estado',
        type: 'post',
        dataType: 'json',
        data: { _token: CSRF_TOKEN, id_mesa: id, estado: nuevoEstado }
    }).done(function (r) {
        if (r.estado) {
            showSuccess(r.mensaje || 'Estado actualizado');
            cargarPlano();
        } else showError(r.mensaje);
    });
}
