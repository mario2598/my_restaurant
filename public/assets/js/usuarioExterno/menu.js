/**
 * Menú público — UI estilo menú digital; datos desde Laravel (MENU_TIPOS_INICIAL o API).
 */
var tipos = [];
var categoriaAbierta = null;
var menuIdSucursal = null;

/** Asegura array (en prod JSON a veces llega como objeto). */
function normalizarLista(datos) {
    if (!datos) {
        return [];
    }
    if (Array.isArray(datos)) {
        return datos;
    }
    if (typeof datos === 'object') {
        return Object.keys(datos)
            .sort(function (a, b) { return Number(a) - Number(b); })
            .map(function (k) { return datos[k]; })
            .filter(function (item) { return item && typeof item === 'object'; });
    }
    return [];
}

function normalizarTipos(datos) {
    var cats = normalizarLista(datos);
    cats.forEach(function (cat) {
        if (cat && cat.productos) {
            cat.productos = normalizarLista(cat.productos);
        }
    });
    return cats;
}

var $q = function (sel) { return document.querySelector(sel); };
var fmtPrecio = function (n) {
    return '\u20A1' + Math.round(Number(n) || 0).toLocaleString('es-CR');
};

function escHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function imagenValida(url) {
    if (!url || String(url).trim() === '') return false;
    if (String(url).indexOf('default-logo') !== -1) return false;
    if (url === base_path + '/storage' || url === '/storage') return false;
    if (url.indexOf('/storage') !== -1 && !url.match(/\/storage\/.+/)) return false;
    return true;
}

function urlImagen(url) {
    return imagenValida(url) ? url : (typeof DEFAULT_IMAGE_URL !== 'undefined' ? DEFAULT_IMAGE_URL : '');
}

function wireEnlaces() {
    var phone = String(typeof MENU_WHATSAPP !== 'undefined' ? MENU_WHATSAPP : '').replace(/\D/g, '');
    if (phone) {
        var wa = 'https://wa.me/506' + phone + '?text=' + encodeURIComponent('Hola, me gustar\u00eda ordenar:');
        var order = $q('#order-link');
        if (order) order.href = wa;
    }
}

function iniciarMenu() {
    tipos = normalizarTipos(tipos);
    if (!tipos.length) {
        var list = $q('#category-list');
        if (list) {
            list.innerHTML = '<p class="small" style="color:rgba(244,234,208,0.9);text-align:center;padding:2rem 0;">No hay productos disponibles en el men\u00fa.</p>';
        }
        return;
    }
    wireEnlaces();
    renderDestacados();
    renderListaCategorias();
    renderNavCategorias();
    configurarBusqueda();
    configurarDetalle();
    configurarVistaCategoria();
}

function renderNavCategorias() {
    var nav = $q('#menu-nav-cats');
    if (!nav) return;
    nav.innerHTML = '';
    tipos.forEach(function (cat, idx) {
        var n = (cat.productos || []).length;
        if (n < 1) return;
        var li = document.createElement('li');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.dataset.idx = String(idx);
        btn.innerHTML = '<span>' + escHtml(cat.categoria) + '</span>'
            + '<span class="menu-nav-count">' + n + '</span>';
        btn.onclick = function () {
            abrirCategoria(cat, idx);
            marcarNavCategoriaActiva(idx);
        };
        li.appendChild(btn);
        nav.appendChild(li);
    });
}

function marcarNavCategoriaActiva(idx) {
    var nav = $q('#menu-nav-cats');
    if (!nav) return;
    nav.querySelectorAll('button').forEach(function (btn) {
        btn.classList.toggle('is-active', idx >= 0 && btn.dataset.idx === String(idx));
    });
}

function sucursalMenuPorDefecto() {
    var list = window.MENU_SUCURSALES || [];
    if (list.length) return Number(list[0].id);
    return 1;
}

function idSucursalValido(id) {
    var list = window.MENU_SUCURSALES || [];
    for (var i = 0; i < list.length; i++) {
        if (Number(list[i].id) === Number(id)) return true;
    }
    return false;
}

function nombreSucursalPorId(id) {
    var list = window.MENU_SUCURSALES || [];
    for (var i = 0; i < list.length; i++) {
        if (Number(list[i].id) === Number(id)) return list[i].nombre || '';
    }
    return '';
}

