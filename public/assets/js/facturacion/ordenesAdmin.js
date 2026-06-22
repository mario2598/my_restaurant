var idOrdenAnular = 0;
var detallesAnular = [];
var ordenesGen = [];
$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-ordenes tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $("#input_buscar_generico3").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-detallesAnular tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    var currentDate = new Date();

    var year = currentDate.getFullYear();
    var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    var day = currentDate.getDate().toString().padStart(2, '0');
    var formattedDate = `${year}-${month}-${day}`;

    document.getElementById('desde').value = formattedDate;
    document.getElementById('hasta').value = formattedDate;

    filtrar();
});


function filtrar() {
    var filtro = {
        "desde": $('#desde').val(),
        "hasta": $('#hasta').val(),
        "sucursal": $('#select_sucursal').val(),
        "cliente": $('#select_cliente').val()
    }

    $.ajax({
        url: `${base_path}/facturacion/filtrarOrdenesAdmin`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            filtro: filtro
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        generarHTMLOrdenes(response['datos']);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function generarHTMLOrdenes(ordenes) {
    var texto = "";
    ordenesGen = ordenes;
    ordenes.forEach(orden => {
        var anulada   = orden.cod_general === 'ORD_ANULADA';
        var pagada    = !anulada && orden.pagado == 1;
        var pendiente = !anulada && !pagada;

        var estiloFila = anulada  ? 'background:#fff0f0;border-left:4px solid #e74c3c;'
                       : pagada   ? 'background:#f0fff0;border-left:4px solid #27ae60;'
                       :            'background:#fffbf0;border-left:4px solid #f39c12;';

        var badgePago = anulada  ? '<span class="badge badge-danger">Anulada</span>'
                      : pagada   ? '<span class="badge badge-success">Pagada</span>'
                      :            '<span class="badge badge-warning text-dark">Pendiente</span>';

        var incidentes = orden.incidentes || [];
        var tieneInc   = orden.tiene_incidentes || incidentes.length > 0;
        var celdaInc   = tieneInc
            ? `<td class="text-center">
                   <a href="javascript:void(0)"
                      onclick="verIncidentesOrden(${orden.id},'${(orden.numero_orden||'').toString().replace(/'/g,"\\'")}')"
                      class="text-warning font-weight-bold" title="Ver incidentes">
                       <i class="fas fa-exclamation-triangle"></i> ${incidentes.length}
                   </a>
               </td>`
            : `<td class="text-center text-muted">—</td>`;

        var acciones = `<button class="btn btn-sm btn-outline-primary mr-1 mb-1"
                                onclick="imprimirTicket(${orden.id})"
                                title="Imprimir tiquete">
                            <i class="fas fa-print"></i>
                        </button>`;


        if (pendiente) {
            acciones += `<button class="btn btn-sm btn-outline-info mr-1 mb-1"
                                 onclick="abrirModalCambiarCaja(${orden.id},'${(orden.numero_orden||orden.id).toString().replace(/'/g,"\\'")}')"
                                 title="Cambiar caja">
                             <i class="fas fa-cash-register"></i>
                         </button>`;
        }

        if (!anulada) {
            acciones += `<button class="btn btn-sm btn-outline-danger mb-1"
                                 onclick='abrirModalAnularOrden("${orden.id??0}")'
                                 title="Anular orden">
                             <i class="fas fa-ban"></i>
                         </button>`;
        }

        texto += `<tr style="${estiloFila} border-bottom:1px solid #dee2e6;">
            <td class="text-center font-weight-bold">${orden.numero_orden}</td>
            <td class="text-center">${orden.nombreSucursal??''}</td>
            <td class="text-center small">${orden.fecha_inicio??''}</td>
            <td class="text-center">${orden.nombre_cliente??''}</td>
            <td class="text-center font-weight-bold">
                ${anulada ? '<span class="text-muted">—</span>' : (orden.total_con_descuento??0)}
            </td>
            <td class="text-center">${badgePago}</td>
            <td class="text-center small" title="${(orden.caja_etiqueta||'').replace(/"/g,'&quot;')}">
                ${orden.caja_etiqueta||'—'}
            </td>
            ${celdaInc}
            <td class="text-center" style="white-space:nowrap;">${acciones}</td>
        </tr>`;
    });

    $('#tbody-ordenes').html(texto);
}


function verIncidentesOrden(idOrden, numeroOrden) {
    var orden = ordenesGen.find(function (o) { return o.id == idOrden; });
    if (!orden || !orden.incidentes || orden.incidentes.length === 0) {
        return;
    }
    var incidentes = orden.incidentes;
    $('#mdl-incidentes-orden-numero').text('Orden #' + (numeroOrden || idOrden));
    var filas = '';
    var totalRebaja = 0;
    incidentes.forEach(function (inc) {
        var monto = parseFloat(inc.monto_afectado) || 0;
        totalRebaja += monto;
        var fecha = (inc.fecha || '').substring(0, 19).replace('T', ' ');
        var usuario = (inc.usuario_nombre || inc.usuario_login || '—');
        var desc = (inc.descripcion || '').substring(0, 500);
        filas += '<tr><td>' + fecha + '</td><td>' + usuario + '</td><td>' + desc + '</td><td class="text-right">' + (monto).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' }) + '</td></tr>';
    });
    $('#tbody-incidentes-orden').html(filas);
    $('#mdl-incidentes-total-rebaja').html('<strong>Total a rebajar:</strong> ' + (totalRebaja).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' }));
    $('#mdl-incidentes-orden').modal('show');
}


function imprimirTicket(id) {
    var modo = (typeof ticketModo !== 'undefined') ? ticketModo : 'html';
    if (modo === 'qz' && typeof qz !== 'undefined') {
        _qzPrint(id);
        return;
    }
    var url = base_path + '/impresora/tiquete/html/' + id;
    window.open(url, '_blank', 'width=380,height=600,scrollbars=yes');
}

function _qzPrint(id) {
    var pdfUrl  = base_path + '/impresora/tiquete/' + id;
    var ancho   = (typeof ticketAncho !== 'undefined' && parseInt(ticketAncho) > 0) ? parseInt(ticketAncho) : 80;
    var printer = (typeof ticketImpresora !== 'undefined') ? ticketImpresora : '';
    try {
    qz.security.setCertificatePromise(function(resolve, reject) {
        fetch(base_path + '/qz-cert').then(function(r) { return r.text(); })
            .then(resolve).catch(reject);
    });
    qz.security.setSignatureAlgorithm('SHA512');
    qz.security.setSignaturePromise(function(toSign) {
        return function(resolve, reject) {
            fetch(base_path + '/qz-sign?request=' + encodeURIComponent(toSign))
                .then(function(r) { return r.text(); }).then(resolve).catch(reject);
        };
    });
    var doConnect = (qz.websocket.isActive && qz.websocket.isActive())
        ? Promise.resolve()
        : qz.websocket.connect({ retries: 1, delay: 1 });
    doConnect.then(function() {
        return qz.printers.find(printer);
    }).then(function(p) {
        var cfg = qz.configs.create(p, {
            size: { width: ancho, units: 'mm' }, margins: 0,
            colorType: 'blackwhite', interpolation: 'bicubic', scaleContent: false
        });
        return qz.print(cfg, [{ type: 'pixel', format: 'pdf', flavor: 'file', data: pdfUrl }]);
    }).then(function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon:'success', title:'Imprimiendo...', timer:2500,
                showConfirmButton:false, toast:true, position:'top-end' });
        }
    }).catch(function(err) {
        var msg = (err && err.message) ? err.message : String(err);
        var m = msg.toLowerCase();
        var titulo = (m.indexOf('unable to establish') !== -1 || m.indexOf('websocket') !== -1
            || m.indexOf('connection') !== -1 || m.indexOf('failed to connect') !== -1)
            ? 'QZ Tray no esta abierto'
            : (m.indexOf('no printer') !== -1 || m.indexOf('not found') !== -1
               ? 'Impresora "' + printer + '" no encontrada' : 'Error QZ Tray');
        window.open(base_path + '/impresora/tiquete/html/' + id,
                    '_blank', 'width=380,height=600,scrollbars=yes');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon:'warning', title: titulo,
                text: 'El tiquete se abrio en una ventana. ' + msg,
                confirmButtonText:'Entendido', confirmButtonColor:'#4e73df' });
        }
    });
    } catch(e) {
        console.warn('QZ Tray no disponible:', e.message || e);
        window.open(base_path + '/impresora/tiquete/html/' + id, '_blank', 'width=380,height=600,scrollbars=yes');
    }
}


