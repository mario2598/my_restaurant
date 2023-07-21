window.addEventListener("load", init, false);

var contenedores = new Map();
var valores = new Map();
var total = 0.00;
var pendiente = 0.0;
var totalPagar = 0.0;
var prepago = 0.00;
var vuelto = 0.00;
var efectivo = 0.00;
var tarjeta = 0.00;
var sinpe = 0.00;
var descuento = 0;
var imprime = "N";
var factura = "N";
var montoTeclado = "0";


function init() {
   // console.log(orden);
    inicializarMapaContenedores();
    inicializarMapaValores();
    limpiar();
}

function inicializarMapaContenedores() {
    contenedores.set("total", $("#lbl-total"));
    contenedores.set("pendiente", $("#lbl-total-pendiente"));
    contenedores.set("vuelto", $("#lbl-vuelto"));
    contenedores.set("efectivo", $("#txt-efectivo"));
    contenedores.set("tarjeta", $("#txt-tarjeta"));
    contenedores.set("sinpe", $("#txt-sinpe"));
    contenedores.set("teclado", $("#txt-teclado"));
    contenedores.set("descuento", $("#txt-descuento"));
    contenedores.set("descuento-total", $("#lbl-descuento"));
}

function inicializarMapaValores() {
    valores.set("total", total);
    valores.set("pendiente", pendiente);
    valores.set("vuelto", vuelto);
    valores.set("efectivo", efectivo);
    valores.set("tarjeta", tarjeta);
    valores.set("sinpe", sinpe);
}

/**
 * Asigna el valor por defecto de las variables
 */
function limpiar() {
    pendiente = 0.0;
    totalPagar = 0.0;
    prepago = 0.00;
    vuelto = 0.00;
    efectivo = 0.00;
    tarjeta = 0.00;
    sinpe = 0.00;
    descuento = 0;
    imprime = "N";
    factura = "N";
    montoTeclado = "0";
    limpiarTeclado();
    cargarDatosOrden();
}

function cargarDatosOrden() {
    total = parseFloat(redondeo(orden.total));
    prepago = parseFloat(redondeo(orden.total_cancelado));
    pendiente = parseFloat(redondeo(redondeo(total) - redondeo(pendiente)));
    vuelto = parseFloat(redondeo(0));
    actualizarPendiente();
}

function actualizarTotales() {
    actualizarLabels(["total", "pendiente", "vuelto"], ["0.00", redondeo(pendiente), redondeo(vuelto)]);
}

function actualizarValores() {
    actualizarInputs(["efectivo", "tarjeta", "sinpe"], [efectivo, tarjeta, sinpe]);
}

/***************************************
 *  
 * Métodos para actualizar elementos
 * 
***************************************/
function actualizarInputs(claves = [], valores = []) {
    let contador = 0;
    claves.forEach(clave => {
        actualizarInput(clave, valores[contador]);
        contador++;
    });
}

function actualizarLabels(claves = [], valores = []) {
    let contador = 0;
    claves.forEach(clave => {
        actualizarLabel(clave, valores[contador]);
        contador++;
    });
}

function actualizarInput(clave, valor = "") {
    if (!isEmpty(clave)) {
        contenedores.get(clave).val(valor);
    }
}

function actualizarLabel(clave, valor = "") {
    if (!isEmpty(clave)) {
        contenedores.get(clave).html(valor);
    }
}


/*********************************
 * 
 * Métdos OnAction
 * 
 ********************************/

function cambioValor(clave) {
    if (validarExcedentes(clave)) {
        actualizarValor(clave, contenedores.get(clave).val());
    } else {
        actualizarValor(clave, "0");
        swal('Monto excedente en métodos de pago electrónicos', "El total a pagar en " + clave + "provoca que se sobrepase el total a pagar de la orden", 'error');
    }
    actualizarTotalPagar();
}

function validarExcedentes(clave) {
    if (clave == "efectivo") {
        return true;
    } else {
        let auxTajeta = parseFloat(getValorInput("tarjeta"));
        let auxSinpe = parseFloat(getValorInput("sinpe"));
        return (auxTajeta + auxSinpe) <= total;
    }
}

function getValorInput(clave) {
    let valor = contenedores.get(clave).val();
    if (!isEmpty(valor)) {
        return redondeo(valor);
    } else {
        return redondeo("0");
    }
}

function actualizarValor(clave, valor) {
    valor = redondeo(valor);
    switch (clave) {
        case "efectivo":
            efectivo = parseFloat(valor);
            break;
        case "tarjeta":
            tarjeta = parseFloat(valor);
            break;
        case "sinpe":
            sinpe = parseFloat(valor);
            break;
        default: break;
    }
    actualizarInput(clave, valor);
}

function actualizarTotalPagar() {
    totalPagar = efectivo + getTotalElectronicos();
    actualizarLabel("total", redondeo(totalPagar));
    actualizarPendiente();
}

function getTotalElectronicos() {
    return sinpe + tarjeta;
}

function actualizarPendiente() {
    pendiente = getTotalPendiente() - totalPagar;
    if (pendiente < 0.00) {
        pendiente = 0.00;
    }
    actualizarLabel("pendiente", redondeo(pendiente));
    actualizarVuelto();
}