function guardarSucursalMenu(id) {
    try {
        localStorage.setItem(window.MENU_STORAGE_KEY || 'menu_id_sucursal', String(id));
    } catch (e) { /* ignore */ }
}

function leerSucursalGuardada() {
    try {
        var v = localStorage.getItem(window.MENU_STORAGE_KEY || 'menu_id_sucursal');
        return v ? parseInt(v, 10) : 0;
    } catch (e) {
        return 0;
    }
}

function mostrarOverlaySucursal(show) {
    var ov = $q('#menu-sucursal-overlay');
    var shell = $q('#menu-shell');
    if (ov) {
        ov.classList.toggle('is-visible', !!show);
        ov.setAttribute('aria-hidden', show ? 'false' : 'true');
    }
    if (shell) shell.classList.toggle('menu-shell--pending', !!show);
    document.body.classList.toggle('no-scroll', !!show);
}

function resetPanelMesasMenu() {
    var contentDiv = document.getElementById('mesas-disponibles-content');
    if (contentDiv) {
        delete contentDiv.dataset.loaded;
        contentDiv.innerHTML = '<p class="menu-mesas-placeholder small text-muted mb-0">Toque para ver el mapa de mesas libres</p>';
    }
    var panel = document.getElementById('menu-mesas-panel');
    if (panel) panel.classList.remove('is-open');
}

function seleccionarSucursalMenu(id, opts) {
    opts = opts || {};
    menuIdSucursal = Number(id);
    if (!idSucursalValido(menuIdSucursal)) return;

    guardarSucursalMenu(menuIdSucursal);
    mostrarOverlaySucursal(false);

    var btnCambiar = $q('#menu-cambiar-sucursal');
    if (btnCambiar) btnCambiar.hidden = false;

    var label = $q('#menu-sucursal-label');
    if (label) label.textContent = nombreSucursalPorId(menuIdSucursal);

    var logo = $q('#menu-logo');
    if (logo && typeof base_path !== 'undefined') {
        logo.onerror = function () {
            if (typeof DEFAULT_IMAGE_URL !== 'undefined') logo.src = DEFAULT_IMAGE_URL;
        };
        logo.src = base_path + '/assets/images/sucursales/' + menuIdSucursal + '/logo_sistema.png';
    }

    resetPanelMesasMenu();
    if (typeof cerrarCategoria === 'function') cerrarCategoria();
    if (typeof cerrarDetalle === 'function') cerrarDetalle();

    if (!opts.skipUrl) {
        try {
            var u = new URL(window.location.href);
            u.searchParams.set('sucursal', String(menuIdSucursal));
            window.history.replaceState({}, '', u);
        } catch (e) { /* ignore */ }
    }

    var iniciales = opts.iniciales ? normalizarTipos(opts.iniciales) : [];
    if (iniciales.length) {
        tipos = iniciales;
        iniciarMenu();
        return;
    }
    cargarTiposGeneral(menuIdSucursal);
}

function configurarSelectorSucursal() {
    var list = $q('#menu-sucursal-list');
    if (!list) return;
    list.querySelectorAll('.menu-sucursal-btn').forEach(function (btn) {
        btn.onclick = function () {
            seleccionarSucursalMenu(btn.getAttribute('data-id'));
        };
    });
    var cambiar = $q('#menu-cambiar-sucursal');
    if (cambiar) {
        cambiar.onclick = function () {
            mostrarOverlaySucursal(true);
        };
    }
}

function cargarTiposGeneral(idSucursal) {
    var id = Number(idSucursal || menuIdSucursal);
    if (!id || !idSucursalValido(id)) return;
    menuIdSucursal = id;

    var list = $q('#category-list');
    if (list) {
        list.innerHTML = '<p class="small" style="color:rgba(244,234,208,0.9);text-align:center;padding:2rem 0;">Cargando men\u00fa\u2026</p>';
    }

    $.ajax({
        url: base_path + '/usuarioExterno/menuMobile/cargarTiposGeneral',
        type: 'post',
        dataType: 'json',
        data: { _token: CSRF_TOKEN, id_sucursal: menuIdSucursal }
    }).done(function (response) {
        if (!response.estado) {
            if (typeof showError === 'function') {
                showError(response.mensaje || 'Error al cargar el men\u00fa');
            }
            return;
        }
        tipos = normalizarTipos(response.datos);
        iniciarMenu();
    }).fail(function () {
        if (typeof showError === 'function') {
            showError('No se pudo cargar el men\u00fa');
        }
    });
}

