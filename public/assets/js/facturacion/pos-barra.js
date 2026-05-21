/**
 * POS Barra — capa sobre pos.js (venta rápida + cuentas abiertas).
 */
var cuentaBarraActiva = null;
var cuentasBarraLista = [];

/* Pills de tipos/categorías con estado activo (evita botones blancos sueltos) */
window.generarHTMLTipo = function (indice, nombre) {
    var active = indice === tipoSeleccionado ? ' active' : '';
    return '<li class="nav-item" onclick="seleccionarTipo(' + indice + ')">'
        + '<a class="nav-link' + active + '" href="javascript:void(0);">' + escBarraHtml(nombre) + '</a></li>';
};

window.generarHTMLCategoria = function (indice, nombre) {
    var active = indice === categoriaSeleccionada ? ' active' : '';
    return '<li class="nav-item" onclick="seleccionarCategoria(' + indice + ')">'
        + '<a class="nav-link' + active + '" href="javascript:void(0);">' + escBarraHtml(nombre) + '</a></li>';
};

var seleccionarTipoBarraOrig = typeof seleccionarTipo === 'function' ? seleccionarTipo : null;
if (seleccionarTipoBarraOrig) {
    window.seleccionarTipo = function (indice) {
        seleccionarTipoBarraOrig(indice);
        if (typeof generarTipos === 'function') {
            generarTipos();
        }
    };
}

var seleccionarCategoriaBarraOrig = typeof seleccionarCategoria === 'function' ? seleccionarCategoria : null;
if (seleccionarCategoriaBarraOrig) {
    window.seleccionarCategoria = function (indice) {
        seleccionarCategoriaBarraOrig(indice);
        if (typeof generarCategorias === 'function') {
            generarCategorias();
        }
    };
}

var generarHTMLProductoOriginal = typeof generarHTMLProducto === 'function' ? generarHTMLProducto : null;

function inicializarMapaContenedoresBarra() {
    contenedores.set('categorias', $('#scrl-categorias'));
    contenedores.set('productos', $('#pos-barra-productos-grid'));
    contenedores.set('tipos', $('#nv-tipos'));
    contenedores.set('orden', $('#tbody-orden'));
}

