var idOrdenAnular = 0;
var idInfoFeGen = 0;
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
        "sucursal": $('#select_sucursal').val()
    }

    $.ajax({
        url: `${base_path}/fe/filtrarFacturas`,
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
        var lineas = "";
        var tablaDetalles = "";
        texto = texto +
            `<tr style="border-bottom: 1px solid grey;">
                <td class="text-center" onclick="imprimirTicket( ${orden.id})" style="cursor:pointer; text-decoration : underline; ">
                    ${orden.numero_orden}
                </td> 
                <td class="text-center">
                ${orden.nombreSucursal}
            </td>
                <td class="text-center">
                    ${orden.fechaFormat}
                </td>
                <td class="text-center">
                    ${orden.cedFe ?? ""}
                </td>
                <td class="text-center">
                ${orden.nombreFe ?? 0}
            </td> 
            <td class="text-center">
            ${orden.correoFe ?? 0}
        </td> 
        <td class="text-center">
        ${orden.comprobanteFe ?? "PENDIENTE ENVÍAR"}
    </td>
            <td class="text-center">
            ${orden.estadoFe ?? ""}
        </td>
        <td class="text-center">
            <span class="badge ${orden.estadoHaciendaCod == 'HACIENDA_ACEPTADO' ? 'badge-success' : orden.estadoHaciendaCod == 'HACIENDA_RECHAZADO' ? 'badge-danger' : 'badge-warning'}">
                ${orden.estadoHaciendaNombre ?? 'N/A'}
            </span>
        </td>
        <td class="text-center" style="white-space: nowrap;">`;
        
        // Mostrar botones si está pendiente O si está rechazado (para poder reenviar)
        const esPendiente = orden.cod_general == 'FE_ORDEN_PEND';
        const esRechazado = orden.estadoHaciendaCod == 'HACIENDA_RECHAZADO';
        const esAceptado = orden.estadoHaciendaCod == 'HACIENDA_ACEPTADO';
        
        // Grupo 1: Botones principales de envío
        if (esPendiente || esRechazado) {
            const textoBoton = esRechazado ? 'Reenviar' : 'Enviar';
            const colorBoton = esRechazado ? 'btn-warning' : 'btn-primary';
            
            texto = texto + `
            <div class="btn-group mb-1" role="group">
                <button type="button" class="btn ${colorBoton} btn-sm"
                 onclick='enviarFacturaHacienda("${orden.id ?? 0}","${orden.idFe ?? 0}")' 
                    title="${textoBoton} Factura" data-toggle="tooltip">
                    <i class="fas fa-file-invoice"></i> Factura
                </button>
                <button type="button" class="btn ${colorBoton} btn-sm"
                 onclick='enviarComprobanteHacienda("${orden.id ?? 0}","${orden.idFe ?? 0}")' 
                    title="${textoBoton} Comprobante" data-toggle="tooltip">
                    <i class="fas fa-paper-plane"></i> Comprobante
                </button>
            </div>`;
        }
        
        // Grupo 2: Botones secundarios (con separación)
        if (esPendiente || esRechazado) {
            texto = texto + `<br>`;
            texto = texto + `
            <button type="button" class="btn btn-info btn-sm mb-1"
             onclick='verJsonComprobante("${orden.id ?? 0}","${orden.idFe ?? 0}")' 
                title="Ver JSON" data-toggle="tooltip">
                <i class="fas fa-eye"></i> JSON
            </button>`;
            
            // Solo mostrar botón "Manual" si está pendiente (no rechazado)
            if (esPendiente) {
                texto = texto + `
                <button type="button" class="btn btn-success btn-sm mb-1"
                 onclick='abrirModalEnvia("${orden.id ?? 0}","${orden.idFe ?? 0}")' 
                    title="Marcar manual" data-toggle="tooltip">
                    <i class="fas fa-check"></i> Manual
                </button>`;
            }
        }
        
        // Botón para asignar/cambiar cliente (solo visible si los botones de envío están visibles)
        if ((esPendiente || esRechazado) && orden.idFe && orden.idFe != 0) {
            const tieneCliente = orden.cedFe && orden.cedFe.trim() !== '' && 
                                 orden.nombreFe && orden.nombreFe.trim() !== '' && 
                                 orden.correoFe && orden.correoFe.trim() !== '';
            const textoBotonCliente = tieneCliente ? 'Cambiar Cliente' : 'Asignar Cliente';
            const iconoCliente = tieneCliente ? 'fa-user-edit' : 'fa-user-plus';
            
            texto = texto + `<br>`;
            texto = texto + `
            <button type="button" class="btn btn-outline-primary btn-sm mb-1"
             onclick='abrirModalAsignarCliente("${orden.id ?? 0}","${orden.idFe ?? 0}")' 
                title="${textoBotonCliente}" data-toggle="tooltip">
                <i class="fas ${iconoCliente}"></i> ${textoBotonCliente}
            </button>`;
        }
        
        // Grupo 3: Botones de estado (con separación)
        if ((esAceptado && orden.comprobanteFe && orden.comprobanteFe != '') || 
            (orden.url_consulta_estado && orden.url_consulta_estado != '')) {
            texto = texto + `<br>`;
        }
        
        // Botón para reenviar correo (solo si está aceptado por Hacienda y tiene clave de comprobante)
        if (esAceptado && orden.comprobanteFe && orden.comprobanteFe != '') {
            texto = texto + `
            <button type="button" class="btn btn-secondary btn-sm mb-1"
             onclick='reenviarCorreoFactuX("${orden.idFe ?? 0}","${orden.correoFe ?? ''}")' 
                title="Reenviar correo" data-toggle="tooltip">
                <i class="fas fa-envelope"></i> Correo
            </button>`;
        }
        
        // Botón para consultar estado de Hacienda (si tiene URL)
        if (orden.url_consulta_estado && orden.url_consulta_estado != '') {
            texto = texto + `
            <button type="button" class="btn btn-warning btn-sm mb-1"
             onclick='consultarEstadoHacienda("${orden.idFe ?? 0}","${orden.url_consulta_estado}")' 
                title="Consultar estado" data-toggle="tooltip">
                <i class="fas fa-sync-alt"></i> Consultar
            </button>`;
        }
        
        texto = texto + `
        </td> `;

        texto = texto + `</tr>`;
    });

    $('#tbody-ordenes').html(texto);
    
    // Inicializar tooltips de Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
}