function todosLosProductos() {
    var lista = [];
    tipos.forEach(function (cat) {
        normalizarLista(cat.productos).forEach(function (p) {
            lista.push({ producto: p, categoria: cat.categoria });
        });
    });
    return lista;
}

function renderDestacados() {
    var cont = $q('#featured');
    var head = $q('#featured-head');
    if (!cont) return;
    cont.innerHTML = '';

    var picks = [];
    tipos.forEach(function (cat) {
        normalizarLista(cat.productos).forEach(function (p) {
            if (imagenValida(p.url_imagen) && picks.length < 8) {
                picks.push({ producto: p, categoria: cat.categoria });
            }
        });
    });
    if (picks.length < 4) {
        tipos.forEach(function (cat) {
            normalizarLista(cat.productos).slice(0, 2).forEach(function (p) {
                if (picks.length < 8) {
                    picks.push({ producto: p, categoria: cat.categoria });
                }
            });
        });
    }

    if (head) head.textContent = 'Destacados';

    picks.forEach(function (item) {
        var p = item.producto;
        var img = urlImagen(p.url_imagen);
        var card = document.createElement('button');
        card.type = 'button';
        card.className = 'featured__card' + (img ? '' : ' no-image');
        card.innerHTML =
            (img ? '<img src="' + escHtml(img) + '" alt="' + escHtml(p.nombre) + '" loading="lazy">' : '') +
            '<div class="featured__overlay"></div>' +
            '<div class="featured__label">' +
            '<h3>' + escHtml(p.nombre) + '</h3>' +
            '<p>' + escHtml(item.categoria) + '</p>' +
            '</div>' +
            '<div class="featured__price">' +
            '<strong>' + fmtPrecio(p.precio) + '</strong>' +
            '<span class="featured__cta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>' +
            '</div>';
        card.onclick = function () { abrirDetalle(p, item.categoria); };
        cont.appendChild(card);
    });
}

function renderListaCategorias() {
    var list = $q('#category-list');
    if (!list) return;
    list.innerHTML = '';

    tipos.forEach(function (cat, idx) {
        var n = (cat.productos || []).length;
        if (n < 1) return;
        var img = urlImagen(cat.url_imagen);
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cat-card';
        btn.innerHTML =
            (img ? '<img src="' + escHtml(img) + '" alt="' + escHtml(cat.categoria) + '" loading="lazy">' : '') +
            '<div class="cat-card__overlay"></div>' +
            '<div class="cat-card__content">' +
            '<div><h3>' + escHtml(cat.categoria) + '</h3>' +
            '<span>' + n + (n === 1 ? ' opci\u00f3n' : ' opciones') + '</span></div>' +
            '<div class="cat-card__chevron"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></div>' +
            '</div>';
        btn.onclick = function () { abrirCategoria(cat, idx); };
        list.appendChild(btn);
    });
}

function abrirCategoria(cat, idx) {
    categoriaAbierta = idx;
    $q('#cat-name').textContent = cat.categoria || '';
    var n = (cat.productos || []).length;
    $q('#cat-desc').textContent = n + (n === 1 ? ' opci\u00f3n' : ' opciones');
    var list = $q('#item-list');
    list.innerHTML = '';

    normalizarLista(cat.productos).forEach(function (p) {
        var img = urlImagen(p.url_imagen);
        var inicial = (p.nombre || '?').trim().charAt(0).toUpperCase();
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'item-card';
        btn.innerHTML =
            (img
                ? '<img class="item-card__img" src="' + escHtml(img) + '" alt="">'
                : '<div class="item-card__img placeholder">' + escHtml(inicial) + '</div>') +
            '<div class="item-card__body">' +
            '<h3>' + escHtml(p.nombre) + '</h3>' +
            (p.descripcion ? '<p>' + escHtml(p.descripcion) + '</p>' : '') +
            '</div>' +
            '<div class="item-card__price">' + fmtPrecio(p.precio) + '</div>';
        btn.onclick = function () { abrirDetalle(p, cat.categoria); };
        list.appendChild(btn);
    });

    $q('#home-view').hidden = true;
    $q('#cat-view').classList.add('is-active');
    marcarNavCategoriaActiva(idx);
    window.scrollTo(0, 0);
}