function abrirModalAnularOrden(idOrden) {
    idOrdenAnular = idOrden;
    var tablaDetalles = "";
    ordenesGen.forEach(orden => {
        if (orden.id == idOrden) {
            orden.detalles.forEach(detalle => {
                tablaDetalles = tablaDetalles + `<tr style='border-bottom: 1px solid grey;'>
                                                    <td class="text-center" >
                                                        ${detalle.nombre_producto}
                                                    </td> 
                                                    <td class="text-center">
                                                        ${detalle.cantidad}
                                                    </td>
                                                    <td class="text-center">
                                                        ${detalle.total ?? 0}
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" id="elemento${detalle.id ?? 0}" class="elemento" value="${detalle.id ?? 0}"> 
                                                    </td>
                                                </tr>`;
            });
        }

    });
    $('#mdl-detallesAnular').modal('show');
    $('#tbody-detallesAnular').html(tablaDetalles);
}

function cerrarMdlAnular() {
    $('#mdl-detallesAnular').modal('hide');
}

function abrirModalCambiarCaja(idOrden, numeroOrden) {
    $('#mdl-cambiar-caja-id-orden').val(idOrden);
    $('#mdl-cambiar-caja-orden-numero').text('Orden #' + (numeroOrden || idOrden));
    var orden = ordenesGen.find(function (o) { return o.id == idOrden; });
    var idSucursal = orden && orden.sucursal ? orden.sucursal : '';
    $('#select-caja-destino').html('<option value="">-- Cargando... --</option>');
    $('#mdl-cambiar-caja').modal('show');

    $.ajax({
        url: base_path + '/facturacion/cajasAbiertas',
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            sucursal: idSucursal
        }
    }).done(function (response) {
        if (!response.estado) {
            showError(response.mensaje || 'Error al cargar cajas');
            return;
        }
        var opciones = '<option value="">-- Seleccione una caja --</option>';
        (response.datos || []).forEach(function (c) {
            opciones += '<option value="' + c.id + '">' + (c.etiqueta || 'Caja #' + c.id) + '</option>';
        });
        $('#select-caja-destino').html(opciones);
    }).fail(function () {
        showError('No se pudieron cargar las cajas abiertas');
    });
}