function actualizarVuelto() {
    vuelto = 0.00;
    let diferencia = totalPagar - getTotalPendiente();
    if (diferencia > 0.00 && efectivo >= diferencia) {
        vuelto = diferencia;
    }
    actualizarLabel("vuelto", redondeo(vuelto));
}

function getTotalPendiente() {
    return getTotal() - prepago;
}

function getTotal() {
    return total - (total * getDescuento());
}

function getDescuentoTotal() {
    return total * getDescuento();
}

function getDescuento() {
    return descuento / 100;
}

function asignarDescuento() {
    descuento = contenedores.get("descuento").val();

    actualizarLabel("descuento-total", redondeo(getDescuentoTotal()));
    actualizarPendiente();
}


function cambiarImprimeTiquete() {
    imprime = $("#sel-tiquete").val();
   // console.log("Imprime: " + imprime);
}

function cambiarGeneraFactura() {
    factura = $("#sel-factura").val();
   // console.log("Factura: " + factura);
}

function confirmarPagarOrden(adelanto = false) {

    swal('Procesar Orden', "Procesando pago, espere ..." , 'info');
    if (!adelanto) {
        pagarOrden();
    }else{
        adelantarOrden();
    }
}

function pagarOrden() {
    if (validarPago()) {
        let descuento = $("#txt-descuento").val();
        let id_cliente = $("#txt-id-cliente").val();
        let nombre_cliente = $("#txt-cliente").val();
        let monto_efectivo = efectivo - vuelto;
        
        try {
            $.ajax({
                url: `${base_path}/cocina/facturar/ordenes/facturarOrden`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    id_orden: orden["id"],
                    monto_efectivo: monto_efectivo,
                    monto_tarjeta: tarjeta,
                    monto_sinpe: sinpe,
                    monto_otros: 0.00,
                    genera_factura: factura,
                    imprime_tiquete: imprime,
                    porcentaje_descuento :descuento,
                    idCliente : id_cliente,
                    nombreCliente : nombre_cliente,
                    ordenParcialRuta: "NO"
                }
            }).done(function (res) {
                if (!res['estado']) {
                    swal('Pagar Orden', res['mensaje'], 'error');
                } else {
                    let id = res["datos"];

                    if (!isEmpty(id)) {
                        swal('Pagar Orden', "Orden " + id + " pagada correctamente.", 'success');
                        $("#btn-pdf").prop('href',`${base_path}/impresora/tiquete/${id}`);
                        document.getElementById('btn-pdf').click();
                    } else {
                        swal('Pagar Orden', "No se ha podido redirigir a la nueva vista.", 'error');
                        setError('Pagar Orden', "No se ha podido redirigir a la nueva vista");
                        
                    }
                    setTimeout(window.location.href = `${base_path}/facturacion/facturar`, 3000);
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                swal('Pagar Orden', "Algo salió mal.", 'error');
            });

        } finally {

        }
    } else {
        swal('Pagar orden.', "Parece que aún hay monto pendiente.", 'error');
    }

}

function validarPago() {
    actualizarPendiente();
    return pendiente <= 0.00;
}

function adelantarOrden() {
    try {
        $.ajax({
            url: `${base_path}/cocina/facturar/ordenes/prePagarOrden`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                id_orden: orden["id"],
                monto: totalPagar
            }
        }).done(function (res) {
            if (!res['estado']) {
                swal('Pagar Orden', res['mensaje'], 'error');
            } else {
                let id = res["datos"];

                if (!isEmpty(id)) {
                    swal('Adelanto Orden', `Se generó un adelanto de ${totalPagar} para la orden.`, 'success');
                } else {
                    swal('Pagar Orden', "No se ha podido redirigir a la nueva vista.", 'error');
                    setError('Adelnato Orden', "No se ha podido redirigir a la nueva vista");
                }

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            swal('Adelnato Orden', "Algo salió mal.", 'error');
        });

    } finally {

    }
}

/***********************
 * Métodos del teclado de ayuda
 **********************/

function agregarNumeroTeclado(event) {
    montoTeclado += $(event.target).val();
    actualizarInput("teclado", redondeo(montoTeclado));
}

function limpiarTeclado() {
    montoTeclado = 0.00;
    actualizarInput("teclado", redondeo(montoTeclado));
}

function asignarMontoTeclado(event) {
    let valor = $(event.target).val() || $(event.target).parent().val();
    switch (valor) {
        case "E":
            cambioValorTeclado("efectivo");
            break;
        case "T":
            cambioValorTeclado("tarjeta");
            break;
        case "S":
            cambioValorTeclado("sinpe");
            break;
        default:
            break;
    }
}

function seleccionarCliente(id, nombre) {
    $("#txt-id-cliente").val(id);
    $("#txt-cliente").val(nombre);
}


function cambioValorTeclado(clave) {
    actualizarInput(clave, redondeo(montoTeclado));
    cambioValor(clave);
    limpiarTeclado();
}

/** Métodos generales */

function isEmpty(str) {
    return (!str || 0 === str.length);
}

function isBlank(str) {
    return (!str || /^\s*$/.test(str));
}

function redondeo(valor, decimales = 2) {
    return parseFloat(valor).toFixed(decimales);
}