function cerrarCategoria() {
    $q('#cat-view').classList.remove('is-active');
    $q('#home-view').hidden = false;
    categoriaAbierta = null;
    marcarNavCategoriaActiva(-1);
}

function abrirDetalle(producto, nombreCategoria) {
    var hero = $q('#detail-hero');
    var img = urlImagen(producto.url_imagen);
    if (img) {
        hero.className = 'detail__hero';
        hero.innerHTML = '<img src="' + escHtml(img) + '" alt="' + escHtml(producto.nombre) + '">';
    } else {
        hero.className = 'detail__hero no-image';
        hero.textContent = (producto.nombre || '?').trim().charAt(0).toUpperCase();
    }
    $q('#detail-cat').textContent = nombreCategoria || '';
    $q('#detail-name').textContent = producto.nombre || '';
    $q('#detail-price').textContent = fmtPrecio(producto.precio);
    $q('#detail-desc').textContent = producto.descripcion || 'Sin descripci\u00f3n adicional.';
    var detail = $q('#detail');
    detail.classList.add('is-open');
    detail.setAttribute('aria-hidden', 'false');
    document.body.classList.add('no-scroll');
    detail.scrollTop = 0;
}

function cerrarDetalle() {
    var detail = $q('#detail');
    detail.classList.remove('is-open');
    detail.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('no-scroll');
}

function configurarDetalle() {
    var closeBtn = $q('#detail-close');
    if (closeBtn) closeBtn.onclick = cerrarDetalle;
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrarDetalle();
    });
}

function configurarVistaCategoria() {
    var back = $q('#back-btn');
    if (back) back.onclick = cerrarCategoria;
}

var menuBusquedaInicializada = false;

function setSeccionesMenuVisibles(mostrar, excepto) {
    excepto = excepto || '';
    document.querySelectorAll('[data-menu-section]').forEach(function (el) {
        if (excepto && el.getAttribute('data-menu-section') === excepto) return;
        el.hidden = !mostrar;
    });
    var feat = $q('#featured');
    if (feat && excepto !== 'destacados') feat.hidden = !mostrar;
    var navAside = $q('#menu-sidebar');
    if (navAside) navAside.hidden = !mostrar;
}

function configurarBusqueda() {
    var input = $q('#search');
    if (!input || menuBusquedaInicializada) return;
    menuBusquedaInicializada = true;

    input.addEventListener('input', function (e) {
        var q = e.target.value.trim().toLowerCase();
        var list = $q('#category-list');
        var resultsHead = $q('#search-results-head');
        var searchEmpty = $q('#search-empty');

        if (!q) {
            if (searchEmpty) searchEmpty.hidden = true;
            if (resultsHead) resultsHead.hidden = true;
            if (list) list.hidden = false;
            setSeccionesMenuVisibles(true);
            renderDestacados();
            renderListaCategorias();
            renderNavCategorias();
            return;
        }

        setSeccionesMenuVisibles(false);
        if (resultsHead) resultsHead.hidden = false;
        if (list) {
            list.hidden = false;
            list.innerHTML = '';
        }

        var matches = [];
        tipos.forEach(function (cat) {
            normalizarLista(cat.productos).forEach(function (p) {
                var nombre = String(p.nombre || '').toLowerCase();
                var desc = String(p.descripcion || '').toLowerCase();
                var catNombre = String(cat.categoria || '').toLowerCase();
                if (nombre.indexOf(q) !== -1 || desc.indexOf(q) !== -1 || catNombre.indexOf(q) !== -1) {
                    matches.push({ producto: p, categoria: cat.categoria });
                }
            });
        });

        if (!matches.length) {
            if (searchEmpty) searchEmpty.hidden = false;
            return;
        }
        if (searchEmpty) searchEmpty.hidden = true;

        matches.forEach(function (item) {
            var p = item.producto;
            var img = urlImagen(p.url_imagen);
            var inicial = (p.nombre || '?').trim().charAt(0).toUpperCase();
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'item-card';
            btn.style.marginBottom = '0.6rem';
            btn.innerHTML =
                (img
                    ? '<img class="item-card__img" src="' + escHtml(img) + '" alt="">'
                    : '<div class="item-card__img placeholder">' + escHtml(inicial) + '</div>') +
                '<div class="item-card__body">' +
                '<h3>' + escHtml(p.nombre) + '</h3>' +
                '<p>' + escHtml(item.categoria) + '</p>' +
                '</div>' +
                '<div class="item-card__price">' + fmtPrecio(p.precio) + '</div>';
            btn.onclick = function () { abrirDetalle(p, item.categoria); };
            list.appendChild(btn);
        });
    });
}

