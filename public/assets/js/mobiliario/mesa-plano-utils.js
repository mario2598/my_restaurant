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

function etiquetaFormaMesa(forma) {
    if (forma === 'redonda') return 'Redonda';
    if (forma === 'cuadrada') return 'Cuadrada';
    return 'Rectangular';
}

function htmlContenidoMesaPlano(numero, capacidad, extraHtml) {
    return '<span class="mesa-plano-superficie" aria-hidden="true"></span>'
        + '<span class="plano-mesa-numero">' + numero + '</span>'
        + '<span class="plano-mesa-cap">' + (capacidad || 0) + ' p.</span>'
        + (extraHtml || '');
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
            + '<span class="mesa-forma-preview forma-' + f + '"></span>'
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
