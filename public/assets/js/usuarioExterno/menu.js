/**
 * Menú público — UI estilo menú digital; datos desde Laravel (MENU_TIPOS_INICIAL o API).
 */
var tipos = [];
var categoriaAbierta = null;

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

function cargarTiposGeneral() {
    $.ajax({
        url: base_path + '/usuarioExterno/menuMobile/cargarTiposGeneral',
        type: 'post',
        dataType: 'json',
        data: { _token: CSRF_TOKEN }
    }).done(function (response) {
        if (!response.estado) {
            if (typeof showError === 'function') {
                showError(response.mensaje || 'Error al cargar el men\u00fa');
            }
            return;
        }
        tipos = response.datos || [];
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
        (cat.productos || []).forEach(function (p) {
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
        (cat.productos || []).forEach(function (p) {
            if (imagenValida(p.url_imagen) && picks.length < 8) {
                picks.push({ producto: p, categoria: cat.categoria });
            }
        });
    });
    if (picks.length < 4) {
        tipos.forEach(function (cat) {
            (cat.productos || []).slice(0, 2).forEach(function (p) {
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

    (cat.productos || []).forEach(function (p) {
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

function configurarBusqueda() {
    var input = $q('#search');
    if (!input) return;
    input.addEventListener('input', function (e) {
        var q = e.target.value.trim().toLowerCase();
        if (!q) {
            $q('#search-empty').hidden = true;
            $q('#featured').hidden = false;
            document.querySelectorAll('#home-view .section-head').forEach(function (el) {
                el.hidden = false;
            });
            $q('#category-list').hidden = false;
            renderDestacados();
            renderListaCategorias();
            renderNavCategorias();
            return;
        }
        $q('#featured').hidden = true;
        document.querySelectorAll('.section-head').forEach(function (el) { el.hidden = true; });
        var list = $q('#category-list');
        list.hidden = false;
        list.innerHTML = '';
        var matches = [];
        tipos.forEach(function (cat) {
            (cat.productos || []).forEach(function (p) {
                var desc = (p.descripcion || '').toLowerCase();
                if ((p.nombre || '').toLowerCase().indexOf(q) !== -1 || desc.indexOf(q) !== -1) {
                    matches.push({ producto: p, categoria: cat.categoria });
                }
            });
        });
        if (matches.length === 0) {
            $q('#search-empty').hidden = false;
            return;
        }
        $q('#search-empty').hidden = true;
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

    $.ajax({
        url: base_path + '/usuarioExterno/menu/mesas-disponibles',
        type: 'POST',
        dataType: 'json',
        data: { _token: CSRF_TOKEN }
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
    if (window.MENU_TIPOS_INICIAL && window.MENU_TIPOS_INICIAL.length) {
        tipos = window.MENU_TIPOS_INICIAL;
        iniciarMenu();
    } else {
        cargarTiposGeneral();
    }
});
