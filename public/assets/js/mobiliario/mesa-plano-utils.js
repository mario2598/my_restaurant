/**
 * Utilidades compartidas: forma y tamaño de mesas en planos (mobiliario + POS).
 */
function getFormaMesa(m) {
    var f = (m && m.forma) ? String(m.forma).toLowerCase() : 'rectangular';
    if (f !== 'redonda' && f !== 'cuadrada' && f !== 'rectangular') {
        f = 'rectangular';
    }
    return f;
}

function esFormaCuadradaVisual(forma) {
    return forma === 'redonda' || forma === 'cuadrada';
}

/** No agranda la mesa: devuelve % tal cual están en BD. */
function dimensionesMesaGuardadas(w, h) {
    return {
        w: parseFloat(w) || 7,
        h: parseFloat(h) || 7
    };
}

/**
 * Estilo inline para el mapa. Redonda/cuadrada: solo ancho + aspect-ratio 1 (círculo real en canvas).
 */
function estiloPosicionMesa(forma, x, y, w, h) {
    var d = dimensionesMesaGuardadas(w, h);
    var s = 'left:' + x + '%;top:' + y + '%;width:' + d.w + '%;';
    if (esFormaCuadradaVisual(forma)) {
        s += 'height:auto;';
    } else {
        s += 'height:' + d.h + '%;';
    }
    return s;
}

/** Vista POS: limita % para evitar mesas gigantes o texto cortado. */
function estiloPosicionMesaPos(forma, x, y, w, h) {
    w = parseFloat(w) || 7;
    h = parseFloat(h) || 7;
    var maxCuad = 11;
    var maxW = 12;
    var maxH = 10;
    if (esFormaCuadradaVisual(forma)) {
        var tam = Math.min(Math.max(w, h), maxCuad);
        return estiloPosicionMesa(forma, x, y, tam, tam);
    }
    return estiloPosicionMesa(forma, x, y, Math.min(w, maxW), Math.min(h, maxH));
}

function etiquetaCortaMesa(numero, maxLen) {
    var s = String(numero == null ? '' : numero);
    maxLen = maxLen || 14;
    if (s.length <= maxLen) return s;
    return s.substring(0, maxLen - 1) + '\u2026';
}

function etiquetaFormaMesa(forma) {
    if (forma === 'redonda') return 'Redonda';
    if (forma === 'cuadrada') return 'Cuadrada';
    return 'Rectangular';
}

/**
 * Distribución de sillas según capacidad y forma (referencia visual del plano).
 */
function calcularDistribucionSillasMesa(forma, capacidad, planoAncho, planoAlto) {
    var cap = Math.max(1, Math.min(12, parseInt(capacidad, 10) || 4));
    var lista = [];
    var i;
    var w = parseFloat(planoAncho) || 7;
    var h = parseFloat(planoAlto) || 7;

    if (forma === 'redonda' || forma === 'cuadrada') {
        for (i = 0; i < cap; i++) {
            lista.push({
                tipo: 'arco',
                angulo: (360 / cap) * i - 90
            });
        }
        return lista;
    }

    var vertical = h > w * 1.08;
    if (vertical) {
        var der = Math.ceil(cap / 2);
        var izq = cap - der;
        for (i = 0; i < der; i++) {
            lista.push({ tipo: 'barra', lado: 'e', indice: i, total: der });
        }
        for (i = 0; i < izq; i++) {
            lista.push({ tipo: 'barra', lado: 'o', indice: i, total: izq });
        }
        return lista;
    }

    var arriba = Math.ceil(cap / 2);
    var abajo = cap - arriba;
    for (i = 0; i < arriba; i++) {
        lista.push({ tipo: 'barra', lado: 'n', indice: i, total: arriba });
    }
    for (i = 0; i < abajo; i++) {
        lista.push({ tipo: 'barra', lado: 's', indice: i, total: abajo });
    }
    return lista;
}