$(document).on('click', '#btn-guardar-cambiar-caja', function () {
    var idOrden = $('#mdl-cambiar-caja-id-orden').val();
    var idCaja = $('#select-caja-destino').val();
    if (!idOrden || !idCaja) {
        showError('Seleccione una caja destino');
        return;
    }
    $('#loader').fadeIn();
    $.ajax({
        url: base_path + '/facturacion/orden/cambiarCaja',
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            idOrden: idOrden,
            idCaja: idCaja
        }
    }).done(function (response) {
        if (!response.estado) {
            showError(response.mensaje || 'Error al cambiar la caja');
            return;
        }
        $('#mdl-cambiar-caja').modal('hide');
        showSuccess(response.mensaje || 'Caja actualizada');
        filtrar();
    }).fail(function () {
        showError('Algo salió mal');
    }).always(function () {
        $('#loader').fadeOut();
    });
});

function anularOrden() {
    if (idOrdenAnular == null || idOrdenAnular == 0) {
        showError("No existe la orden");
        return;
    }
    var checkboxes = document.querySelectorAll('.elemento');
    var detallesAnular = "";
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            detallesAnular = detallesAnular + checkboxes[i].value + ",";
        }
    }
    detallesAnular = detallesAnular.slice(0, -1);
    var arrayDeCadenas = detallesAnular.split(',');
    var detallesAux = arrayDeCadenas.map(function (numero) {
        return parseInt(numero, 10);
    });
    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/facturacion/pos/anularOrden`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idOrden: idOrdenAnular,
            lineas: detallesAux
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        cerrarMdlAnular();
        showSuccess("Se anulo correctamente la orden");
        filtrar();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    }).always(function () {
        $('#loader').fadeOut();
    });
}