function imprimirTicket(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}


function abrirModalEnvia(idOrden, idInfoFe) {

    idOrdenAnular = idOrden;
    idInfoFeGen = idInfoFe;
    $('#num_comprobante').val("");
    $('#mdl-envia').modal('show');
}

function cerrarMdlEnvia() {
    $('#mdl-envia').modal('hide');
}

function enviarOrden() {
    if (idOrdenAnular == null || idOrdenAnular == 0) {
        showError("No existe la orden");
        return;
    }
    var numComprobante = $('#num_comprobante').val();

    $.ajax({
        url: `${base_path}/fe/enviarFe`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idOrden: idOrdenAnular,
            idInfoFe: idInfoFeGen,
            numComprobante: numComprobante
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        cerrarMdlEnvia();
        showSuccess("Se marcó como envíada la factura");
        filtrar();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

// Variables globales para el flujo de actualización de cliente
var idInfoFePendiente = null;
var idOrdenPendiente = null;
var tipoEnvioPendiente = null; // 'factura' o 'comprobante'

/**
 * Envía la factura electrónica a Hacienda (método V2 - con cliente, formato nuevo FactuX)
 */
function enviarFacturaHacienda(idOrden, idInfoFe) {
    if (idInfoFe == null || idInfoFe == 0) {
        showError("ID de factura inválido");
        return;
    }

    // Verificar si es un reenvío (buscar en ordenesGen)
    const orden = ordenesGen.find(o => o.idFe == idInfoFe);
    
    // Verificar si tiene cliente (cedula, nombre, correo)
    const tieneCliente = orden && orden.cedFe && orden.cedFe.trim() !== '' && 
                         orden.nombreFe && orden.nombreFe.trim() !== '' && 
                         orden.correoFe && orden.correoFe.trim() !== '';
    
    // Si no tiene cliente, mostrar error
    if (!tieneCliente) {
        showError("Esta factura no tiene cliente asignado. Por favor, use el botón 'Asignar Cliente' primero.");
        return;
    }
    
    // Si tiene cliente, proceder con el envío normal
    procederConEnvioFactura(idOrden, idInfoFe, orden);
}

/**
 * Procede con el envío de la factura después de verificar/actualizar cliente
 */
function procederConEnvioFactura(idOrden, idInfoFe, orden) {
    const esReenvio = orden && orden.estadoHaciendaCod == 'HACIENDA_RECHAZADO';
    const titulo = esReenvio ? '¿Reenviar Factura a Hacienda?' : '¿Enviar Factura a Hacienda?';
    const texto = esReenvio 
        ? "Se generará y reenviará la factura electrónica con datos del cliente (formato nuevo FactuX). Asegúrese de haber corregido los errores antes de reenviar."
        : "Se generará y enviará la factura electrónica con datos del cliente (formato nuevo FactuX)";
    const textoBoton = esReenvio ? 'Sí, reenviar' : 'Sí, enviar';
    const textoProceso = esReenvio ? 'Reenviando...' : 'Enviando...';

    swal({
        title: titulo,
        text: texto,
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                closeModal: true
            },
            confirm: {
                text: textoBoton,
                value: true,
                visible: true,
                closeModal: false
            }
        },
        dangerMode: true
    }).then((confirmado) => {
        if (confirmado) {
            swal({
                title: textoProceso,
                text: 'Por favor espere mientras se procesa la factura',
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false
            });

            $.ajax({
                url: `${base_path}/fe/enviarFacturaHacienda`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    idInfoFe: idInfoFe
                }
            }).done(function (response) {
                swal.close();
                
                if (!response['estado']) {
                    if (response['datos'] && Array.isArray(response['datos'])) {
                        var container = document.createElement('div');
                        var mensaje = document.createElement('p');
                        mensaje.innerText = response['mensaje'];
                        container.appendChild(mensaje);

                        var lista = document.createElement('ul');
                        lista.style.textAlign = 'left';
                        response['datos'].forEach(function (prod) {
                            var li = document.createElement('li');
                            li.innerHTML = `<strong>${prod.nombre}</strong> (${prod.codigo}): ${prod.motivo}`;
                            lista.appendChild(li);
                        });
                        container.appendChild(lista);

                        swal({
                            title: 'Configuración incompleta',
                            content: container,
                            icon: 'warning',
                            buttons: {
                                confirm: {
                                    text: 'Entendido',
                                    value: true
                                }
                            }
                        });
                    } else {
                        showError(response['mensaje']);
                    }
                    return;
                }
                
                const mensajeExito = esReenvio 
                    ? 'La factura se reenvió exitosamente a FactuX/Hacienda'
                    : 'La factura se envió exitosamente a FactuX/Hacienda';
                const tituloExito = esReenvio ? '¡Factura reenviada!' : '¡Factura enviada!';
                
                var mensajeCompleto = mensajeExito;
                if (response['datos'] && response['datos'].clave) {
                    mensajeCompleto += `\nClave: ${response['datos'].clave}`;
                }
                if (response['datos'] && response['datos'].urlConsultaEstado) {
                    mensajeCompleto += `\n\nPuede consultar el estado usando el botón "Consultar"`;
                }

                swal({
                    title: tituloExito,
                    text: mensajeCompleto,
                    icon: 'success',
                    buttons: 'Aceptar'
                }).then(() => {
                    filtrar();
                });
                
            }).fail(function () {
                swal.close();
                showError("Error al comunicarse con el servidor");
            });
        }
    });
}

/**
 * Envía el comprobante electrónico a Hacienda (método original - sin cliente)
 */
function enviarComprobanteHacienda(idOrden, idInfoFe) {
    if (idInfoFe == null || idInfoFe == 0) {
        showError("ID de factura inválido");
        return;
    }

    // Verificar si es un reenvío (buscar en ordenesGen)
    const orden = ordenesGen.find(o => o.idFe == idInfoFe);
    
    // Proceder directamente con el envío (puede tener o no cliente)
    procederConEnvioComprobante(idOrden, idInfoFe, orden);
}

/**
 * Procede con el envío del comprobante
 */
