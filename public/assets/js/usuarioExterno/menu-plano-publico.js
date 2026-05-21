/**
 * Mapa plano del menú público (zonas + mesas, estilo flat).
 */
var MenuPlanoPublico = (function () {
    var datos = null;
    var cargadoParaSucursal = null;

    function tienePosicion(m) {
        return m.plano_x !== null && m.plano_x !== '' && m.plano_y !== null && m.plano_y !== '';
    }

    function renderizarZonas(zonas) {
        var cont = document.getElementById('menu-plano-zonas');
        if (!cont) return;
        var html = '';
        (zonas || []).forEach(function (z) {
            var color = z.color || '#e8ebe6';
            html += '<div class="menu-plano-zona" style="'
                + 'left:' + z.x + '%;top:' + z.y + '%;width:' + z.w + '%;height:' + z.h + '%;'
                + 'background:' + color + ';">'
                + '<span class="menu-plano-zona-label">' + escMenu(z.nombre || z.id || '') + '</span>'
                + '</div>';
        });
        cont.innerHTML = html;
    }

    function renderizarMesas(mesas) {
        var cont = document.getElementById('menu-plano-mesas');
        if (!cont) return;
        var idxSin = 0;
        var html = '';

        (mesas || []).forEach(function (m) {
            var x, y, w, h;
            if (tienePosicion(m)) {
                x = parseFloat(m.plano_x);
                y = parseFloat(m.plano_y);
                w = parseFloat(m.plano_ancho) || 7;
                h = parseFloat(m.plano_alto) || 7;
            } else {
                w = 6;
                h = 6;
                x = 4 + (idxSin % 6) * 9;
                y = 78 + Math.floor(idxSin / 6) * 8;
                idxSin++;
            }

            var forma = typeof getFormaMesa === 'function' ? getFormaMesa(m) : 'rectangular';
            var disp = !!m.disponible;
            var clases = ['menu-plano-mesa', 'plano-mesa', 'forma-' + forma];
            clases.push(disp ? 'disponible' : 'ocupada');
            if (!tienePosicion(m)) clases.push('sin-ubicar');

            var estilo = typeof estiloPosicionMesaPos === 'function'
                ? estiloPosicionMesaPos(forma, x, y, w, h)
                : (typeof estiloPosicionMesa === 'function' ? estiloPosicionMesa(forma, x, y, w, h) : '');

            var hint = disp ? (m.capacidad || 0) + ' p.' : 'Ocupada';
            var contenido = typeof htmlContenidoMesaPlano === 'function'
                ? htmlContenidoMesaPlano(m.numero_mesa, m.capacidad, '', forma, w, h)
                : '<span class="plano-mesa-numero">' + escMenu(m.numero_mesa) + '</span>';

            html += '<div class="' + clases.join(' ') + '" data-mesa-id="' + m.id + '"'
                + ' title="' + escMenu('Mesa ' + (m.numero_mesa || '') + ' — ' + (disp ? 'libre' : 'ocupada')) + '"'
                + ' style="' + estilo + '">' + contenido + '</div>';
        });

        cont.innerHTML = html;
    }

    function escMenu(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function aplicarProporcionCanvas(ar, al) {
        var canvas = document.getElementById('menu-plano-canvas');
        if (!canvas) return;
        ar = Math.max(40, parseInt(ar, 10) || 100);
        al = Math.max(40, parseInt(al, 10) || 150);
        canvas.style.aspectRatio = ar + ' / ' + al;
        var ref = document.getElementById('menu-plano-ref');
        if (ref) ref.textContent = 'Escala ref. ' + ar + ' × ' + al;
    }

    function mostrarEstado(estado) {
        var stage = document.getElementById('menu-plano-stage');
        var loading = document.getElementById('menu-plano-loading');
        var empty = document.getElementById('menu-plano-empty');
        if (loading) loading.hidden = estado !== 'loading';
        if (stage) stage.hidden = estado !== 'ready';
        if (empty) empty.hidden = estado !== 'empty';
    }

    function actualizarBadge(disponibles, total) {
        var badge = document.getElementById('menu-mesas-badge');
        if (!badge) return;
        if (total < 1) {
            badge.hidden = true;
            return;
        }
        badge.hidden = false;
        badge.textContent = disponibles + ' / ' + total + ' libres';
    }

    function render(payload) {
        datos = payload;
        if (!payload || !payload.mesas || !payload.mesas.length) {
            mostrarEstado('empty');
            actualizarBadge(0, 0);
            return;
        }

        aplicarProporcionCanvas(payload.ancho_referencia, payload.alto_referencia);
        renderizarZonas(payload.zonas || []);
        renderizarMesas(payload.mesas || []);
        mostrarEstado('ready');
        actualizarBadge(payload.disponibles || 0, payload.total_mesas || payload.mesas.length);

        var resumenId = 'menu-plano-resumen';
        var content = document.getElementById('mesas-disponibles-content');
        if (content && !document.getElementById(resumenId)) {
            var res = document.createElement('div');
            res.id = resumenId;
            res.className = 'menu-plano-resumen';
            res.innerHTML = '<span class="menu-plano-resumen-chip"><i class="fas fa-chair"></i> '
                + (payload.disponibles || 0) + ' mesas libres</span>'
                + '<span class="menu-plano-resumen-chip">' + (payload.zonas || []).length + ' áreas</span>';
            content.appendChild(res);
        }
    }

    function cargar(idSucursal, callback) {
        if (!idSucursal) {
            if (callback) callback(false);
            return;
        }
        if (cargadoParaSucursal === idSucursal && datos) {
            render(datos);
            if (callback) callback(true);
            return;
        }

        mostrarEstado('loading');
        var content = document.getElementById('mesas-disponibles-content');
        var prevResumen = document.getElementById('menu-plano-resumen');
        if (prevResumen) prevResumen.remove();

        $.ajax({
            url: (typeof base_path !== 'undefined' ? base_path : '') + '/usuarioExterno/menu/mesas-disponibles',
            type: 'POST',
            dataType: 'json',
            data: {
                _token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '',
                id_sucursal: idSucursal
            }
        }).done(function (response) {
            if (!response.estado || !response.datos) {
                mostrarEstado('empty');
                if (callback) callback(false);
                return;
            }
            cargadoParaSucursal = idSucursal;
            render(response.datos);
            if (callback) callback(true);
        }).fail(function () {
            mostrarEstado('empty');
            var empty = document.getElementById('menu-plano-empty');
            if (empty) {
                empty.innerHTML = '<p class="small text-danger mb-0">No se pudo cargar el mapa.</p>';
                empty.hidden = false;
            }
            if (callback) callback(false);
        });
    }

    function reset() {
        datos = null;
        cargadoParaSucursal = null;
        var res = document.getElementById('menu-plano-resumen');
        if (res) res.remove();
        var zonas = document.getElementById('menu-plano-zonas');
        var mesas = document.getElementById('menu-plano-mesas');
        if (zonas) zonas.innerHTML = '';
        if (mesas) mesas.innerHTML = '';
        mostrarEstado('empty');
        var badge = document.getElementById('menu-mesas-badge');
        if (badge) badge.hidden = true;
        var empty = document.getElementById('menu-plano-empty');
        if (empty) {
            empty.hidden = true;
            empty.innerHTML = '<p class="small mb-0">No hay mesas configuradas para esta sucursal.</p>';
        }
    }

    return {
        cargar: cargar,
        render: render,
        reset: reset
    };
})();