function htmlSillaMesaPlano(silla) {
    if (silla.tipo === 'arco') {
        var rad = (silla.angulo * Math.PI) / 180;
        var cx = 50 + 44 * Math.cos(rad);
        var cy = 50 + 44 * Math.sin(rad);
        var rot = silla.angulo + 90;
        return '<span class="mesa-silla mesa-silla--arco" style="'
            + 'left:' + cx + '%;top:' + cy + '%;'
            + 'transform:translate(-50%,-50%) rotate(' + rot + 'deg);" aria-hidden="true"></span>';
    }
    var lado = silla.lado || 'n';
    var total = Math.max(1, silla.total || 1);
    var slot = ((silla.indice + 1) / (total + 1)) * 100;
    var estilo = 'left:' + slot + '%;';
    if (lado === 'n') {
        estilo += 'top:0;transform:translate(-50%,0);';
    } else if (lado === 's') {
        estilo += 'bottom:0;transform:translate(-50%,0);';
    } else if (lado === 'e') {
        estilo = 'right:0;top:' + slot + '%;transform:translate(0,-50%);';
    } else if (lado === 'o') {
        estilo = 'left:0;top:' + slot + '%;transform:translate(0,-50%);';
    } else {
        estilo += 'left:0;top:' + slot + '%;transform:translate(0,-50%);';
    }
    return '<span class="mesa-silla mesa-silla--barra mesa-silla--' + lado + '" style="' + estilo + '" aria-hidden="true"></span>';
}

function htmlSillasMesaPlano(forma, capacidad, planoAncho, planoAlto) {
    var sillas = calcularDistribucionSillasMesa(forma, capacidad, planoAncho, planoAlto);
    var html = '<div class="mesa-plano-sillas" aria-hidden="true">';
    sillas.forEach(function (s) {
        html += htmlSillaMesaPlano(s);
    });
    html += '</div>';
    return html;
}

/**
 * Vista gráfica mesa + sillas + etiqueta (mobiliario y POS).
 */
function htmlContenidoMesaPlano(numero, capacidad, extraHtml, forma, planoAncho, planoAlto) {
    forma = forma || 'rectangular';
    return '<div class="mesa-plano-grafico">'
        + htmlSillasMesaPlano(forma, capacidad, planoAncho, planoAlto)
        + '<span class="mesa-plano-superficie" aria-hidden="true"></span>'
        + '<span class="plano-mesa-numero">' + numero + '</span>'
        + '<span class="plano-mesa-cap">' + (capacidad || 0) + ' p.</span>'
        + (extraHtml || '')
        + '</div>';
}

/** POS: número corto + hint opcional dentro del gráfico */
function htmlContenidoMesaPlanoPos(m, extraHtml) {
    var forma = getFormaMesa(m);
    var numero = typeof etiquetaCortaMesa === 'function' ? etiquetaCortaMesa(m.numero_mesa, 8) : m.numero_mesa;
    return '<div class="mesa-plano-grafico">'
        + htmlSillasMesaPlano(forma, m.capacidad, m.plano_ancho, m.plano_alto)
        + '<span class="mesa-plano-superficie" aria-hidden="true"></span>'
        + '<div class="mesa-plano-etiquetas">'
        + '<span class="pos-plano-mesa-num">' + numero + '</span>'
        + (extraHtml || '')
        + '</div>'
        + '</div>';
}

function htmlSelectorFormaMesa(valorActual, inputId) {
    inputId = inputId || 'detalle_forma';
    var formas = ['redonda', 'cuadrada', 'rectangular'];
    var html = '<label class="d-block small font-weight-bold mb-1">Forma de la mesa</label>'
        + '<input type="hidden" id="' + inputId + '" value="' + (valorActual || 'rectangular') + '">'
        + '<div class="mesa-forma-picker mb-2">';
    formas.forEach(function (f) {
        var sel = f === valorActual ? ' activa' : '';
        html += '<button type="button" class="mesa-forma-opt' + sel + '" data-forma="' + f + '" data-target="' + inputId + '"'
            + ' onclick="seleccionarFormaMesaPicker(this)" title="' + etiquetaFormaMesa(f) + '">'
            + '<span class="mesa-forma-preview-wrap">'
            + (f === 'redonda'
                ? '<span class="mesa-forma-preview-silla mesa-forma-preview-silla--arco" style="left:50%;top:8%;transform:translateX(-50%);"></span>'
                : '<span class="mesa-forma-preview-silla mesa-forma-preview-silla--n"></span>'
                    + '<span class="mesa-forma-preview-silla mesa-forma-preview-silla--s"></span>')
            + '<span class="mesa-forma-preview forma-' + f + '"></span></span>'
            + '<span class="mesa-forma-label">' + etiquetaFormaMesa(f) + '</span>'
            + '</button>';
    });
    html += '</div>';
    return html;
}