function procederConEnvioComprobante(idOrden, idInfoFe, orden) {
    const esReenvio = orden && orden.estadoHaciendaCod == 'HACIENDA_RECHAZADO';
    const titulo = esReenvio ? '¿Reenviar Comprobante a Hacienda?' : '¿Enviar Comprobante a Hacienda?';
    const texto = esReenvio 
        ? "Se generará y reenviará el comprobante electrónico. Asegúrese de haber corregido los errores antes de reenviar."
        : "Se generará y enviará el comprobante electrónico";
    const textoBoton = esReenvio ? 'Sí, reenviar' : 'Sí, enviar';
    const textoProceso = esReenvio ? 'Reenviando...' : 'Enviando...';

    swal({
        title: titulo,
        text: texto,
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                closeModal: true
            },
            confirm: {
                text: textoBoton,
                value: true,
                visible: true,
                closeModal: false
            }
        },
        dangerMode: true
    }).then((confirmado) => {
        if (confirmado) {
            swal({
                title: textoProceso,
                text: 'Por favor espere mientras se procesa el comprobante',
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false
            });

            $.ajax({
                url: `${base_path}/fe/enviarComprobanteHacienda`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    idInfoFe: idInfoFe
                }
            }).done(function (response) {
                swal.close();
                
                if (!response['estado']) {
                    if (response['datos'] && Array.isArray(response['datos'])) {
                        var container = document.createElement('div');
                        var mensaje = document.createElement('p');
                        mensaje.innerText = response['mensaje'];
                        container.appendChild(mensaje);

                        var lista = document.createElement('ul');
                        lista.style.textAlign = 'left';
                        response['datos'].forEach(function (prod) {
                            var li = document.createElement('li');
                            li.innerHTML = `<strong>${prod.nombre}</strong> (${prod.codigo}): ${prod.motivo}`;
                            lista.appendChild(li);
                        });
                        container.appendChild(lista);

                        swal({
                            title: 'Configuración incompleta',
                            content: container,
                            icon: 'warning',
                            buttons: {
                                confirm: {
                                    text: 'Entendido',
                                    value: true
                                }
                            }
                        });
                    } else {
                        showError(response['mensaje']);
                    }
                    return;
                }
                
                const mensajeExito = esReenvio 
                    ? 'El comprobante se reenvió exitosamente a FactuX/Hacienda'
                    : 'El comprobante se envió exitosamente a FactuX/Hacienda';
                const tituloExito = esReenvio ? '¡Comprobante reenviado!' : '¡Comprobante enviado!';
                
                var mensajeCompleto = mensajeExito;
                if (response['datos'] && response['datos'].clave) {
                    mensajeCompleto += `\nClave: ${response['datos'].clave}`;
                }
                if (response['datos'] && response['datos'].urlConsultaEstado) {
                    mensajeCompleto += `\n\nPuede consultar el estado usando el botón "Consultar"`;
                }

                swal({
                    title: tituloExito,
                    text: mensajeCompleto,
                    icon: 'success',
                    buttons: 'Aceptar'
                }).then(() => {
                    filtrar();
                });
                
            }).fail(function () {
                swal.close();
                showError("Error al comunicarse con el servidor");
            });
        }
    });
}

/**
 * Muestra el JSON del comprobante electrónico (para debugging)
 */
function verJsonComprobante(idOrden, idInfoFe) {
    if (idInfoFe == null || idInfoFe == 0) {
        showError("ID de factura inválido");
        return;
    }

    swal({
        title: 'Cargando...',
        text: 'Generando JSON del comprobante',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });

    $.ajax({
        url: `${base_path}/fe/obtenerJsonComprobante`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idInfoFe: idInfoFe
        }
    }).done(function (response) {
        swal.close();
        
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        
        // Mostrar el JSON formateado
        const jsonPretty = JSON.stringify(response['datos'], null, 2);
        var container = document.createElement('div');

        var scroll = document.createElement('div');
        scroll.style.maxHeight = '500px';
        scroll.style.overflowY = 'auto';

        var pre = document.createElement('pre');
        pre.style.textAlign = 'left';
        pre.style.fontSize = '12px';
        pre.style.background = '#f4f4f4';
        pre.style.padding = '15px';
        pre.style.borderRadius = '5px';
        pre.textContent = jsonPretty;

        scroll.appendChild(pre);
        container.appendChild(scroll);

        var copyBtn = document.createElement('button');
        copyBtn.className = 'btn btn-sm btn-secondary mt-2';
        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copiar JSON';
        copyBtn.addEventListener('click', copiarJsonAlPortapapeles);
        container.appendChild(copyBtn);

        swal({
            title: 'JSON del Comprobante Electrónico',
            content: container,
            buttons: {
                confirm: {
                    text: 'Cerrar',
                    value: true
                }
            }
        });
        
        // Guardar el JSON temporalmente para poder copiarlo
        window.jsonComprobanteTemp = jsonPretty;
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        swal.close();
        showError("Error al generar el JSON");
    });
}

/**
 * Copia el JSON al portapapeles
 */
function copiarJsonAlPortapapeles() {
    if (window.jsonComprobanteTemp) {
        navigator.clipboard.writeText(window.jsonComprobanteTemp).then(() => {
            showSuccess("JSON copiado al portapapeles");
        }).catch(err => {
            showError("No se pudo copiar al portapapeles");
        });
    }
}

/**
 * Consulta el estado del comprobante en Hacienda usando la URL guardada
 */
/**
 * Reenvía el correo del comprobante electrónico usando FactuX
 */