function toggleMesasMenuPanel() {
    var panel = document.getElementById('menu-mesas-panel');
    var chevron = document.getElementById('menu-mesas-chevron');
    if (!panel) return;
    panel.classList.toggle('is-open');
    if (chevron) {
        chevron.style.transform = panel.classList.contains('is-open') ? 'rotate(180deg)' : '';
    }
    if (panel.classList.contains('is-open')) {
        cargarMesasDisponiblesMenu();
    }
}

function cargarMesasDisponiblesMenu() {
    var contentDiv = document.getElementById('mesas-disponibles-content');
    if (!contentDiv || contentDiv.dataset.loaded === '1') return;
    if (!menuIdSucursal) {
        contentDiv.innerHTML = '<p class="small text-muted mb-0">Seleccione una sucursal primero.</p>';
        return;
    }

    if (typeof MenuPlanoPublico !== 'undefined' && MenuPlanoPublico.cargar) {
        MenuPlanoPublico.cargar(menuIdSucursal, contentDiv);
        return;
    }

    $.ajax({
        url: base_path + '/usuarioExterno/menu/mesas-disponibles',
        type: 'POST',
        dataType: 'json',
        data: { _token: CSRF_TOKEN, id_sucursal: menuIdSucursal }
    }).done(function (response) {
        contentDiv.dataset.loaded = '1';
        if (!response.estado || !response.datos || !response.datos.length) {
            contentDiv.innerHTML = '<p class="small text-muted mb-0">No hay mesas libres en este momento.</p>';
            return;
        }
        var html = '<div class="menu-mesas-grid">';
        response.datos.forEach(function (mesa) {
            html += '<div class="menu-mesa-chip" title="Capacidad: ' + escHtml(mesa.capacidad) + '">';
            html += '<i class="fas fa-chair"></i>' + escHtml(mesa.numero_mesa || '\u2014');
            html += '</div>';
        });
        html += '</div>';
        contentDiv.innerHTML = html;
    }).fail(function () {
        contentDiv.innerHTML = '<p class="small text-danger mb-0">No se pudieron cargar las mesas.</p>';
    });
}

$(document).ready(function () {
    configurarSelectorSucursal();

    var idInicial = window.MENU_ID_SUCURSAL;
    if (idInicial == null || idInicial === 'null' || isNaN(Number(idInicial))) {
        if (window.MENU_REQUIERE_SUCURSAL) {
            var guardada = leerSucursalGuardada();
            if (idSucursalValido(guardada)) idInicial = guardada;
        } else if ((window.MENU_SUCURSALES || []).length === 1) {
            idInicial = Number(window.MENU_SUCURSALES[0].id);
        }
    } else {
        idInicial = Number(idInicial);
    }

    if (idInicial && idSucursalValido(idInicial)) {
        var iniciales = normalizarTipos(window.MENU_TIPOS_INICIAL);
        var skipUrl = window.MENU_ID_SUCURSAL != null && window.MENU_ID_SUCURSAL !== 'null';
        if (iniciales.length) {
            seleccionarSucursalMenu(idInicial, { iniciales: iniciales, skipUrl: skipUrl });
        } else {
            seleccionarSucursalMenu(idInicial, { skipUrl: skipUrl });
        }
        return;
    }

    if (window.MENU_REQUIERE_SUCURSAL) {
        mostrarOverlaySucursal(true);
        return;
    }

    var inicialesSolo = normalizarTipos(window.MENU_TIPOS_INICIAL);
    if (inicialesSolo.length) {
        tipos = inicialesSolo;
        menuIdSucursal = sucursalMenuPorDefecto();
        iniciarMenu();
        return;
    }
    menuIdSucursal = sucursalMenuPorDefecto();
    cargarTiposGeneral(menuIdSucursal);
});