function seleccionarFormaMesaPicker(btn) {
    var $btn = $(btn);
    var forma = $btn.data('forma');
    var target = $btn.data('target') || 'detalle_forma';
    $('#' + target).val(forma);
    $btn.closest('.mesa-forma-picker').find('.mesa-forma-opt').removeClass('activa');
    $btn.addClass('activa');
    var mesaId = typeof mesaSeleccionadaId !== 'undefined' ? mesaSeleccionadaId : null;
    if (mesaId) {
        var $el = $('.plano-mesa[data-id="' + mesaId + '"], .pos-plano-mesa[data-mesa-id="' + mesaId + '"]');
        if ($el.length) {
            $el.removeClass('forma-rectangular forma-cuadrada forma-redonda')
                .addClass('forma-' + forma)
                .attr('data-forma', forma);
            var w = parseFloat(String($el[0].style.width).replace('%', '')) || 7;
            var h = parseFloat(String($el[0].style.height).replace('%', '')) || 7;
            if (esFormaCuadradaVisual(forma)) {
                $el.css({ height: 'auto' });
            } else if (!esFormaCuadradaVisual(forma) && $el[0].style.height === 'auto') {
                $el.css({ height: h + '%' });
            }
        }
    }
}

function construirClasesMesaPlano(m, extraClases) {
    var forma = getFormaMesa(m);
    var clases = ['plano-mesa', 'forma-' + forma];
    if (extraClases) {
        if (Array.isArray(extraClases)) {
            clases = clases.concat(extraClases);
        } else {
            clases.push(extraClases);
        }
    }
    return clases;
}

/** Lee tamaño real del elemento en % del canvas (para guardar sin deformar). */
function leerPorcentajesMesaEnCanvas($el, canvas) {
    var rect = canvas.getBoundingClientRect();
    var elRect = $el[0].getBoundingClientRect();
    return {
        x: ((elRect.left - rect.left) / rect.width) * 100,
        y: ((elRect.top - rect.top) / rect.height) * 100,
        w: (elRect.width / rect.width) * 100,
        h: (elRect.height / rect.height) * 100
    };
}

function aplicarTamanoMesaEnDom($el, forma, w, h) {
    forma = forma || getFormaMesa({ forma: $el.data('forma') });
    w = parseFloat(w) || 7;
    h = parseFloat(h) || 7;
    $el.attr('data-forma', forma);
    if (esFormaCuadradaVisual(forma)) {
        $el.css({ width: w + '%', height: 'auto' });
    } else {
        $el.css({ width: w + '%', height: h + '%' });
    }
}

function htmlAsaRedimensionMesa() {
    return '<span class="mesa-plano-handle mesa-plano-handle-se" data-handle="se"></span>';
}

function htmlControlesTamanoMesa(m) {
    var forma = getFormaMesa(m);
    var w = parseFloat(m.plano_ancho) || 7;
    var h = parseFloat(m.plano_alto) || 7;
    var tam = esFormaCuadradaVisual(forma) ? w : Math.round((w + h) / 2 * 10) / 10;
    var html = '<div class="mesa-tamano-controles border-top pt-2 mt-2">'
        + '<label class="small font-weight-bold d-block mb-1">Tamaño en el plano</label>';
    if (esFormaCuadradaVisual(forma)) {
        html += '<input type="range" class="custom-range mb-1" id="mesa_tamano_pct" min="4" max="20" step="0.5" value="' + tam + '"'
            + ' oninput="vistaPreviaTamanoMesa(' + m.id + ')">'
            + '<div class="d-flex justify-content-between small text-muted">'
            + '<span>4%</span><span id="mesa_tamano_val">' + tam + '%</span><span>20%</span></div>';
    } else {
        html += '<div class="form-row">'
            + '<div class="col-6"><label class="small mb-0">Ancho %</label>'
            + '<input type="number" class="form-control form-control-sm" id="mesa_ancho_pct" min="4" max="25" step="0.5" value="' + w.toFixed(1) + '" onchange="vistaPreviaTamanoMesa(' + m.id + ')"></div>'
            + '<div class="col-6"><label class="small mb-0">Alto %</label>'
            + '<input type="number" class="form-control form-control-sm" id="mesa_alto_pct" min="4" max="25" step="0.5" value="' + h.toFixed(1) + '" onchange="vistaPreviaTamanoMesa(' + m.id + ')"></div>'
            + '</div>';
    }
    html += '<p class="small text-muted mt-1 mb-0">Arrastre la esquina azul de la mesa o use los controles.</p></div>';
    return html;
}