function reenviarCorreoFactuX(idInfoFe, correoActual) {
    if (idInfoFe == null || idInfoFe == 0) {
        showError("ID de factura inválido");
        return;
    }

    // Si hay correo actual, preguntar si quiere usarlo o agregar más
    let correosIniciales = correoActual && correoActual.trim() != '' ? correoActual.trim() : '';
    
    swal({
        title: '¿Reenviar correo del comprobante?',
        text: 'Ingrese los correos a los que desea reenviar el comprobante (separados por comas)',
        content: {
            element: "input",
            attributes: {
                placeholder: "correo1@example.com, correo2@example.com",
                type: "text",
                value: correosIniciales
            },
        },
        icon: 'info',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                closeModal: true
            },
            confirm: {
                text: 'Reenviar',
                value: true,
                visible: true,
                closeModal: false
            }
        }
    }).then((resultado) => {
        // En SweetAlert2, cuando usas content con input, el valor puede venir de diferentes formas
        if (resultado) {
            // Intentar obtener el valor del input de diferentes formas
            let correosInput = '';
            
            if (typeof resultado === 'string') {
                correosInput = resultado;
            } else if (resultado && resultado.value) {
                correosInput = resultado.value;
            } else {
                // Si no viene en el resultado, intentar obtenerlo del DOM
                const inputElement = document.querySelector('.swal2-input');
                if (inputElement) {
                    correosInput = inputElement.value || '';
                }
            }
            
            if (!correosInput || correosInput.trim() === '') {
                showError("Debe ingresar al menos un correo");
                return;
            }

            // Separar correos por comas y limpiar espacios
            const correos = correosInput.split(',')
                .map(c => c.trim())
                .filter(c => c !== '');

            if (correos.length === 0) {
                showError("Debe ingresar al menos un correo válido");
                return;
            }

            swal({
                title: 'Reenviando correo...',
                text: 'Por favor espere mientras se reenvía el correo',
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false
            });

            $.ajax({
                url: `${base_path}/fe/reenviarCorreoFactuX`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    idInfoFe: idInfoFe,
                    remitentes: correos
                }
            }).done(function (response) {
                swal.close();
                
                if (!response['estado']) {
                    showError(response['mensaje']);
                    return;
                }
                
                showSuccess(response['mensaje'] || "Correo reenviado exitosamente");
            }).fail(function (jqXHR, textStatus, errorThrown) {
                swal.close();
                showError("Error al reenviar el correo");
            });
        }
    });
}

