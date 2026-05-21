/**
 * Plano público del menú — vista sala estilo Habbo (2D + skin isométrico).
 */
var MenuPlanoPublico = (function () {
    var COD_LIBRE = 'MESA_DISPONIBLE';

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formaMesa(m) {
        var f = (m && m.forma) ? String(m.forma).toLowerCase() : 'rectangular';
        if (f !== 'redonda' && f !== 'cuadrada' && f !== 'rectangular') return 'rectangular';
        return f;
    }

    function tienePosicion(m) {
        return m.plano_x !== null && m.plano_x !== '' && m.plano_y !== null && m.plano_y !== '';
    }

    function estiloMesa(forma, x, y, w, h) {
        w = parseFloat(w) || 7;
        h = parseFloat(h) || 7;
        if (forma === 'redonda' || forma === 'cuadrada') {
            var tam = Math.min(Math.max(w, h), 11);
            return 'left:' + x + '%;top:' + y + '%;width:' + tam + '%;height:auto;';
        }
        return 'left:' + x + '%;top:' + y + '%;width:' + Math.min(w, 12) + '%;height:' + Math.min(h, 10) + '%;';
    }

    function esLibre(m) {
        return m.estado_codigo === COD_LIBRE;
    }

    function aplicarCanvas(datos) {
        var ar = parseInt(datos.ancho_referencia, 10) || 100;
        var al = parseInt(datos.alto_referencia, 10) || 150;
        var canvas = document.getElementById('menu-habbo-canvas');
        if (!canvas) return;
        canvas.style.aspectRatio = ar + ' / ' + al;
        canvas.style.minHeight = Math.min(320, Math.max(200, al * 2)) + 'px';
    }

    function renderZonas(zonas) {
        var cont = document.getElementById('menu-habbo-zonas');
        if (!cont) return;
        var html = '';
        (zonas || []).forEach(function (z) {
            html += '<div class="habbo-zona" style="'
                + 'left:' + z.x + '%;top:' + z.y + '%;width:' + z.w + '%;height:' + z.h + '%;'
                + 'background:' + (z.color || '#b8e0d2') + ';">'
                + '<span>' + esc(z.nombre || z.id) + '</span></div>';
        });
        cont.innerHTML = html;
    }

    function htmlMesaHabbo(m, x, y, w, h) {
        var forma = formaMesa(m);
        var libre = esLibre(m);
        var cls = 'habbo-mesa forma-' + forma + (libre ? ' habbo-mesa--libre' : ' habbo-mesa--ocupada');
        var cap = m.capacidad || 0;
        var titulo = libre
            ? 'Mesa ' + m.numero_mesa + ' — ¡Libre! (' + cap + ' personas)'
            : 'Mesa ' + m.numero_mesa + ' — ocupada';

        return '<div class="' + cls + '" style="' + estiloMesa(forma, x, y, w, h) + '"'
            + ' title="' + esc(titulo) + '" data-mesa-id="' + m.id + '">'
            + '<div class="habbo-mesa__sombra"></div>'
            + '<div class="habbo-mesa__cubo">'
            + '<div class="habbo-mesa__top"></div>'
            + '<div class="habbo-mesa__front"></div>'
            + '<span class="habbo-mesa__num">' + esc(m.numero_mesa) + '</span>'
            + (libre ? '<span class="habbo-mesa__spark" aria-hidden="true"></span>' : '')
            + '</div>'
            + (libre ? '<span class="habbo-mesa__badge">Libre</span>' : '')
            + '</div>';
    }

    function renderMesas(mesas) {
        var cont = document.getElementById('menu-habbo-mesas');
        var sinPos = document.getElementById('menu-habbo-sin-pos');
        if (!cont) return { libres: 0, total: 0 };

        var html = '';
        var chips = '';
        var idxSin = 0;
        var libres = 0;

        (mesas || []).forEach(function (m) {
            if (esLibre(m)) libres++;
            var x, y, w, h;
            if (tienePosicion(m)) {
                x = parseFloat(m.plano_x);
                y = parseFloat(m.plano_y);
                w = parseFloat(m.plano_ancho) || 7;
                h = parseFloat(m.plano_alto) || 7;
                html += htmlMesaHabbo(m, x, y, w, h);
            } else {
                w = 6;
                h = 6;
                x = 38 + (idxSin % 5) * 11;
                y = 8 + Math.floor(idxSin / 5) * 10;
                idxSin++;
                html += htmlMesaHabbo(m, x, y, w, h);
                if (esLibre(m)) {
                    chips += '<span class="habbo-chip habbo-chip--libre" title="' + esc('Capacidad ' + (m.capacidad || 0)) + '">'
                        + '<i class="fas fa-chair"></i> ' + esc(m.numero_mesa) + '</span>';
                }
            }
        });

        cont.innerHTML = html;
        if (sinPos) {
            if (chips) {
                sinPos.innerHTML = '<p class="habbo-sin-pos__title">Mesas libres (sin posición en mapa)</p><div class="habbo-chips">' + chips + '</div>';
                sinPos.hidden = false;
            } else {
                sinPos.innerHTML = '';
                sinPos.hidden = true;
            }
        }

        return { libres: libres, total: (mesas || []).length };
    }

    function actualizarContador(stats) {
        var el = document.getElementById('menu-habbo-stats');
        if (!el) return;
        if (!stats.total) {
            el.textContent = 'No hay mesas configuradas en esta sucursal.';
            return;
        }
        el.innerHTML = '<strong>' + stats.libres + '</strong> mesa(s) libre(s) de <strong>' + stats.total + '</strong>';
    }

    function skeletonHtml() {
        return '<div class="menu-habbo-plano-wrap">'
            + '<p class="menu-habbo-loading"><i class="fas fa-spinner fa-spin"></i> Cargando sala…</p>'
            + '</div>';
    }

    function hostHtml() {
        return '<div class="menu-habbo-plano-wrap">'
            + '<p class="menu-habbo-stats" id="menu-habbo-stats"></p>'
            + '<div class="habbo-sala">'
            + '<div class="habbo-sala__corners" aria-hidden="true">'
            + '<span class="habbo-pared habbo-pared--tl"></span>'
            + '<span class="habbo-pared habbo-pared--tr"></span>'
            + '</div>'
            + '<div class="habbo-sala__viewport">'
            + '<div class="habbo-sala__canvas" id="menu-habbo-canvas">'
            + '<div class="habbo-piso" aria-hidden="true"></div>'
            + '<div id="menu-habbo-zonas"></div>'
            + '<div id="menu-habbo-mesas"></div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="habbo-leyenda">'
            + '<span><i class="habbo-dot habbo-dot--libre"></i> Libre</span>'
            + '<span><i class="habbo-dot habbo-dot--ocupada"></i> Ocupada</span>'
            + '</div>'
            + '<div id="menu-habbo-sin-pos" class="habbo-sin-pos" hidden></div>'
            + '</div>';
    }

    function render(datos, contentDiv) {
        contentDiv.innerHTML = hostHtml();
        aplicarCanvas(datos);
        renderZonas(datos.zonas || []);
        var stats = renderMesas(datos.mesas || []);
        actualizarContador(stats);

        contentDiv.querySelectorAll('.habbo-mesa--libre').forEach(function (el) {
            el.addEventListener('click', function () {
                el.classList.add('habbo-mesa--pop');
                setTimeout(function () { el.classList.remove('habbo-mesa--pop'); }, 400);
            });
        });
    }

    function cargar(idSucursal, contentDiv) {
        if (!contentDiv || !idSucursal) return;
        contentDiv.dataset.loaded = '1';
        contentDiv.innerHTML = skeletonHtml();

        $.ajax({
            url: base_path + '/usuarioExterno/menu/plano-sucursal',
            type: 'POST',
            dataType: 'json',
            data: { _token: CSRF_TOKEN, id_sucursal: idSucursal }
        }).done(function (response) {
            if (!response.estado || !response.datos) {
                contentDiv.innerHTML = '<p class="small text-muted mb-0">'
                    + esc(response.mensaje || 'No se pudo cargar el mapa.') + '</p>';
                return;
            }
            render(response.datos, contentDiv);
        }).fail(function () {
            contentDiv.innerHTML = '<p class="small text-danger mb-0">No se pudo cargar el mapa de mesas.</p>';
        });
    }

    return { cargar: cargar };
})();