function generarHTMLProducto(nombre, codigo, precio, cantidad, tipoProd, descripcion) {
    var stock = '';
    if (tipoProd === 'E' && cantidad < 15) {
        stock = '<span class="pb-stock">Quedan ' + cantidad + '</span>';
    }
    return '<button type="button" class="pos-barra-prod-btn" data-codigo="' + escBarraHtml(codigo) + '" onclick="seleccionarProducto(\'N\',\'' + String(codigo).replace(/'/g, "\\'") + '\')">'
        + '<span class="pb-nombre">' + escBarraHtml(nombre) + '</span>'
        + '<span class="pb-precio">₡' + parseFloat(precio).toFixed(0) + '</span>'
        + stock + '</button>';
}

function escBarraHtml(s) {
    return String(s == null ? '' : s).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function generarProductos() {
    var categoria = tipos[tipoSeleccionado].categorias[categoriaSeleccionada];
    var cards = '';
    $(contenedores.get('productos')).html('');
    categoria.productos.forEach(function (producto) {
        cards += generarHTMLProducto(
            producto.nombre, producto.codigo, producto.precio, producto.cantidad,
            producto.tipoProducto, producto.descripcion || ''
        );
    });
    $(contenedores.get('productos')).html(cards);
    if ($('#tbody-productos').length) {
        $('#tbody-productos').html('');
    }
}

function filtrarProductosBarra(termino) {
    termino = (termino || '').trim().toLowerCase();
    if (termino.length > 0) {
        $('#btn-limpiar-busqueda').show();
        productosFiltrados = (productosGeneral || []).filter(function (producto) {
            var nombre = (producto.nombre || '').toLowerCase();
            var codigo = (producto.codigo || '').toLowerCase();
            var categoria = (producto.categoria || '').toLowerCase();
            return nombre.indexOf(termino) >= 0 || codigo.indexOf(termino) >= 0 || categoria.indexOf(termino) >= 0;
        });
        var html = '';
        if (productosFiltrados.length === 0) {
            html = '<p class="text-muted text-center p-3">Sin resultados</p>';
        } else {
            productosFiltrados.forEach(function (producto) {
                html += generarHTMLProducto(producto.nombre, producto.codigo, producto.precio, producto.cantidad, producto.tipoProducto, producto.descripcion || '');
            });
        }
        $('#pos-barra-productos-grid').html(html);
    } else {
        $('#btn-limpiar-busqueda').hide();
        generarProductos();
    }
}

function limpiarBusquedaProductos() {
    $('#buscador-productos').val('');
    filtrarProductosBarra('');
}

function keydownBuscadorBarra(e) {
    if (e.key === 'Enter') {
        var cod = $('#buscador-productos').val().trim();
        if (cod) {
            seleccionarProducto('N', cod, true);
            $('#buscador-productos').val('').focus();
        }
    }
}

function validarCajaAbiertaBarra() {
    if (!cajaAbierta) {
        $('#pos-barra-app').addClass('caja-cerrada');
        $('#contAbrirCaja').show();
    } else {
        $('#pos-barra-app').removeClass('caja-cerrada');
        $('#contAbrirCaja').hide();
        cargarCuentasBarra();
    }
}

function cargarCuentasBarra() {
    $.ajax({
        url: base_path + '/facturacion/posBarra/cuentas',
        type: 'get',
        dataType: 'json'
    }).done(function (r) {
        if (!r.estado) {
            $('#lista-cuentas-barra').html('<p class="text-danger small">' + (r.mensaje || 'Error') + '</p>');
            return;
        }
        cuentasBarraLista = r.datos || [];
        renderCuentasBarra();
    }).fail(function () {
        $('#lista-cuentas-barra').html('<p class="text-danger small">Error al cargar cuentas</p>');
    });
}

function renderCuentasBarra() {
    if (!cuentasBarraLista.length) {
        $('#lista-cuentas-barra').html('<p class="small text-muted mb-0">No hay cuentas abiertas.</p>');
        return;
    }
    var html = '';
    cuentasBarraLista.forEach(function (c) {
        var activa = cuentaBarraActiva && String(cuentaBarraActiva.id) === String(c.id) ? ' activa' : '';
        var total = c.total != null ? parseFloat(c.total) : 0;
        var mesaTxt = c.numero_mesa ? ' · Mesa ' + c.numero_mesa : '';
        html += '<div class="cuenta-barra-item' + activa + '" onclick="seleccionarCuentaBarra(' + c.id + ')">'
            + '<div class="cb-etiqueta">' + escBarraHtml(c.etiqueta) + mesaTxt + '</div>'
            + '<div class="cb-total">₡' + total.toLocaleString('es-CR') + '</div>'
            + (c.numero_orden ? '<div class="small text-muted">#' + escBarraHtml(c.numero_orden) + '</div>' : '<div class="small text-muted">Sin orden</div>')
            + '</div>';
    });
    $('#lista-cuentas-barra').html(html);
}

function promptNuevaCuentaBarra() {
    if (!cajaAbierta) {
        showError('Abra la caja primero.');
        return;
    }
    var etiqueta = prompt('Nombre de la cuenta (ej. Mesa 5, Barra, Grupo VIP):', 'Barra');
    if (etiqueta === null) return;
    etiqueta = etiqueta.trim();
    if (!etiqueta) {
        showError('La etiqueta es obligatoria.');
        return;
    }
    abrirCuentaBarra(etiqueta, $('#select_mesa').val());
}

function abrirCuentaBarra(etiqueta, mesaId) {
    $.ajax({
        url: base_path + '/facturacion/posBarra/cuentas/abrir',
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            etiqueta: etiqueta,
            mesa: mesaId || -1
        }
    }).done(function (r) {
        if (!r.estado) {
            showError(r.mensaje || 'Error');
            return;
        }
        cuentaBarraActiva = r.datos;
        prepararOrdenParaCuenta(cuentaBarraActiva);
        cargarCuentasBarra();
        showSuccess('Cuenta abierta: ' + etiqueta);
    }).fail(function () {
        showError('Error al abrir cuenta');
    });
}

function prepararOrdenParaCuenta(cuenta) {
    if (typeof limpiarOrden === 'function') {
        limpiarOrden();
    }
    cuentaBarraActiva = cuenta;
    ordenGestion.cuenta_barra_id = cuenta.id;
    ordenGestion.cliente = cuenta.etiqueta;
    ordenGestion.mesa = cuenta.mesa || -1;
    $('#txt-cliente').val(cuenta.etiqueta);
    if (cuenta.mesa) {
        $('#select_mesa').val(cuenta.mesa);
    } else {
        $('#select_mesa').val(-1);
    }
    $('#infoHeaderOrden').text(cuenta.numero_orden ? 'Orden ' + cuenta.numero_orden : 'Cuenta nueva');
    $('#pos-barra-cuenta-label').text(cuenta.etiqueta);

    if (cuenta.orden_activa && cuenta.orden_activa > 0) {
        cargarOrdenGestion(cuenta.orden_activa);
    }
}

function seleccionarCuentaBarra(id) {
    var c = cuentasBarraLista.find(function (x) { return String(x.id) === String(id); });
    if (!c) {
        $.ajax({
            url: base_path + '/facturacion/posBarra/cuentas/seleccionar',
            type: 'post',
            dataType: 'json',
            data: { _token: CSRF_TOKEN, id: id }
        }).done(function (r) {
            if (r.estado) prepararOrdenParaCuenta(r.datos);
        });
        return;
    }
    prepararOrdenParaCuenta(c);
    renderCuentasBarra();
}

function limpiarOrdenBarra() {
    if (!cuentaBarraActiva) {
        showError('Abra o seleccione una cuenta.');
        return;
    }
    var etiqueta = cuentaBarraActiva.etiqueta;
    var mesa = cuentaBarraActiva.mesa;
    var cid = cuentaBarraActiva.id;
    if (typeof limpiarOrden === 'function') limpiarOrden();
    cuentaBarraActiva = { id: cid, etiqueta: etiqueta, mesa: mesa };
    ordenGestion.cuenta_barra_id = cid;
    ordenGestion.cliente = etiqueta;
    $('#txt-cliente').val(etiqueta);
}

function iniciarOrdenBarra() {
    if (!cuentaBarraActiva) {
        showError('Abra o seleccione una cuenta primero.');
        return;
    }
    iniciarOrden();
}

function abrirModalPagoBarra() {
    if (!ordenGestion.id || ordenGestion.id < 1) {
        if (detalles.length > 0) {
            iniciarOrdenBarra();
            return;
        }
        showError('Agregue productos y envíe la orden antes de cobrar.');
        return;
    }
    abrirModalPago();
}

function pagoRapidoEfectivoBarra() {
    abrirModalPagoBarra();
    setTimeout(function () {
        if (typeof verificarAbrirModalPagoEfectivo === 'function') {
            verificarAbrirModalPagoEfectivo();
        }
    }, 400);
}

function recargarOrdenesBarra() {
    if (typeof recargarOrdenes === 'function') {
        recargarOrdenes();
        $('#mdl-ordenes').modal('show');
    }
}

function seleccionarMesaDesdeMapaBarra(mesaId) {
    if (cuentaBarraActiva) {
        ordenGestion.mesa = mesaId;
        $('#select_mesa').val(mesaId);
        if (ordenGestion.id > 0) {
            cambiarMesa();
        }
        return;
    }
    var mesaOpt = $('#select_mesa option[value="' + mesaId + '"]');
    var label = mesaOpt.length ? 'Mesa ' + mesaOpt.text().replace('Mesa ', '') : 'Mesa ' + mesaId;
    abrirCuentaBarra(label, mesaId);
}

var _cargarOrdenGestionOrig = typeof cargarOrdenGestion === 'function' ? cargarOrdenGestion : null;
if (_cargarOrdenGestionOrig) {
    window.cargarOrdenGestion = function (idOrden) {
        _cargarOrdenGestionOrig(idOrden);
        if (ordenGestion.cuenta_barra_id) {
            cuentaBarraActiva = cuentasBarraLista.find(function (c) {
                return String(c.id) === String(ordenGestion.cuenta_barra_id);
            }) || cuentaBarraActiva;
        }
        cargarCuentasBarra();
    };
}

var agregarProductoOrig = typeof agregarProducto === 'function' ? agregarProducto : null;
if (agregarProductoOrig) {
    window.agregarProducto = function (producto) {
        agregarProductoOrig(producto);
        if (ordenGestion.id && ordenGestion.id > 0 && !guardandoOrden) {
            ordenGestion.cuenta_barra_id = cuentaBarraActiva ? cuentaBarraActiva.id : ordenGestion.cuenta_barra_id;
            actualizarOrdenGestion();
        }
    };
}

var iniciarOrdenOrig = typeof iniciarOrden === 'function' ? iniciarOrden : null;
if (iniciarOrdenOrig) {
    window.iniciarOrden = function () {
        if (!cuentaBarraActiva && window.POS_CONFIG && window.POS_CONFIG.modo === 'barra') {
            showError('Seleccione o cree una cuenta.');
            return false;
        }
        if (!$('#txt-cliente').val().trim() && cuentaBarraActiva) {
            $('#txt-cliente').val(cuentaBarraActiva.etiqueta);
            ordenGestion.cliente = cuentaBarraActiva.etiqueta;
        }
        ordenGestion.cuenta_barra_id = cuentaBarraActiva ? cuentaBarraActiva.id : null;
        var r = iniciarOrdenOrig.apply(this, arguments);
        setTimeout(cargarCuentasBarra, 800);
        return r;
    };
}

var initOrig = typeof init === 'function' ? init : null;
window.init = function () {
    inicializarMapaContenedoresBarra();
    if (initOrig) {
        cargarProductosPos();
        limpiar();
    }
    validarCajaAbiertaBarra();
};

$(document).ready(function () {
    validarCajaAbiertaBarra();
    setTimeout(function () {
        if ($('#nv-tipos').children().length === 0 && typeof cargarProductosPos === 'function') {
            cargarProductosPos();
        }
    }, 500);
});