function consultarEstadoHacienda(idInfoFe, urlConsulta) {
    if (!idInfoFe || idInfoFe == 0) {
        showError("ID de factura inválido");
        return;
    }

    if (!urlConsulta || urlConsulta == '') {
        showError("No hay URL de consulta disponible");
        return;
    }

    swal({
        title: 'Consultando...',
        text: 'Consultando el estado del comprobante en Hacienda',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });

    $.ajax({
        url: `${base_path}/fe/consultarEstadoHacienda`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idInfoFe: idInfoFe,
            urlConsulta: urlConsulta
        }
    }).done(function (response) {
        swal.close();
        
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        
        // Mostrar la respuesta completa en pantalla para mapearla
        const respuestaData = response['datos'] || {};
        const respuestaInterna = respuestaData['respuestaInterna'] || respuestaData['respuesta'] || {};
        const estadoActual = respuestaData['estadoActual'] || respuestaInterna['estadoActual'] || respuestaData['estadoHacienda'] || respuestaInterna['estadoHacienda'] || respuestaData['estado'] || 'N/A';
        const estadoHaciendaNombre = respuestaData['estadoHacienda'] || respuestaInterna['estadoHacienda'] || estadoActual;
        const mensajeRespuestaHacienda = respuestaData['mensajeRespuestaHacienda'] || respuestaInterna['mensajeRespuestaHacienda'] || '';
        const claveComprobante = respuestaData['clave'] || respuestaInterna['clave'] || '';
        
        const jsonPretty = JSON.stringify(respuestaData, null, 2);
        
        var container = document.createElement('div');
        
        // Función para extraer errores del XML de Hacienda
        function extraerErroresDelXML(xmlString) {
            if (!xmlString || xmlString.trim() === '') return [];
            
            try {
                // Crear un parser de XML
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(xmlString, 'text/xml');
                
                // Buscar el elemento DetalleMensaje
                const detalleMensaje = xmlDoc.getElementsByTagName('DetalleMensaje')[0];
                if (!detalleMensaje) return [];
                
                const textoDetalle = detalleMensaje.textContent || detalleMensaje.innerText || '';
                
                // Extraer errores del formato: codigo, mensaje, fila, columna
                const errores = [];
                const lineas = textoDetalle.split('\n');
                
                let enSeccionErrores = false;
                for (let i = 0; i < lineas.length; i++) {
                    const linea = lineas[i].trim();
                    
                    // Detectar inicio de sección de errores
                    if (linea.includes('codigo, mensaje, fila, columna')) {
                        enSeccionErrores = true;
                        continue;
                    }
                    
                    // Detectar fin de sección de errores
                    if (enSeccionErrores && (linea === ']' || linea.startsWith(']'))) {
                        break;
                    }
                    
                    // Procesar líneas de error
                    if (enSeccionErrores && linea && !linea.includes('codigo, mensaje')) {
                        // Formato esperado: -53, "mensaje", 0, 0
                        const match = linea.match(/(-?\d+),\s*"([^"]+)",\s*(\d+),\s*(\d+)/);
                        if (match) {
                            errores.push({
                                codigo: match[1],
                                mensaje: match[2].replace(/&#13;/g, '').trim(),
                                fila: match[3],
                                columna: match[4]
                            });
                        }
                    }
                }
                
                return errores;
            } catch (e) {
                console.error('Error al parsear XML:', e);
                return [];
            }
        }
        
        // Extraer errores del mensaje de Hacienda
        const errores = extraerErroresDelXML(mensajeRespuestaHacienda);
        
        // Mostrar el estado actual de forma destacada
        var estadoDiv = document.createElement('div');
        const esRechazado = estadoActual.toUpperCase() === 'RECHAZADO' || estadoHaciendaNombre.toLowerCase() === 'rechazado';
        const esAceptado = estadoActual.toUpperCase() === 'ACEPTADO' || estadoHaciendaNombre.toLowerCase() === 'aceptado';
        
        if (esRechazado) {
            estadoDiv.className = 'alert alert-danger mb-3';
        } else if (esAceptado) {
            estadoDiv.className = 'alert alert-success mb-3';
        } else {
            estadoDiv.className = 'alert alert-warning mb-3';
        }
        
        estadoDiv.style.textAlign = 'center';
        estadoDiv.innerHTML = `<strong>Estado actual:</strong> <span class="badge ${esRechazado ? 'badge-danger' : esAceptado ? 'badge-success' : 'badge-warning'}">${estadoHaciendaNombre}</span>`;
        container.appendChild(estadoDiv);
        
        // Mostrar clave del comprobante si existe
        if (claveComprobante) {
            var claveDiv = document.createElement('div');
            claveDiv.className = 'alert alert-secondary mb-3';
            claveDiv.style.fontSize = '12px';
            claveDiv.innerHTML = `<strong>Clave del comprobante:</strong><br><code style="font-size: 11px;">${claveComprobante}</code>`;
            container.appendChild(claveDiv);
        }
        
        // Mostrar errores de forma clara si existen
        if (errores.length > 0) {
            var erroresDiv = document.createElement('div');
            erroresDiv.className = 'alert alert-danger mb-3';
            erroresDiv.innerHTML = '<h5><i class="fas fa-exclamation-triangle"></i> Errores encontrados:</h5>';
            
            var erroresList = document.createElement('ul');
            erroresList.style.textAlign = 'left';
            erroresList.style.marginTop = '10px';
            
            errores.forEach(function(error) {
                var li = document.createElement('li');
                li.style.marginBottom = '10px';
                li.innerHTML = `
                    <strong>Código ${error.codigo}:</strong> ${error.mensaje}
                    ${error.fila !== '0' || error.columna !== '0' ? `<br><small class="text-muted">Fila: ${error.fila}, Columna: ${error.columna}</small>` : ''}
                `;
                erroresList.appendChild(li);
            });
            
            erroresDiv.appendChild(erroresList);
            container.appendChild(erroresDiv);
        } else if (mensajeRespuestaHacienda && mensajeRespuestaHacienda.includes('DetalleMensaje')) {
            // Si hay mensaje pero no se pudieron extraer errores, mostrar el mensaje completo
            var mensajeDiv = document.createElement('div');
            mensajeDiv.className = 'alert alert-info mb-3';
            mensajeDiv.innerHTML = '<h5><i class="fas fa-info-circle"></i> Mensaje de Hacienda:</h5>';
            
            try {
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(mensajeRespuestaHacienda, 'text/xml');
                const detalleMensaje = xmlDoc.getElementsByTagName('DetalleMensaje')[0];
                if (detalleMensaje) {
                    const texto = detalleMensaje.textContent || detalleMensaje.innerText || '';
                    const textoLimpio = texto.replace(/&#13;/g, '').replace(/\n\n/g, '\n').trim();
                    mensajeDiv.innerHTML += '<pre style="white-space: pre-wrap; font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 5px;">' + textoLimpio + '</pre>';
                }
            } catch (e) {
                mensajeDiv.innerHTML += '<p style="font-size: 12px;">' + mensajeRespuestaHacienda.substring(0, 500) + '...</p>';
            }
            
            container.appendChild(mensajeDiv);
        }
        
        // Sección para JSON completo (colapsable)
        var jsonSection = document.createElement('div');
        jsonSection.className = 'mb-3';
        
        var jsonHeader = document.createElement('div');
        jsonHeader.className = 'd-flex justify-content-between align-items-center';
        jsonHeader.style.cursor = 'pointer';
        jsonHeader.style.padding = '10px';
        jsonHeader.style.background = '#f8f9fa';
        jsonHeader.style.borderRadius = '5px';
        jsonHeader.innerHTML = '<strong><i class="fas fa-code"></i> Respuesta JSON completa</strong> <i class="fas fa-chevron-down" id="jsonToggleIcon"></i>';
        
        var jsonContent = document.createElement('div');
        jsonContent.id = 'jsonContent';
        jsonContent.style.display = 'none';
        jsonContent.style.marginTop = '10px';
        
        var scroll = document.createElement('div');
        scroll.style.maxHeight = '400px';
        scroll.style.overflowY = 'auto';

        var pre = document.createElement('pre');
        pre.style.textAlign = 'left';
        pre.style.fontSize = '11px';
        pre.style.background = '#f4f4f4';
        pre.style.padding = '15px';
        pre.style.borderRadius = '5px';
        pre.style.margin = '0';
        pre.textContent = jsonPretty;

        scroll.appendChild(pre);
        jsonContent.appendChild(scroll);
        
        // Toggle para mostrar/ocultar JSON
        jsonHeader.addEventListener('click', function() {
            const isVisible = jsonContent.style.display !== 'none';
            jsonContent.style.display = isVisible ? 'none' : 'block';
            const icon = jsonHeader.querySelector('#jsonToggleIcon');
            icon.className = isVisible ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
        });
        
        jsonSection.appendChild(jsonHeader);
        jsonSection.appendChild(jsonContent);
        container.appendChild(jsonSection);

        var copyBtn = document.createElement('button');
        copyBtn.className = 'btn btn-sm btn-secondary mt-2';
        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copiar Respuesta';
        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(jsonPretty).then(() => {
                showSuccess("Respuesta copiada al portapapeles");
            }).catch(err => {
                showError("No se pudo copiar al portapapeles");
            });
        });
        container.appendChild(copyBtn);

        swal({
            title: 'Respuesta de Hacienda',
            content: container,
            buttons: {
                confirm: {
                    text: 'Cerrar y Actualizar',
                    value: true
                }
            },
            width: '80%'
        }).then(() => {
            // Esperar un momento para asegurar que la actualización en BD se complete
            setTimeout(function() {
            // Refrescar el listado para actualizar el estado
            filtrar();
            showSuccess("Estado consultado y actualizado correctamente");
            }, 300);
        });
        
        // Guardar temporalmente para copiar
        window.respuestaHaciendaTemp = jsonPretty;
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        swal.close();
        showError("Error al consultar el estado en Hacienda: " + (errorThrown || textStatus));
    });
}

/**
 * Función para abrir el modal y cargar los pagos sin FE asociado
 */
