/**
 * Plano público — sala interactiva estilo Habbo Hotel (isométrico CSS).
 */
var MenuPlanoPublico = (function () {
    var COD_LIBRE = 'MESA_DISPONIBLE';
    var planoCache = null;
    var filtroActivo = 'todas';
    var zoomNivel = 1;
    var mesaSeleccionadaId = null;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function etiquetaMesa(num) {
        var s = String(num == null ? '' : num).trim();
        if (s.length > 10) return s.substring(0, 9) + '\u2026';
        return s;
    }

    function formaMesa(m) {
        var f = (m && m.forma) ? String(m.forma).toLowerCase() : 'rectangular';
        if (f !== 'redonda' && f !== 'cuadrada' && f !== 'rectangular') return 'redonda';
        return f;
    }

    function tienePosicion(m) {
        return m.plano_x !== null && m.plano_x !== '' && m.plano_y !== null && m.plano_y !== '';
    }

    function esLibre(m) {
        return m.estado_codigo === COD_LIBRE;
    }

    function pasaFiltro(m) {
        if (filtroActivo === 'libres') return esLibre(m);
        return true;
    }

    function estiloMesa(forma, x, y, w, h) {
        w = parseFloat(w) || 7;
        h = parseFloat(h) || 7;
        var z = Math.round((parseFloat(y) + (parseFloat(h) || 7)) * 8);
        if (forma === 'redonda' || forma === 'cuadrada') {
            var tam = Math.min(Math.max(w, h), 12);
            return 'left:' + x + '%;top:' + y + '%;width:' + tam + '%;z-index:' + z + ';';
        }
        return 'left:' + x + '%;top:' + y + '%;width:' + Math.min(w, 14) + '%;height:' + Math.min(h, 11) + '%;z-index:' + z + ';';
    }

    function htmlSillas(cap, forma) {
        var n = Math.min(8, Math.max(2, parseInt(cap, 10) || 4));
        var slots = forma === 'rectangular'
            ? ['n', 's', 'e', 'o']
            : ['n', 'ne', 'e', 'se', 's', 'sw', 'w', 'nw'];
        var html = '<div class="habbo-sillas" aria-hidden="true">';
        for (var i = 0; i < n && i < slots.length; i++) {
            html += '<span class="habbo-chair habbo-chair--' + slots[i] + '"></span>';
        }
        html += '</div>';
        return html;
    }

    function htmlMesaHabbo(m, x, y, w, h) {
        var forma = formaMesa(m);
        var libre = esLibre(m);
        var cls = 'habbo-furni habbo-furni--mesa forma-' + forma
            + (libre ? ' habbo-furni--libre' : ' habbo-furni--ocupada');
        if (String(mesaSeleccionadaId) === String(m.id)) cls += ' habbo-furni--selected';

        var cap = m.capacidad || 0;
        var titulo = libre
            ? 'Mesa ' + m.numero_mesa + ' \u2014 Libre (' + cap + ' p.)'
            : 'Mesa ' + m.numero_mesa + ' \u2014 Ocupada';

        return '<button type="button" class="' + cls + '" style="' + estiloMesa(forma, x, y, w, h) + '"'
            + ' title="' + esc(titulo) + '" data-mesa-id="' + m.id + '"'
            + ' data-mesa-num="' + esc(etiquetaMesa(m.numero_mesa)) + '"'
            + ' data-mesa-cap="' + cap + '" data-mesa-libre="' + (libre ? '1' : '0') + '">'
            + '<span class="habbo-furni__shadow"></span>'
            + htmlSillas(cap, forma)
            + '<span class="habbo-table">'
            + '<span class="habbo-table__cloth"></span>'
            + '<span class="habbo-table__rim"></span>'
            + '<span class="habbo-table__leg"></span>'
            + '</span>'
            + '<span class="habbo-furni__tag">' + esc(etiquetaMesa(m.numero_mesa)) + '</span>'
            + (libre ? '<span class="habbo-furni__sparkle" aria-hidden="true"></span>' : '')
            + '</button>';
    }

    function aplicarCanvas(datos) {
        var ar = parseInt(datos.ancho_referencia, 10) || 100;
        var al = parseInt(datos.alto_referencia, 10) || 150;
        var canvas = document.getElementById('menu-habbo-canvas');
        if (!canvas) return;
        canvas.style.aspectRatio = ar + ' / ' + al;
        canvas.style.minHeight = Math.min(380, Math.max(220, al * 2.2)) + 'px';
    }

    function renderZonas(zonas) {
        var cont = document.getElementById('menu-habbo-zonas');
        if (!cont) return;
        var html = '';
        (zonas || []).forEach(function (z) {
            var color = z.color || '#a8d4a0';
            html += '<div class="habbo-rug" style="'
                + 'left:' + z.x + '%;top:' + z.y + '%;width:' + z.w + '%;height:' + z.h + '%;'
                + '--rug-color:' + color + ';">'
                + '<span class="habbo-rug__tile"></span>'
                + '<span class="habbo-rug__label">' + esc(z.nombre || z.id) + '</span>'
                + '</div>';
        });
        cont.innerHTML = html;
    }

    function renderMesas(mesas) {
        var cont = document.getElementById('menu-habbo-mesas');
        var sinPos = document.getElementById('menu-habbo-sin-pos');
        if (!cont) return { libres: 0, total: 0, visibles: 0 };

        var lista = (mesas || []).slice().sort(function (a, b) {
            var ya = parseFloat(a.plano_y) || 0;
            var yb = parseFloat(b.plano_y) || 0;
            return ya - yb;
        });

        var html = '';
        var chips = '';
        var idxSin = 0;
        var libres = 0;
        var visibles = 0;

        lista.forEach(function (m) {
            if (esLibre(m)) libres++;
            if (!pasaFiltro(m)) return;

            visibles++;
            var x, y, w, h;
            if (tienePosicion(m)) {
                x = parseFloat(m.plano_x);
                y = parseFloat(m.plano_y);
                w = parseFloat(m.plano_ancho) || 7;
                h = parseFloat(m.plano_alto) || 7;
                html += htmlMesaHabbo(m, x, y, w, h);
            } else {
                w = 7;
                h = 7;
                x = 36 + (idxSin % 5) * 12;
                y = 10 + Math.floor(idxSin / 5) * 11;
                idxSin++;
                html += htmlMesaHabbo(m, x, y, w, h);
                if (esLibre(m)) {
                    chips += '<button type="button" class="habbo-chip habbo-chip--libre" data-mesa-id="' + m.id + '">'
                        + esc(etiquetaMesa(m.numero_mesa)) + '</button>';
                }
            }
        });

        cont.innerHTML = html;
        if (sinPos) {
            if (chips) {
                sinPos.innerHTML = '<p class="habbo-sin-pos__title">Sin ubicaci\u00f3n en el plano</p><div class="habbo-chips">' + chips + '</div>';
                sinPos.hidden = false;
            } else {
                sinPos.innerHTML = '';
                sinPos.hidden = true;
            }
        }

        return { libres: libres, total: (mesas || []).length, visibles: visibles };
    }

    function actualizarContador(stats) {
        var el = document.getElementById('menu-habbo-stats');
        if (!el) return;
        if (!stats.total) {
            el.textContent = 'No hay mesas en esta sucursal.';
            return;
        }
        var extra = filtroActivo === 'libres' ? ' (filtro: libres)' : '';
        el.innerHTML = '<span class="habbo-stats__icon">\u2728</span> <strong>' + stats.libres + '</strong> libres'
            + ' \u00b7 <strong>' + stats.visibles + '</strong> en el mapa'
            + ' \u00b7 ' + stats.total + ' total' + extra;
    }

    function mostrarBubble(btn) {
        var bubble = document.getElementById('habbo-bubble');
        if (!bubble || !btn) return;
        var libre = btn.getAttribute('data-mesa-libre') === '1';
        var num = btn.getAttribute('data-mesa-num') || '';
        var cap = btn.getAttribute('data-mesa-cap') || '0';
        bubble.hidden = false;
        bubble.innerHTML = '<div class="habbo-bubble__tail"></div>'
            + '<p class="habbo-bubble__title">Mesa ' + esc(num) + '</p>'
            + '<p class="habbo-bubble__meta">'
            + (libre ? '<span class="habbo-bubble__ok">\u25CF Libre</span>' : '<span class="habbo-bubble__busy">\u25CF Ocupada</span>')
            + ' &middot; ' + esc(cap) + ' personas</p>'
            + (libre ? '<p class="habbo-bubble__hint">Pide en caja o con el mesero esta mesa</p>' : '');
    }

    function aplicarZoom(wrap) {
        var scaler = wrap.querySelector('.habbo-room__scaler');
        if (scaler) scaler.style.transform = 'scale(' + zoomNivel + ')';
    }

    function bindInteraccion(wrap) {
        if (wrap.dataset.habboBound === '1') return;
        wrap.dataset.habboBound = '1';

        var viewport = wrap.querySelector('.habbo-room__viewport');
        var bubble = document.getElementById('habbo-bubble');

        wrap.addEventListener('click', function (e) {
            var tab = e.target.closest('[data-habbo-filter]');
            if (tab) {
                filtroActivo = tab.getAttribute('data-habbo-filter');
                wrap.querySelectorAll('[data-habbo-filter]').forEach(function (t) {
                    t.classList.toggle('is-active', t === tab);
                });
                if (planoCache) {
                    var stats = renderMesas(planoCache.mesas || []);
                    actualizarContador(stats);
                    if (mesaSeleccionadaId) {
                        var still = wrap.querySelector('.habbo-furni[data-mesa-id="' + mesaSeleccionadaId + '"]');
                        if (!still && bubble) bubble.hidden = true;
                    }
                }
                return;
            }

            var zoomBtn = e.target.closest('[data-habbo-zoom]');
            if (zoomBtn) {
                var z = zoomBtn.getAttribute('data-habbo-zoom');
                if (z === 'in') zoomNivel = Math.min(1.65, zoomNivel + 0.15);
                else if (z === 'out') zoomNivel = Math.max(0.75, zoomNivel - 0.15);
                else zoomNivel = 1;
                aplicarZoom(wrap);
                return;
            }

            var chip = e.target.closest('.habbo-chip');
            if (chip) {
                var id = chip.getAttribute('data-mesa-id');
                var btn = wrap.querySelector('.habbo-furni[data-mesa-id="' + id + '"]');
                if (btn) btn.click();
                return;
            }

            var furni = e.target.closest('.habbo-furni--mesa');
            if (furni) {
                e.stopPropagation();
                mesaSeleccionadaId = furni.getAttribute('data-mesa-id');
                wrap.querySelectorAll('.habbo-furni--selected').forEach(function (el) {
                    el.classList.remove('habbo-furni--selected');
                });
                furni.classList.add('habbo-furni--selected', 'habbo-furni--bounce');
                setTimeout(function () { furni.classList.remove('habbo-furni--bounce'); }, 450);
                mostrarBubble(furni);
                return;
            }

            if (bubble && !e.target.closest('.habbo-bubble')) {
                bubble.hidden = true;
                mesaSeleccionadaId = null;
                wrap.querySelectorAll('.habbo-furni--selected').forEach(function (el) {
                    el.classList.remove('habbo-furni--selected');
                });
            }
        });

        if (viewport) {
            var pan = { on: false, x: 0, y: 0, sl: 0, st: 0 };
            viewport.addEventListener('pointerdown', function (e) {
                if (e.target.closest('.habbo-furni')) return;
                pan.on = true;
                pan.x = e.clientX;
                pan.y = e.clientY;
                pan.sl = viewport.scrollLeft;
                pan.st = viewport.scrollTop;
                viewport.setPointerCapture(e.pointerId);
            });
            viewport.addEventListener('pointermove', function (e) {
                if (!pan.on) return;
                viewport.scrollLeft = pan.sl - (e.clientX - pan.x);
                viewport.scrollTop = pan.st - (e.clientY - pan.y);
            });
            viewport.addEventListener('pointerup', function () { pan.on = false; });
            viewport.addEventListener('pointercancel', function () { pan.on = false; });
        }
    }

    function hostHtml() {
        return '<div class="menu-habbo-plano-wrap">'
            + '<div class="habbo-toolbar">'
            + '<div class="habbo-toolbar__tabs">'
            + '<button type="button" class="habbo-tab is-active" data-habbo-filter="todas">Todas</button>'
            + '<button type="button" class="habbo-tab" data-habbo-filter="libres">Solo libres</button>'
            + '</div>'
            + '<div class="habbo-toolbar__zoom">'
            + '<button type="button" data-habbo-zoom="out" aria-label="Alejar">\u2212</button>'
            + '<button type="button" data-habbo-zoom="reset" aria-label="Tama\u00f1o normal">1:1</button>'
            + '<button type="button" data-habbo-zoom="in" aria-label="Acercar">+</button>'
            + '</div>'
            + '</div>'
            + '<p class="menu-habbo-stats" id="menu-habbo-stats"></p>'
            + '<p class="habbo-pan-hint"><i class="fas fa-hand-paper"></i> Arrastra el piso para moverte</p>'
            + '<div class="habbo-room">'
            + '<div class="habbo-room__sky" aria-hidden="true"></div>'
            + '<div class="habbo-room__trim" aria-hidden="true"></div>'
            + '<div class="habbo-room__wall habbo-room__wall--left" aria-hidden="true"></div>'
            + '<div class="habbo-room__wall habbo-room__wall--right" aria-hidden="true"></div>'
            + '<div class="habbo-room__viewport">'
            + '<div class="habbo-room__scaler">'
            + '<div class="habbo-room__floor" id="menu-habbo-canvas">'
            + '<div class="habbo-iso-grid" aria-hidden="true"></div>'
            + '<div id="menu-habbo-zonas"></div>'
            + '<div id="menu-habbo-mesas"></div>'
            + '<div class="habbo-avatar habbo-avatar--idle" aria-hidden="true"></div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div id="habbo-bubble" class="habbo-bubble" hidden></div>'
            + '</div>'
            + '<div class="habbo-leyenda">'
            + '<span><i class="habbo-legend habbo-legend--libre"></i> Libre</span>'
            + '<span><i class="habbo-legend habbo-legend--ocupada"></i> Ocupada</span>'
            + '<span><i class="habbo-legend habbo-legend--rug"></i> Zona</span>'
            + '</div>'
            + '<div id="menu-habbo-sin-pos" class="habbo-sin-pos" hidden></div>'
            + '</div>';
    }

    function render(datos, contentDiv) {
        planoCache = datos;
        filtroActivo = 'todas';
        zoomNivel = 1;
        mesaSeleccionadaId = null;
        contentDiv.innerHTML = hostHtml();
        aplicarCanvas(datos);
        renderZonas(datos.zonas || []);
        var stats = renderMesas(datos.mesas || []);
        actualizarContador(stats);
        bindInteraccion(contentDiv);
        aplicarZoom(contentDiv);
    }

    function skeletonHtml() {
        return '<div class="menu-habbo-plano-wrap menu-habbo-plano-wrap--loading">'
            + '<div class="habbo-loader">'
            + '<div class="habbo-loader__room"></div>'
            + '<p>Cargando sala\u2026</p></div></div>';
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