function verOrdenesSinFactura() {
    // Abrir el modal
    $('#mdl-pagos-sin-fe').modal('show');
    
    // Limpiar el tbody
    $('#tbody-pagos-sin-fe').html('<tr><td colspan="6" style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    
    // Hacer la petición AJAX
    $.ajax({
        url: `${base_path}/fe/obtenerPagosSinFE`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            $('#tbody-pagos-sin-fe').html('<tr><td colspan="6" style="text-align: center; color: red;"><i class="fas fa-exclamation-triangle"></i> ' + response['mensaje'] + '</td></tr>');
            return;
        }
        
        const pagos = response['datos'] || [];
        
        if (pagos.length === 0) {
            $('#tbody-pagos-sin-fe').html('<tr><td colspan="7" style="text-align: center;"><i class="fas fa-check-circle"></i> No hay pagos sin factura electrónica asociada</td></tr>');
            return;
        }
        
        // Construir las filas de la tabla
        let html = '';
        pagos.forEach(function(pago) {
            const fecha = pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleString('es-CR') : 'N/A';
            const total = parseFloat(pago.total || 0).toFixed(2);
            const nombreCliente = pago.nombre_cliente || 'Sin cliente';
            const numeroOrden = pago.numero_orden || 'N/A';
            const nombreSucursal = pago.nombre_sucursal || 'N/A';
            
            html += '<tr>';
            html += '<td style="text-align: center;">' + pago.id_pago + '</td>';
            html += '<td style="text-align: center;">' + numeroOrden + '</td>';
            html += '<td style="text-align: center;">' + fecha + '</td>';
            html += '<td style="text-align: center;">' + nombreCliente + '</td>';
            html += '<td style="text-align: center;">' + nombreSucursal + '</td>';
            html += '<td style="text-align: right;">₡' + parseFloat(total).toLocaleString('es-CR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
            html += '<td style="text-align: center;">';
            html += '<button type="button" class="btn btn-sm btn-primary" onclick="abrirModalCrearFE(' + pago.id_pago + ', ' + (pago.id_orden || 0) + ', \'' + numeroOrden + '\', \'' + (pago.cedula_cliente || '') + '\', \'' + nombreCliente.replace(/'/g, "\\'") + '\', \'' + (pago.correo_cliente || '') + '\')" title="Crear Factura Electrónica">';
            html += '<i class="fas fa-file-invoice"></i> Crear FE';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });
        
        $('#tbody-pagos-sin-fe').html(html);
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        $('#tbody-pagos-sin-fe').html('<tr><td colspan="7" style="text-align: center; color: red;"><i class="fas fa-exclamation-triangle"></i> Error al cargar los pagos: ' + (errorThrown || textStatus) + '</td></tr>');
    });
}

/**
 * Abre el modal para crear FE desde un pago sin FE
 */
function abrirModalCrearFE(idPago, idOrden, numeroOrden, cedulaCliente, nombreCliente, correoCliente) {
    $('#crear-fe-id-pago').val(idPago);
    $('#crear-fe-id-orden').val(idOrden);
    $('#crear-fe-numero-orden').val(numeroOrden);
    $('#crear-fe-numero-orden-display').text(numeroOrden);
    
    // Limpiar campos
    $('#crear-fe-es-comprobante').prop('checked', false);
    $('#crear-fe-cliente-id').val('');
    $('#crear-fe-cliente-nombre').val('');
    $('#crear-fe-cedula').val(cedulaCliente || '');
    $('#crear-fe-nombre').val(nombreCliente || '');
    $('#crear-fe-correo').val(correoCliente || '');
    
    // Mostrar campos de cliente por defecto
    toggleCamposCliente();
    
    $('#mdl-crear-fe-pago').modal('show');
}

/**
 * Muestra/oculta los campos de cliente según si es comprobante
 */
function toggleCamposCliente() {
    const esComprobante = $('#crear-fe-es-comprobante').is(':checked');
    
    if (esComprobante) {
        // Ocultar campos de cliente
        $('#campos-cliente-container').hide();
        // Limpiar campos
        $('#crear-fe-cliente-id').val('');
        $('#crear-fe-cliente-nombre').val('');
        $('#crear-fe-cedula').val('');
        $('#crear-fe-nombre').val('');
        $('#crear-fe-correo').val('');
    } else {
        // Mostrar campos de cliente
        $('#campos-cliente-container').show();
    }
}

/**
 * Abre el modal para buscar cliente
 */
function abrirModalBuscarClienteFE() {
    $('#txt-buscar-cliente-fe').val('');
    $('#tbody-clientes-fe').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando clientes...</td></tr>');
    buscarClientesFE('', 1);
    $('#mdl-buscar-cliente-fe').modal('show');
}

// Variable para debounce de búsqueda
let searchTimeoutFE = null;

/**
 * Busca clientes con debounce
 */
function buscarClientesFEConDebounce(termino) {
    if (searchTimeoutFE) {
        clearTimeout(searchTimeoutFE);
    }
    searchTimeoutFE = setTimeout(function() {
        buscarClientesFE(termino, 1);
    }, 300);
}

/**
 * Busca clientes
 */
function buscarClientesFE(termino, page) {
    $.ajax({
        url: `${base_path}/facturacion/buscar-clientes`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            search: termino,
            page: page
        }
    }).done(function (response) {
        if (!response['estado']) {
            $('#tbody-clientes-fe').html('<tr><td colspan="5" class="text-center text-danger">' + response['mensaje'] + '</td></tr>');
            return;
        }
        
        const clientes = response['datos']['clientes'] || [];
        
        if (clientes.length === 0) {
            $('#tbody-clientes-fe').html('<tr><td colspan="5" class="text-center">No se encontraron clientes</td></tr>');
            return;
        }
        
        let html = '';
        clientes.forEach(function(cliente) {
            const nombreCompleto = (cliente.nombre || '') + ' ' + (cliente.apellidos || '');
            const identificacion = cliente.identificacion || '';
            html += '<tr>';
            html += '<td>' + nombreCompleto.trim() + '</td>';
            html += '<td>' + (cliente.telefono || '-') + '</td>';
            html += '<td>' + (cliente.correo || '-') + '</td>';
            html += '<td>' + (cliente.ubicacion || '-') + '</td>';
            html += '<td>';
            html += '<button type="button" class="btn btn-sm btn-primary" onclick="seleccionarClienteFE(' + cliente.id + ', \'' + nombreCompleto.trim().replace(/'/g, "\\'") + '\', \'' + (cliente.correo || '').replace(/'/g, "\\'") + '\', \'' + identificacion.replace(/'/g, "\\'") + '\')">';
            html += '<i class="fas fa-check"></i> Seleccionar';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });
        
        $('#tbody-clientes-fe').html(html);
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        $('#tbody-clientes-fe').html('<tr><td colspan="5" class="text-center text-danger">Error al buscar clientes: ' + (errorThrown || textStatus) + '</td></tr>');
    });
}

/**
 * Abre el modal para asignar/cambiar cliente (independiente del envío)
 */
function abrirModalAsignarCliente(idOrden, idInfoFe) {
    if (idInfoFe == null || idInfoFe == 0) {
        showError("ID de factura inválido");
        return;
    }
    
    // Guardar en variables globales para el flujo de asignación
    idInfoFePendiente = idInfoFe;
    idOrdenPendiente = idOrden;
    tipoEnvioPendiente = null; // null indica que es solo asignación, no envío
    
    // Verificar si tiene cliente actual
    const orden = ordenesGen.find(o => o.idFe == idInfoFe);
    const tieneCliente = orden && orden.cedFe && orden.cedFe.trim() !== '' && 
                         orden.nombreFe && orden.nombreFe.trim() !== '' && 
                         orden.correoFe && orden.correoFe.trim() !== '';
    
    // Actualizar título del modal
    const titulo = tieneCliente ? 'Cambiar Cliente' : 'Asignar Cliente';
    $('#mdl-buscar-cliente-fe-titulo').text(titulo);
    
    // Mostrar/ocultar botón de quitar cliente
    if (tieneCliente) {
        $('#btn-quitar-cliente-fe').show();
    } else {
        $('#btn-quitar-cliente-fe').hide();
    }
    
    $('#txt-buscar-cliente-fe').val('');
    $('#tbody-clientes-fe').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando clientes...</td></tr>');
    buscarClientesFE('', 1);
    $('#mdl-buscar-cliente-fe').modal('show');
}

/**
 * Abre el modal para seleccionar cliente antes de enviar factura/comprobante
 */
function abrirModalSeleccionarClienteParaEnvio() {
    $('#txt-buscar-cliente-fe').val('');
    $('#tbody-clientes-fe').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando clientes...</td></tr>');
    buscarClientesFE('', 1);
    
    // Verificar si tiene cliente actual para mostrar opción de quitar
    const orden = ordenesGen.find(o => o.idFe == idInfoFePendiente);
    const tieneCliente = orden && orden.cedFe && orden.cedFe.trim() !== '' && 
                         orden.nombreFe && orden.nombreFe.trim() !== '' && 
                         orden.correoFe && orden.correoFe.trim() !== '';
    
    // Actualizar título del modal
    const titulo = tipoEnvioPendiente === 'factura' ? 'Seleccionar Cliente para Factura' : 'Seleccionar Cliente para Comprobante';
    $('#mdl-buscar-cliente-fe-titulo').text(titulo);
    
    // Mostrar/ocultar botón de quitar cliente (solo para comprobante)
    if (tipoEnvioPendiente === 'comprobante' && tieneCliente) {
        $('#btn-quitar-cliente-fe').show();
    } else {
        $('#btn-quitar-cliente-fe').hide();
    }
    
    $('#mdl-buscar-cliente-fe').modal('show');
}

/**
 * Actualiza solo el cliente en fe_info (sin proceder con envío)
 */
function actualizarClienteSolo(clienteId, nombreCliente, correoCliente, identificacion) {
    swal({
        title: 'Actualizando cliente...',
        text: 'Por favor espere',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });
    
    $.ajax({
        url: `${base_path}/fe/actualizarClienteFeInfo`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id_info_fe: idInfoFePendiente,
            cedula: identificacion || '',
            nombre: nombreCliente || '',
            correo: correoCliente || '',
            cliente_id: clienteId
        }
    }).done(function (response) {
        swal.close();
        
        if (!response['estado']) {
            showError(response['mensaje']);
            $('#mdl-buscar-cliente-fe').modal('hide');
            return;
        }
        
        // Cerrar modal de búsqueda
        $('#mdl-buscar-cliente-fe').modal('hide');
        
        showSuccess('Cliente actualizado exitosamente');
        
        // Recargar datos
        filtrar();
        
        // Limpiar variables
        idInfoFePendiente = null;
        idOrdenPendiente = null;
        tipoEnvioPendiente = null;
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        swal.close();
        showError('Error al actualizar el cliente: ' + (errorThrown || textStatus));
        $('#mdl-buscar-cliente-fe').modal('hide');
    });
}

/**
 * Quita el cliente desde el modal
 */
function quitarClienteDesdeModal() {
    if (!idInfoFePendiente) {
        return;
    }
    
    const orden = ordenesGen.find(o => o.idFe == idInfoFePendiente);
    
    swal({
        title: '¿Quitar cliente?',
        text: 'Se eliminarán los datos del cliente.',
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                closeModal: true
            },
            confirm: {
                text: 'Sí, quitar',
                value: true,
                visible: true,
                closeModal: false
            }
        },
        dangerMode: true
    }).then((confirmado) => {
        if (confirmado) {
            $('#mdl-buscar-cliente-fe').modal('hide');
            
            // Si es solo asignación (no envío), quitar y recargar
            if (tipoEnvioPendiente === null) {
                quitarClienteSolo();
            } else {
                // Si es para envío, quitar y proceder con envío
                quitarClienteYEnviar(idOrdenPendiente, idInfoFePendiente, orden);
            }
            
            // Limpiar variables
            idInfoFePendiente = null;
            idOrdenPendiente = null;
            tipoEnvioPendiente = null;
        }
    });
}

/**
 * Quita el cliente de fe_info (sin proceder con envío)
 */
function quitarClienteSolo() {
    swal({
        title: 'Quitando cliente...',
        text: 'Por favor espere',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });
    
    $.ajax({
        url: `${base_path}/fe/actualizarClienteFeInfo`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id_info_fe: idInfoFePendiente,
            cedula: '',
            nombre: '',
            correo: '',
            cliente_id: null
        }
    }).done(function (response) {
        swal.close();
        
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        
        showSuccess('Cliente eliminado exitosamente');
        
        // Recargar datos
        filtrar();
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        swal.close();
        showError('Error al quitar el cliente: ' + (errorThrown || textStatus));
    });
}

/**
 * Selecciona un cliente del modal de búsqueda
 */
function seleccionarClienteFE(clienteId, nombreCliente, correoCliente, identificacion) {
    // Si estamos en el flujo de creación de FE desde pago
    if ($('#crear-fe-id-pago').val()) {
        $('#crear-fe-cliente-id').val(clienteId);
        $('#crear-fe-cliente-nombre').val(nombreCliente);
        
        // Si el cliente tiene identificación, usarla
        if (identificacion) {
            $('#crear-fe-cedula').val(identificacion);
        }
        
        // Usar correo del cliente si está disponible
        if (correoCliente) {
            $('#crear-fe-correo').val(correoCliente);
        }
        
        // Usar nombre del cliente si está disponible
        if (nombreCliente) {
            $('#crear-fe-nombre').val(nombreCliente);
        }
        
        $('#mdl-buscar-cliente-fe').modal('hide');
    } 
    // Si estamos en el flujo de asignación independiente (sin envío)
    else if (idInfoFePendiente && tipoEnvioPendiente === null) {
        actualizarClienteSolo(clienteId, nombreCliente, correoCliente, identificacion);
    }
    // Si estamos en el flujo de actualización antes de enviar
    else if (idInfoFePendiente) {
        actualizarClienteYEnviar(clienteId, nombreCliente, correoCliente, identificacion);
    }
}

/**
 * Actualiza el cliente en fe_info y procede con el envío
 */
function actualizarClienteYEnviar(clienteId, nombreCliente, correoCliente, identificacion) {
    swal({
        title: 'Actualizando cliente...',
        text: 'Por favor espere',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });
    
    $.ajax({
        url: `${base_path}/fe/actualizarClienteFeInfo`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id_info_fe: idInfoFePendiente,
            cedula: identificacion || '',
            nombre: nombreCliente || '',
            correo: correoCliente || '',
            cliente_id: clienteId
        }
    }).done(function (response) {
        swal.close();
        
        if (!response['estado']) {
            showError(response['mensaje']);
            $('#mdl-buscar-cliente-fe').modal('hide');
            return;
        }
        
        // Cerrar modal de búsqueda
        $('#mdl-buscar-cliente-fe').modal('hide');
        
        // Recargar datos y proceder con el envío
        filtrar();
        
        // Esperar un momento para que se recarguen los datos
        setTimeout(function() {
            const orden = ordenesGen.find(o => o.idFe == idInfoFePendiente);
            if (tipoEnvioPendiente === 'factura') {
                procederConEnvioFactura(idOrdenPendiente, idInfoFePendiente, orden);
            } else if (tipoEnvioPendiente === 'comprobante') {
                procederConEnvioComprobante(idOrdenPendiente, idInfoFePendiente, orden);
            }
            
            // Limpiar variables
            idInfoFePendiente = null;
            idOrdenPendiente = null;
            tipoEnvioPendiente = null;
        }, 500);
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        swal.close();
        showError('Error al actualizar el cliente: ' + (errorThrown || textStatus));
        $('#mdl-buscar-cliente-fe').modal('hide');
    });
}

/**
 * Quita el cliente de fe_info y procede con el envío del comprobante
 */
function quitarClienteYEnviar(idOrden, idInfoFe, orden) {
    swal({
        title: 'Quitando cliente...',
        text: 'Por favor espere',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });
    
    $.ajax({
        url: `${base_path}/fe/actualizarClienteFeInfo`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id_info_fe: idInfoFe,
            cedula: '',
            nombre: '',
            correo: '',
            cliente_id: null
        }
    }).done(function (response) {
        swal.close();
        
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        
        // Recargar datos y proceder con el envío
        filtrar();
        
        // Esperar un momento para que se recarguen los datos
        setTimeout(function() {
            const ordenActualizada = ordenesGen.find(o => o.idFe == idInfoFe);
            procederConEnvioComprobante(idOrden, idInfoFe, ordenActualizada);
        }, 500);
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        swal.close();
        showError('Error al quitar el cliente: ' + (errorThrown || textStatus));
    });
}

/**
 * Guarda la creación del FE
 */
function guardarCrearFE() {
    const idPago = $('#crear-fe-id-pago').val();
    const idOrden = $('#crear-fe-id-orden').val();
    const esComprobante = $('#crear-fe-es-comprobante').is(':checked');
    const cedula = $('#crear-fe-cedula').val().trim();
    const nombre = $('#crear-fe-nombre').val().trim();
    const correo = $('#crear-fe-correo').val().trim();
    
    // Validaciones básicas
    if (!idPago || !idOrden) {
        showError('Error: No se pudo obtener la información del pago');
        return;
    }
    
    // Si es comprobante, no validar campos de cliente
    if (!esComprobante) {
        // Validar campos de cliente solo si NO es comprobante
        if (!cedula) {
            showError('Por favor ingrese la cédula');
            $('#crear-fe-cedula').focus();
            return;
        }
        
        if (!nombre) {
            showError('Por favor ingrese el nombre');
            $('#crear-fe-nombre').focus();
            return;
        }
        
        if (!correo) {
            showError('Por favor ingrese el correo electrónico');
            $('#crear-fe-correo').focus();
            return;
        }
        
        // Validar formato de correo básico
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(correo)) {
            showError('Por favor ingrese un correo electrónico válido');
            $('#crear-fe-correo').focus();
            return;
        }
    }
    
    const titulo = esComprobante ? 'Creando Comprobante Electrónico...' : 'Creando Factura Electrónica...';
    
    swal({
        title: titulo,
        text: 'Por favor espere',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });
    
    $.ajax({
        url: `${base_path}/fe/crearFeDesdePago`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            id_pago: idPago,
            id_orden: idOrden,
            cedula: esComprobante ? '' : cedula,
            nombre: esComprobante ? '' : nombre,
            correo: esComprobante ? '' : correo,
            cliente_id: $('#crear-fe-cliente-id').val() || null,
            es_comprobante: esComprobante ? 1 : 0
        }
    }).done(function (response) {
        swal.close();
        
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        
        const mensaje = esComprobante ? 'Comprobante electrónico creado exitosamente' : 'Factura electrónica creada exitosamente';
        showSuccess(mensaje);
        
        // Cerrar modal
        $('#mdl-crear-fe-pago').modal('hide');
        
        // Recargar la lista de pagos sin FE
        setTimeout(function() {
            verOrdenesSinFactura();
            // Recargar la tabla principal de facturas
            filtrar();
        }, 500);
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        swal.close();
        showError('Error al crear la factura electrónica: ' + (errorThrown || textStatus));
    });
}
