var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var procentajeServicioRestaurante = 0.1;
var tipoSeleccionado = 0;
var extrasDetalleAux = [];
var impServicioAux = null;
var categoriaSeleccionada = 0;
var productoSeleccionado = null;
var detalleSeleccionado = null;
var salonSeleccionado = 0;
var mobiliarioSeleccionado = 0;
var clienteSeleccionado = 0;
var contenedores = new Map();
var detalles = [];

var totalSeleccionado = 0;
var cambiosPendientes = false;
var mtoDescuentoGen = 0;
var detallesSeleccionados = [];
var infoEnvio = {
    "incluye_envio": false,
    "precio": 0,
    "descripcion_lugar": "",
    "contacto": ""
};
var infoFE = {
    "incluyeFE": false,
    "info_ced_fe": "",
    "info_nombre_fe": "",
    "info_correo_fe": ""
};

var guardandoOrden = false;
var idOrdenAnular = 0;
var detallesAnular = [];
var cambiosPendientes = false;

window.addEventListener("load", init, false);

document.addEventListener('DOMContentLoaded', function () {

    //Inicializa scroll para las dos listas
    inicializarScroller('scrl-categorias');
    inicializarScroller('scrl-productos');
    inicializarScroller('scrl-orden');
});

function init() {
    cargarProductosPos();
    limpiar();
    inicializarMapaContenedores();

}


document.addEventListener('DOMContentLoaded', function () {
    //Inicializa scroll para las dos listas

});

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-ordenes tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    validarCajaAbierta();

});

function validarCajaAbierta() {
    if (!cajaAbierta) {
        $('#contEscogerProductos').fadeOut(100);
        $('#contFacturar').fadeOut(100);
        $('#contDetalles').fadeOut(100);
        $('#contCerrarCaja').fadeOut(100);
        $('#contOrdenesCaja').fadeOut(100);
        $('#contLimiarCaja').fadeOut(100);
        $('#contAbrirCaja').fadeIn(100);
    } else {
        $('#contEscogerProductos').fadeIn(100);
        $('#contFacturar').fadeIn(100);
        $('#contDetalles').fadeIn(100);
        $('#contCerrarCaja').fadeIn(100);
        $('#contOrdenesCaja').fadeIn(100);
        $('#contLimiarCaja').fadeIn(100);
        $('#contAbrirCaja').fadeOut(100);
    }
}

/**
 * Inicializa el evento de scroll mediante arrastre del cursos para un contenedor especificado.
 * @param {String} id Identificador único del elemento a inicializar
 */
function inicializarScroller(id) {
    try {

        const ele = document.getElementById(id);

        ele.scrollTop = 0;
        ele.scrollLeft = 0;

        ele.style.cursor = 'grab';

        let pos = {
            top: 0,
            left: 0,
            x: 0,
            y: 0
        };

        const mouseDownHandler = function (e) {
            ele.style.cursor = 'grabbing';
            ele.style.userSelect = 'none';

            pos = {
                left: ele.scrollLeft,
                top: ele.scrollTop,
                // Get the current mouse position
                x: e.clientX,
                y: e.clientY,
            };

            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
        };

        const mouseMoveHandler = function (e) {
            // How far the mouse has been moved
            const dx = e.clientX - pos.x;
            const dy = e.clientY - pos.y;

            // Scroll the element
            ele.scrollTop = pos.top - dy;
            ele.scrollLeft = pos.left - dx;
        };

        const mouseUpHandler = function () {
            ele.style.cursor = 'grab';
            ele.style.removeProperty('user-select');

            document.removeEventListener('mousemove', mouseMoveHandler);
            document.removeEventListener('mouseup', mouseUpHandler);
        };

        // Attach the handler
        ele.addEventListener('mousedown', mouseDownHandler);
    } catch (error) {
        // console.log(error);
    }

}



function limpiar() {
    procentajeServicioRestaurante = 0.1;
    tipoSeleccionado = 0;
    categoriaSeleccionada = 0;
    productoSeleccionado = null;
    clienteSeleccionado = 0;

}

function inicializarMapaContenedores() {
    contenedores.set("categorias", $("#scrl-categorias"));
    contenedores.set("productos", $("#tbody-productos"));
    contenedores.set("tipos", $("#nv-tipos"));
    contenedores.set("orden", $("#tbody-orden"));
}

function generarTipos() {
    var cards = '';
    var contador = 0;
    //Por cada categoría, genera el HTML correspondiente para el card que será insertado en el scroller
    tipos.forEach(tipo => {
        cards += generarHTMLTipo(contador, tipo.nombre, tipo.color);
        contador++;
    });

    $(contenedores.get("tipos")).html(cards);
}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLTipo(indice, nombre, color = "#0DA8EE") {
    // console.log("indice: " + indice + " , nombre: " + nombre + " , color: " + color);
    return `<li class="nav-item mr-1" onclick="seleccionarTipo(${indice})">
                <a class="nav-link bg-white text-info" style="color:${color} !important" href="javascript:void(0);">${nombre}</a>
            </li>`;
}

/**
 * Selecciona un tipo e invoca los métodos requeridos para generar las categorías y los productos en base al tipo especificado.
 * @param {Integer} indice Índice del tipo a seleccionar
 */
function seleccionarTipo(indice) {
    tipoSeleccionado = indice;
    categoriaSeleccionada = 0;
    generarCategorias();
    generarProductos();
}

function generarCategorias() {
    var cards = '';
    var contador = 0;
    var tipo = tipos[tipoSeleccionado];

    var categorias = Array.isArray(tipo.categorias) ? tipo.categorias : Object.values(tipo.categorias);

    categorias.forEach(categoria => {
        cards += generarHTMLCategoria(contador, categoria.categoria, tipo.color);
        contador++;
    });

    $(contenedores.get("categorias")).html(cards);

}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLCategoria(indice, nombre, color = "#0DA8EE") {
    /* return `<div class="card card-body align-middle bg-info" style="min-width: 6rem !important; margin: .5rem .5rem !important" 
                 onclick="seleccionarCategoria('${indice}')">
                 <h6 class="mb-0">${nombre}</h6>
             </div>`;*/
    return `<li class="nav-item mr-1" onclick="seleccionarCategoria(${indice})">
                <a class="nav-link active" style="background-color: ${color} !important;" onclick="return false">${nombre}</a>
            </li>`;
}

function seleccionarCategoria(indice) {
    categoriaSeleccionada = indice;
    generarProductos();
}

function generarProductos() {
    var categoria = tipos[tipoSeleccionado].categorias[categoriaSeleccionada];
    var contador = 0;
    var cards = '';

    $(contenedores.get("productos")).html("");
    //Por cada categoría, genera el HTML correspondiente para el card que será insertado en el scroller
    categoria.productos.forEach(producto => {
        cards += generarHTMLProducto(producto.nombre, producto.codigo, producto.precio, producto.cantidad, producto.tipoProducto);
        contador++;
    });

    $(contenedores.get("productos")).html(cards);
}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLProducto(nombre, codigo, precio, cantidad, tipoProd) {
    var text = `<tr class="filaProductos" onclick="seleccionarProducto('N','${codigo}')">
    <td width="40%">${nombre}`;
    if (tipoProd == "E") {
        text += `<br> <small `;
        if (cantidad < 15) {
            text += `style="color:red;"`;
        }
        text += `> Cantidad : <strong> ${cantidad}</strong></small>`;
    }
    text += `</td><td width="30%" style="text-align: center">${parseFloat(precio).toFixed(2)}</td></tr>`;

    return text;
}

function generarHTMLExtras() {
    let texto = ``;
    let cont = 0;
    productoSeleccionado.extras.forEach(extra => {

        texto += `<div class="col-sm-12 col-md-12 col-xl-12" >
                    <div class="col-sm-12 col-md-12 col-xl-12">
                      <h5 class="modal-title">${extra.dsc_grupo} ${extra.requerido == 1 ? "<small>(Requerido)</small>" : ""}</h5>
                    </div>
                    <div class="col-sm-12 col-md-12 col-xl-12">
                        <div class="form-group">`;

        if (extra.multiple == 1) {
            extra.extras.forEach(extra1 => {
                texto += `<label style="margin-left:10px;">
                            <input type="checkbox" name="${extra.dsc_grupo}" 
                            onchange="seleccionarExtraCheck(this, ${extra1.id})"
                            value="${extra1.id}">${extra1.descripcion} ₡${extra1.precio}
                        </label>
                        `;
                cont++;
            });
        } else {
            extra.extras.forEach(extra1 => {
                texto += `<label style="margin-left:10px;">
                            <input type="radio" name="${extra.dsc_grupo}"
                            onchange="seleccionarExtraRadio(this, '${extra.dsc_grupo}', ${extra.multiple}, ${extra1.id})"
                            value="${extra1.id}">${extra1.descripcion} ₡${extra1.precio}
                        </label>
                        `;
                cont++;
            });
            if (extra.requerido == 0) {
                texto += `<label style="margin-left:10px;">
                            <input type="radio" name="${extra.dsc_grupo}" checked
                            onchange="seleccionarExtraRadio(this, '${extra.dsc_grupo}', ${extra.multiple}, -1)"
                            value="null">Ninguno
                        </label>
                        `;
            }

        }

        texto += `</div>
        </div></div>`;
    });


    $('#cont-extras').html(texto);
}


function generarHTMLExtrasDetalle() {
    let texto = ``;
    let cont = 0;
    productoSeleccionado.extras.forEach(extra => {

        texto += `<div class="col-sm-12 col-md-12 col-xl-12" >
                    <div class="col-sm-12 col-md-12 col-xl-12">
                      <h5 class="modal-title">${extra.dsc_grupo} ${extra.requerido == 1 ? "<small>(Requerido)</small>" : ""}</h5>
                    </div>
                    <div class="col-sm-12 col-md-12 col-xl-12">
                        <div class="form-group">`;

        if (extra.multiple == 1) {
            extra.extras.forEach(extra1 => {
                const found = detalleSeleccionado.extras.find(s => s.id == extra1.id);
                if (found != null && found != undefined) {
                    extra1.seleccionado = true;
                }
                texto += `<label style="margin-left:10px;">
                            <input type="checkbox" name="${extra.dsc_grupo}" ${(found == null || found == undefined) ? "" : "checked"} 
                            onchange="seleccionarExtraCheck(this, ${extra1.id})"
                            value="${extra1.id}">${extra1.descripcion} ₡${extra1.precio}
                        </label>
                        `;
                cont++;
            });
        } else {
            var selecciono = false;
            extra.extras.forEach(extra1 => {
                const found = detalleSeleccionado.extras.find(s => s.id == extra1.id);
                if (found != null && found != undefined) {
                    extra1.seleccionado = true;
                    selecciono = true;
                }
                texto += `<label style="margin-left:10px;">
                            <input type="radio" name="${extra.dsc_grupo}"  ${(found == null || found == undefined) ? "" : "checked"} 
                            onchange="seleccionarExtraRadio(this, '${extra.dsc_grupo}', ${extra.multiple}, ${extra1.id})"
                            value="${extra1.id}">${extra1.descripcion} ₡${extra1.precio}
                        </label>
                        `;
                cont++;
            });
            if (extra.requerido == 0) {
                texto += `<label style="margin-left:10px;">
                            <input type="radio" name="${extra.dsc_grupo}" ${(!selecciono) ? "checked" : ""} 
                            onchange="seleccionarExtraRadio(this, '${extra.dsc_grupo}', ${extra.multiple}, -1)"
                            value="null">Ninguno
                        </label>
                        `;
            }

        }

        texto += `</div>
        </div></div>`;
    });

    $('#cont-extras-detalle').html(texto);
}


function seleccionarExtraRadio(radio, grupo, multiple, extra1) {
    const found = productoSeleccionado.extras.find(s => s.dsc_grupo == grupo && s.multiple == multiple);
    if (found == null || found == undefined) {
        showError("Grupo extra invalido");
        return;
    }

    found.extras.forEach(extra => {
        extra.seleccionado = false;
    });

    if (extra1 == -1) {
        return;
    }

    const ext = found.extras.find(s => s.id == extra1);

    if (radio.checked) {
        ext.seleccionado = true;
    } else {
        ext.seleccionado = false;
    }
}

function seleccionarExtraCheck(check, extraAux) {
    productoSeleccionado.extras.forEach(extra => {
        extra.extras.forEach(extra1 => {
            if (extra1.id == extraAux) {
                if (check.checked) {
                    extra1.seleccionado = true;
                } else {
                    extra1.seleccionado = false;
                }
            }
        });
    });

}


function abrirModalExtrasProd(producto) {
    productoSeleccionado = producto;
    generarHTMLExtras();
    $("#mdl-extras").modal("show");
}


function abrirModalExtrasDetalle() {
    generarHTMLExtrasDetalle();
    $("#mdl-extras-detalle").modal("show");
}


function seleccionarExtrasProd() {
    var extrasCompletos = true;
    var extrasDetalleAux2 = [];
    var continuar = true;
    productoSeleccionado.extras.forEach(extra => {
        var seleccionado = false;

        extra.extras.forEach(extra1 => {
            if (extra1.seleccionado == 1) {
                extrasDetalleAux2.push(extra1);
                seleccionado = true;
            }
        });

        if (extra.requerido == 1) {
            if (!seleccionado) {
                continuar = false;
                showError(`El tipo ${extra.dsc_grupo} es requerido`);
                return;
            }
        }
    });

    if (continuar) {
        extrasDetalleAux = extrasDetalleAux2;
        cambiosPendientes = true;
        agregarProducto(productoSeleccionado);
        cerrarExtras();
    }
}

function actualizarExtrasDetalle() {
    var extrasDetalleAux2 = [];
    var continuar = true;
    productoSeleccionado.extras.forEach(extra => {
        var seleccionado = false;

        extra.extras.forEach(extra1 => {
            if (extra1.seleccionado == 1) {
                extrasDetalleAux2.push(extra1);
                seleccionado = true;
            }
        });

        if (extra.requerido == 1) {
            if (!seleccionado) {
                continuar = false;
                showError(`El tipo ${extra.dsc_grupo} es requerido`);
                return;
            }
        }
    });

    if (continuar) {
        detalleSeleccionado.extras = extrasDetalleAux2;
        detalleSeleccionado.observacion = $('#detAdicional').val();
        cambiosPendientes = true;
        actualizarOrden();
        cerrarExtrasDetalle();
    }

    productoSeleccionado.extras.forEach(extra => {
        extra.extras.forEach(extra1 => {
            extra1.seleccionado = false;
        });
    });
}

function cerrarExtrasDetalle() {
    $("#mdl-extras-detalle").modal("hide");
    $('#detAdicional').val('');
}

function cerrarExtras() {
    $("#mdl-extras").modal("hide");
}

function seleccionarProducto(impuestoServicio, codigo, todos = false) {
    let producto = buscarProductoCodigo(codigo, todos);
    if (producto !== undefined) {

        if (validarCantidadProducto(producto)) {
            if (producto.extras.length > 0) {
                abrirModalExtrasProd(producto);
            } else {
                extrasDetalleAux = [];
                agregarProducto(producto);
            }
        } else {
            swal('Agregar producto', "Existencias agotadas para este producto.", 'error');
        }
    } else {
        swal('Agregar producto', "No se ha podido encotrar el producto con el código indicado.", 'error');
    }
}

function agregarProducto(producto) {
    productoSeleccionado = producto;
    let indice = buscarDetallePrevio(producto);
    if (indice >= 0) {
        actualizarDetalleOrden(indice);
    } else {
        const indiceMasAlto = obtenerIndiceMasAlto(detalles) + 1;
        detalles.push(crearDetalleOrden(indiceMasAlto, 1, producto, ""));
        reducirCantidadProducto(producto.codigo);
    }

    productoSeleccionado.extras.forEach(extra => {
        extra.extras.forEach(extra1 => {
            extra1.seleccionado = false;
        });
    });
    cambiosPendientes = true;
    actualizarOrden();
}

function obtenerIndiceMasAlto(detalles) {
    if (detalles.length === 0) {
        return 0; // Si no hay detalles, empezar desde 0
    }

    // Obtener el índice más alto
    return Math.max(...detalles.map(detalle => detalle.indice));
}

function buscarProductoCodigo(codigo, todos = false) {
    if (codigo != null && codigo != undefined) {
        let productos;
        productos = productosGeneral;
        var productoEncontrado;
        productos.forEach(producto => {
            if (producto.codigo == codigo) {
                productoEncontrado = producto;
            }
        });
        return productoEncontrado;
    }
    return;
}

function validarCantidadProducto(producto) {
    let cantidad = parseInt(producto.cantidad);
    //No requiere validación
    if (producto.tipoProducto == "R") {
        return true;
    } else if (cantidad == 0) {
        return false;
    } else {
        return true;
    }
}

function arraysSonIguales(arr1, arr2) {
    return arr1.length === arr2.length && arr1.every((valor, indice) => valor === arr2[indice]);
}


function buscarDetallePrevio(producto) {
    let indice = -1;
    let contador = 0;
    let aumentar = true;

    detalles.forEach(detalle => {
        var existeDetalle = arraysSonIguales(extrasDetalleAux, detalle.extras);

        if (detalle.producto.codigo == producto.codigo && existeDetalle) {
            indice = contador;
            aumentar = false;
        }
        if (aumentar) {
            contador++;
        }
    });
    return indice;
}

function eliminarLineaDetalleOrden(indice) {
    var detalle = detalles[indice];
    detalles.splice(indice, 1);

    var producto = buscarProductoCodigo(detalle.producto.codigo);
    if (producto !== undefined) {
        let cantidad = parseInt(producto.cantidad);
        if (cantidad > -1) {
            producto.cantidad += detalle.cantidad;
        }
    }
    cambiosPendientes = true;
    actualizarOrden();
    generarProductos();
}

function actualizarDetalleOrden(indice, aumenta = true) {

    var detalle = detalles[indice];
    if (aumenta) {
        detalle.cantidad = detalle.cantidad + 1;
        detalle.total = detalle.cantidad * detalle.producto.precio;

        detalles[indice] = detalle;
        reducirCantidadProducto(detalle.producto.codigo);
    } else {
        detalle.cantidad = detalle.cantidad - 1;
        if (detalle.cantidad <= 0) {
            detalles.splice(indice, 1);
        } else {
            detalle.total = detalle.cantidad * detalle.producto.precio;

            detalles[indice] = detalle;
        }
        aumentarCantidadProducto(detalle.producto.codigo);
    }
    cambiosPendientes = true;
    actualizarOrden();
    generarProductos();
}

function agregarDetalleInpt(indice, codigo, aumenta = true) {
    if (!aumenta) {
        actualizarDetalleOrden(indice, aumenta);
        return;
    }
    let producto = buscarProductoCodigo(codigo, false);
    if (producto !== undefined) {
        if (validarCantidadProducto(producto)) {
            actualizarDetalleOrden(indice, aumenta);
        } else {
            swal('Agregar producto', "Existencias agotadas para este producto.", 'error');
        }
    } else {
        swal('Agregar producto', "No se encontro el producto.", 'error');
    }

}

function actualizarDetalleOrdenInput(indice, cantidad) {
    var detalle = detalles[indice];
    if (cantidad < 1) {
        cantidad = detalle.cantidad;
        showError("El valor no puede ser menor a 1");
        return;
    }
    detalle.cantidad = cantidad;
    detalle.total = detalle.cantidad * detalle.producto.precio;
    detalles[indice] = detalle;
    actualizarOrden();
}

function agregarDescripcionDetalle(indice) {
    detalleSeleccionado = detalles[indice];
    productoSeleccionado = detalleSeleccionado.producto;
    $('#detAdicional').val('');
    $('#detAdicional').val(detalleSeleccionado.observacion);

    abrirModalExtrasDetalle();

}

function aumentarCantidadProducto(codigo) {
    var producto = buscarProductoCodigo(codigo);
    if (producto !== undefined) {
        let cantidad = parseInt(producto.cantidad);
        if (cantidad > -1) {
            cantidad += 1;
            producto.cantidad = cantidad;
        }
    }
}

function crearDetalleOrden(indice, cantidad, producto, descripcion) {
    let totalAux = parseFloat(producto.precio * cantidad).toFixed(2);

    return {
        "indice": indice,
        "id": -1,
        "cantidad": cantidad,
        "impuestoServicio": '',
        "impuesto": producto.impuesto,
        "precio_unidad": producto.precio,
        "total": parseFloat(totalAux).toFixed(2),
        "observacion": descripcion,
        //"tipo": tipos[tipoSeleccionado].codigo,
        "tipo": producto.tipoProducto,
        "tipoComanda": producto.tipoComanda,
        "cantidad_preparada": 0,
        "cantidad_pagada": 0,
        "objRowAux": null,
        "producto": producto,
        "extras": extrasDetalleAux,
        "nueva": 1
    };
}

function validarVisibilidadBotonesGestion() {

    if (ordenGestion.nueva) {
        $('#btnActualizarOrden').fadeOut();
        if (ordenGestion.mesa != -1) {
            $('#btnPago').fadeOut();
        } else {
            $('#btnPago').fadeIn();
        }
        $('#btnIniciarOrden').fadeIn();
        $('#contRecargarOrden').fadeOut();
    } else {
        if (cambiosPendientes) {
            $('#btnActualizarOrden').fadeIn();
            $('#btnPago').fadeOut();
            $('#btnIniciarOrden').fadeOut();
        } else {
            $('#btnPago').fadeIn();
            $('#btnActualizarOrden').fadeOut();
            $('#btnIniciarOrden').fadeOut();
        }
        $('#contRecargarOrden').fadeIn();
    }


}

function actualizarOrden() {
    let cards = '';
    let contador = 0;
    let subTotal = 0;
    let iva = 0;
    let total = 0;
    $('#txt-id-cliente').val("");


    detalles.forEach(d => {
        if (ordenGestion.mesa != null && ordenGestion.mesa != -1) {
            d.impuestoServicio = 'S';
        } else {
            d.impuestoServicio = 'N';
        }
    });


    detalles.forEach(detalle => {
        let totalExtrasAux = 0;
        let textoExtras = "";

        // Calcular el total de los extras y construir el texto de extras
        detalle.extras.forEach(extra => {
            totalExtrasAux += detalle.cantidad * parseFloat(extra.precio);
            textoExtras += `${extra.descripcion} ${(extra.precio > 0 ? currencyCRFormat(detalle.cantidad * parseFloat(extra.precio)) : "")} </br>`;
        });

        let totalAux = detalle.cantidad * parseFloat(detalle.precio_unidad) + totalExtrasAux;
        let montoIvaLinea = 0;
        let montoLineaSinIva = totalAux;

        // Calcular el monto sin IVA y el IVA de la línea
        if (detalle.impuesto > 0 && sucursalFacturaIva) {
            montoLineaSinIva = totalAux / (1 + parseFloat(`0.${detalle.impuesto}`));
            montoIvaLinea = totalAux - montoLineaSinIva;
        }

        // Aplicar el impuesto de servicio si corresponde
        if (detalle.impuestoServicio === 'S') {
            let impuestoMesa = montoLineaSinIva * 0.10;
            montoLineaSinIva += impuestoMesa;
            if (detalle.impuesto > 0 && sucursalFacturaIva) {
                montoIvaLinea = (montoLineaSinIva) * (parseFloat(`0.${detalle.impuesto}`));

            }
        }

        detalle.total = montoLineaSinIva + montoIvaLinea;
        detalle.montoIva = montoIvaLinea;
        detalle.subTotal = montoLineaSinIva;

        cards += generarHTMLProductoOrden(contador, detalle.producto.nombre, parseFloat(detalle.producto.precio).toFixed(2), detalle.cantidad,
            parseFloat(detalle.total).toFixed(2), detalle.producto.codigo, detalle.impuestoServicio, totalExtrasAux, textoExtras, detalle.cantidad_preparada,
            detalle.cantidad_pagada);
        contador++;
    });


    cargarDetallesSeleccionados();

    detalles.forEach(detalle => {
        if (sucursalFacturaIva) {

            let detalleSelAux;
            if (ordenGestion.nueva) {
                detalleSelAux = detalles.find(det => det.indice == detalle.indice);
            } else {
                detalleSelAux = detalles.find(det => det.id == detalle.id);
            }

            if (detalleSelAux) {
                total += detalleSelAux.total;
            } else {
                total += detalle.total;
            }
        } else {
            total += detalle.total;
        }

        iva += detalle.montoIva;
        subTotal += detalle.subTotal;

    });

    let descuentoAplicado = 0;

    ordenGestion.subTotal = subTotal;

    if (infoEnvio.incluye_envio) {
        total += parseFloat(ordenGestion.envio);
    }

    ordenGestion.total = total;

    // Actualizar los valores en el DOM
    $('#txt-cliente').val(ordenGestion.cliente);
    $('#select_mesa').val(ordenGestion.mesa ?? -1);


    $('#txt-subtotal-pagar').html(`SubTotal: ${(ordenGestion.subTotal).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    $('#txt-mto-envio_mdl').html(infoEnvio.incluye_envio ? `Envío: ${(parseFloat(ordenGestion.envio)).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}` : "Envío: No aplica");
    $('#txt-total-pagar').html(`Total Orden: ${(ordenGestion.total).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    $('#txt-mto-pagado_mdl').html(`Monto Pagado : ${(ordenGestion.mto_pagado).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    $('#txt-total-pagar_mdl').html(`Total: ${(ordenGestion.total).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);

    $(contenedores.get("orden")).html(cards);

    // Actualizar el botón de Factura Electrónica
    $('#btn_fe').html(`<i class="fas fa-pay" aria-hidden="true"></i> Factura Electrónica : ${infoFE.incluyeFE ? 'SÍ' : 'NO'}`);

    validarVisibilidadBotonesGestion();
    mtoDescuentoGen = descuentoAplicado;
}


function generarHTMLProductoOrden(indice, detalle, precio, cantidad, total, codigo, impuestoServicio = "N", totalExtrasAux,
    textoExtras, cantidad_preparada, cantidad_pagada) {
    const pendiente = cantidad - cantidad_preparada;
    var icono = "fas fa-box text-secondary";
    if (impuestoServicio == "S") {
        icono = "fas fa-utensils text-secondary";
    }

    let texto = `<tr style="border-bottom: 1px solid grey;">
                    <td> 
                    <small>
                        ${detalle}
                        <div class="input-group w-auto justify-content-center align-items-center" 
                        style="padding: 0px !important; display: block!important; margin-top:2px; margin-bottom:2px;">`;

    // Verificar si el botón de disminución debe estar deshabilitado
    const botonMenosDisabled = cantidad_pagada >= cantidad ? "disabled" : "";

    if (pendiente > 0) {
        texto += `<input type="button" value="-" 
                            class="button-minus border rounded-circle icon-shape icon-sm mx-1" 
                            data-field="quantity" onclick="agregarDetalleInpt(${indice},'${codigo}',false)" ${botonMenosDisabled}>`;
    }

    texto += `<input type="number" step="1" min="1" value="${cantidad}" readonly
                            name="quantity" class="quantity-field border-0 text-center w-25"
                            style="width:28%!important;">`;

    if (pendiente > 0) {
        texto += `<input type="button" value="+" class="button-plus border rounded-circle icon-shape icon-sm"
                            data-field="quantity" onclick="agregarDetalleInpt(${indice},'${codigo}',true)">`;
    }

    texto += `</div>
                        
                        <p style="line-height: 1.5;">
                        Precio : ${currencyCRFormat(precio)}
                        <br>
                        ${totalExtrasAux > 0 ? "Extras : " + currencyCRFormat(totalExtrasAux) + "<br>" : ""}
                        Total : ${currencyCRFormat(total)}
                        <br>
                        ${cantidad_pagada > 0 ? `<span class="badge badge-success">Pagado: ${cantidad_pagada}</span>` : ""}
                        </small>
                    </td>
    
                    <td>
                        <p style="line-height: 1.5;"><small>${textoExtras}</small></p>
                    </td>
    
                    <td>
                        <div class="row" style="padding: 0px !important;">
                            <div class="col-sm-12 col-md-12 col-lg-12">
                                <div class="input-group w-auto justify-content-center align-items-center">
                                    <div class="row">
                                        ${pendiente > 0
            ? `<div class="col-sm-6 col-md-6 col-lg-6 justify-content-center align-items-center">
                                                <button type="button" class="btn btn-danger px-2" 
                                                    onclick="eliminarLineaDetalleOrden(${indice})" 
                                                    ${cantidad_pagada > 0 ? "disabled" : ""}>
                                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <div class="col-sm-6 col-md-6 col-lg-6 justify-content-center align-items-center">
                                                <button type="button" class="btn btn-warning px-2" 
                                                    title="Agregar observación a la orden"  ${cantidad_pagada > 0 ? "disabled" : ""}
                                                    onclick="agregarDescripcionDetalle(${indice})">
                                                    <i class="fas fa-clipboard" aria-hidden="true"></i>
                                                </button>
                                            </div>`
            : `<p class="text-muted">Todo preparado</p>`}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>`;

    return texto;
}


function reducirCantidadProducto(codigo) {
    var producto = buscarProductoCodigo(codigo);
    if (producto !== undefined) {
        let cantidad = parseInt(producto.cantidad);
        if (cantidad > 0) {
            cantidad -= 1;
            producto.cantidad = cantidad;
        }
    }

    generarProductos();
}


function generarHTMLOpcion(valor, detalle) {
    return `<option value='${valor}'>${detalle}</option>`;
}

function limpiarOrden() {
    ordenGestion = {
        "id": null,
        "cliente": "",
        "nueva": true,
        "total": 0,
        "envio": 0,
        "subTotal": 0,
        "mesa": -1,
        "numero_orden": "",
        "mto_pagado": 0,
        "pagado": false,
        "codigo_descuento": null
    };
    $('#monto_sinpe').val(""); // Supongo que txt-sinpe es el campo para el pago con SINPE
    $('#monto_tarjeta').val(""); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
    $('#monto_efectivo').val("");
    $("#txt-cliente").val("");
    $('#select_mesa').val(-1);
    reiniciarCantidadesProductos();
    detalles = [];
    cambiosPendientes = false;
    actualizarOrden();
    generarProductos();
    guardandoOrden = false;
    $('#btnIniciarOrden').fadeIn();
    $('#btnActualizarOrden').fadeOut();
    $('#infoHeaderOrden').html("Orden Nueva");
}

function reiniciarCantidadesProductos() {
    detalles.forEach(detalle => {
        tipos.forEach(tipo => {
            tipo.categorias.forEach(categoria => {
                categoria.productos.forEach(producto => {
                    if (producto.id == detalle.producto.id) {
                        producto.cantidad = producto.cantidad + detalle.cantidad;
                    }
                })
            })
        });
    });

}


function eliminarCodDescuento() {
    if (ordenGestion.codigo_descuento == null) {
        showError("No hay código de descuento cargado");
        return;
    }
    $('#txt_codigo_descuento').val("");
    ordenGestion.codigo_descuento = null;
    actualizarOrden();
    showSuccess("Descuento eliminado");
}

function validarCodDescuento() {

    if (detalles.length < 1) {
        $('#txt_codigo_descuento').val("");
        showError('Debe seleccionar los productos para aplicar el código');
        return false;
    }

    var codigo = $('#txt_codigo_descuento').val();
    if (codigo == "" || codigo == null || codigo == undefined) {
        $('#txt_codigo_descuento').val("");
        showError("Debe incluir el código a verificar");
        return;
    }

    $.ajax({
        url: `${base_path}/facturacion/pos/validarCodDescuento`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            codigo_descuento: codigo
        }
    }).done(function (res) {
        if (!res['estado']) {
            $('#txt_codigo_descuento').val("");
            showError(res['mensaje']);
            return;
        } else {
            ordenGestion.codigo_descuento = res['datos'];
            actualizarOrden();
            showSuccess("Descuento encontrado");
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");

    });
}


function validarFormularioOrden() {

    if (detalles.length < 1) {
        swal('Datos incompletos', 'Debe seleccionar los productos para generar la orden.', 'error');
        return false;
    }

    return true;
}

function abrirModalPago() {
    
    if (ordenGestion.nueva) {
        if (ordenGestion.mesa != -1) {
            showError("Debe iniciar la orden primero en un pedido que no es para llevar.");
            return;
        } 

    }

    if (!ordenGestion.nueva) {
     
        if (cambiosPendientes) {
            showError("Existen modificaciones sin guardar. Por favor, guarde los cambios antes de continuar.");
            return;
        }
    }

    if (detalles.length === 0) {
        showError("Debe agregar al menos un detalle a la orden.");
        return false;
    }


    $('#monto_sinpe_mdl').val(""); // Supongo que txt-sinpe es el campo para el pago con SINPE
    $('#monto_tarjeta_mdl').val(""); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
    $('#monto_efectivo_mdl').val("");
    $('#nombreCliente').val(ordenGestion.cliente ?? "");
    cargarDetallesDividirCuentas(detalles);
    $('#mdl-pago').modal("show");
}

function verificarAbrirModalPago() {
    if (detalles.length < 1) {
        showError('Debe seleccionar los productos para facturar');
        return false;
    }
    var cliente = $('#nombreCliente').val();
    if (cliente == "" || cliente == null || cliente == undefined) {
        $('#nombreCliente').focus();
        showError("Debe indicar el nombre del cliente");
        return;
    }

    pago_sinpe = $('#monto_sinpe').val();
    pago_tarjeta = $('#monto_tarjeta').val();
    pago_efectivo = $('#monto_efectivo').val();

    if (isNaN(pago_sinpe)) {
        $('#monto_sinpe').val("0");
        pago_sinpe = 0;
    }

    if (isNaN(pago_tarjeta)) {
        $('#monto_tarjeta').val("0");
        pago_tarjeta = 0;
    }
    if (isNaN(pago_efectivo)) {
        $('#monto_efectivo').val("0");
        pago_efectivo = 0;
    }

    var textoPago = "Espere mientras se procesa la factura";

    if (pago_tarjeta > 0) {
        textoPago = "Esperando información de pago mediante tarjeta";
    }

    $('#texto_pago_aux').html(textoPago);
    if (ordenGestion.nueva) {
        procesarPagoInmediato(pago_sinpe, pago_efectivo, pago_tarjeta);
    } else {
        realizarPagoDividido(pago_sinpe, pago_efectivo, pago_tarjeta)
    }


}

function procesarPagoMixto() {
    verificarAbrirModalPago($('#monto_sinpe').val(), $('#monto_tarjeta').val(), $('#monto_efectivo').val());
}

function verificarAbrirModalPagoEfectivo() {

    cargarDetallesSeleccionados();

    $('#monto_efectivo').val(totalSeleccionado + parseFloat(ordenGestion.envio));
    $('#monto_tarjeta').val("0");
    $('#monto_sinpe').val("0");

    verificarAbrirModalPago();
}

function verificarAbrirModalPagoTarjeta() {
    cargarDetallesSeleccionados();
    $('#monto_tarjeta').val(totalSeleccionado + parseFloat(ordenGestion.envio));
    $('#monto_sinpe').val("0");
    $('#monto_efectivo').val("0");

    verificarAbrirModalPago();
}

function verificarAbrirModalPagoSinpe() {
    cargarDetallesSeleccionados();
    $('#monto_sinpe').val(totalSeleccionado + parseFloat(ordenGestion.envio));
    $('#monto_efectivo').val("0");
    $('#monto_tarjeta').val("0");

    verificarAbrirModalPago();
}

function procesarPagoInmediato(mto_sinpe, mto_efectivo, mto_tarjeta) {
    var sumaPagos = parseFloat(mto_sinpe) + parseFloat(mto_tarjeta) + parseFloat(mto_efectivo);
    if (sumaPagos == (parseFloat(totalSeleccionado) + parseFloat(ordenGestion.envio))) {

        $('#mdl-loader-pago').modal("show");
        ordenGestion.cliente = $('#nombreCliente').val();
        $.ajax({
            url: `${base_path}/facturacion/pos/crearFactura`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                orden: ordenGestion,
                envio: infoEnvio,
                infoFE: infoFE,
                detalles: detalles,
                mto_sinpe: mto_sinpe,
                mto_efectivo: mto_efectivo,
                mto_tarjeta: mto_tarjeta
            }
        }).done(function (res) {
            if (!res['estado']) {
                showError(res['mensaje']);
                $('#mdl-loader-pago').modal("hide");
                return;
            } else {
                id = res['datos'];
                imprimirTicket(id);
                showSuccess("Orden realizada!");
            }
            $('#mdl-loader-pago').modal("hide");
            location.reload();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            showError("Algo salió mal");
            $('#mdl-loader-pago').modal("hide");
        }).always(function () {
            $('#mdl-loader-pago').modal("hide");
        });

    } else {
        showError("La suma de los pagos no coincide con el total de la orden.");
        return;
    }
}

function recargarOrdenes() {
    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/facturacion/pos/recargarOrdenes`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(res['mensaje']);
            return;
        }
        generarHTMLOrdenes(response['datos']);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    }).always(function () {
        $('#loader').fadeOut();
    });
}

function imprimirTicket(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}

function generarHTMLOrdenes(ordenes) {
    var texto = "";
    var msjTrackingWhatsp = "";
    var contactoAux = "";
    ordenes.forEach(orden => {
        contactoAux = "";
        msjTrackingWhatsp = "";
        var lineas = "";
        var tablaDetalles = "";
        orden.detalles.forEach(detalle => {
            tablaDetalles = tablaDetalles + `<tr style='border-bottom: 1px solid grey;'>
                                                <td class='text-center' >
                                                    ${detalle.nombre_producto}
                                                </td> 
                                                <td class='text-center'>
                                                    ${detalle.cantidad}
                                                </td>
                                                <td class='text-center'>
                                                    ${detalle.total ?? 0}
                                                </td>
                                                <td class='text-center'>
                                                    <input type='checkbox' id='elemento${detalle.id ?? 0}' class='elemento' value='${detalle.id ?? 0}'> 
                                                </td>
                                            </tr>`;
        });

        if (orden.entrega != null) {
            contactoAux = orden.entrega.contacto;
        }
        msjTrackingWhatsp = generarMensajeTrackingWhatsApp(orden.nombre_cliente,
            orden.numero_orden, contactoAux,
            `${base_path}/tracking/orden/${orden.idOrdenEnc ?? ''}`);

        lineas.slice(0, -1);
        texto = texto +
            `<tr style="border-bottom: 1px solid grey;">
                <td class="text-center"  onclick="cargarOrdenGestion(${orden.id})" style="cursor:pointer; text-decoration : underline; ">
                   <i class="fas fa-cog" aria-hidden="true"> </i> ${orden.numero_orden}
                </td> 
                 <td class="text-center">
                    ${orden.numero_mesa ?? 'PARA LLEVAR'}
                </td>
                <td class="text-center">
                    ${orden.fecha_inicio}
                </td>
                <td class="text-center">
                    ${orden.nombre_cliente ?? ""}
                </td>
                 <td class="text-center">
                ${orden.estadoOrden ?? ""}
            </td>
             <td class="text-center">
                ${orden.pagado == 1 ? "Pagado" : "Pendiente de Pagar"}
            </td>
                <td class="text-center">
                ${orden.total_con_descuento ?? 0}
            </td> 
             <td class="text-center">
                ${orden.mto_pagado ?? 0}
            </td>
                <td class="text-center">
                ${(orden.total_con_descuento ?? 0) - (orden.mto_pagado ?? 0)}
            </td> 
               
           
            <td class="text-center" style="cursor:pointer; text-decoration : underline;" onclick="imprimirTicket( ${orden.id})"> 
                <i class="fas fa-print" aria-hidden="true"> </i> Imprimir Tiquete
            </td>
           `;
        /* <td class="text-center"> 
             <a href="${msjTrackingWhatsp ?? ""}" style="display: block;width: 100%;" target="_blank">
                 <i class="fas fa-barcode" aria-hidden="true"> </i> Envíar link de rastreo de orden
             </a>
         </td>*/

        texto = texto + `</tr>`;
    });

    $('#tbody-ordenes').html(texto);
    $('#mdl-ordenes').modal('show');
}

function generarMensajeTrackingWhatsApp(nombreUsuario, numeroOrden, telefono, url) {
    // Formatear el mensaje con los datos proporcionados
    var mensaje = "Hola " + nombreUsuario + ", te informamos que puedes rastrear el estado de tú orden " + numeroOrden + " siguiendo el siguiente link : " + url;

    // Formatear el enlace con el mensaje y el número de teléfono del usuario
    var enlaceWhatsApp = "https://api.whatsapp.com/send?phone=506" + telefono + "&text=" + encodeURIComponent(mensaje);

    // Retornar el enlace generado
    return enlaceWhatsApp;
}

function cerrarMdlOrdenes() {
    $("#mdl-ordenes").modal("hide");
}

function abrirModalCerrarCaja() {
    cargarCajaPrevia();
    $("#mdl-cerrar-caja").modal("show");
}

function cerrarModalCerrarCaja() {
    $("#mdl-cerrar-caja").modal("hide");
}

function calcularMontoEfectivo() {

    let efectivo_mon_5 = $('#efectivo_mon_5').val();
    let efectivo_mon_10 = $('#efectivo_mon_10').val();
    let efectivo_mon_25 = $('#efectivo_mon_25').val();
    let efectivo_mon_50 = $('#efectivo_mon_50').val();
    let efectivo_mon_100 = $('#efectivo_mon_100').val();
    let efectivo_mon_500 = $('#efectivo_mon_500').val();

    let efectivo_bill_1000 = $('#efectivo_bill_1000').val();
    let efectivo_bill_2000 = $('#efectivo_bill_2000').val();
    let efectivo_bill_5000 = $('#efectivo_bill_5000').val();
    let efectivo_bill_10000 = $('#efectivo_bill_10000').val();
    let efectivo_bill_20000 = $('#efectivo_bill_20000').val();
    let efectivo_bill_50000 = $('#efectivo_bill_50000').val();

    let monto_5 = parseFloat(efectivo_mon_5) * parseFloat(5);
    let monto_10 = parseFloat(efectivo_mon_10) * parseFloat(10);
    let monto_25 = parseFloat(efectivo_mon_25) * parseFloat(25);
    let monto_50 = parseFloat(efectivo_mon_50) * parseFloat(50);
    let monto_100 = parseFloat(efectivo_mon_100) * parseFloat(100);
    let monto_500 = parseFloat(efectivo_mon_500) * parseFloat(500);

    let monto_1000 = parseFloat(efectivo_bill_1000) * parseFloat(1000);
    let monto_2000 = parseFloat(efectivo_bill_2000) * parseFloat(2000);
    let monto_5000 = parseFloat(efectivo_bill_5000) * parseFloat(5000);
    let monto_10000 = parseFloat(efectivo_bill_10000) * parseFloat(10000);
    let monto_20000 = parseFloat(efectivo_bill_20000) * parseFloat(20000);
    let monto_50000 = parseFloat(efectivo_bill_50000) * parseFloat(50000);

    let montoTotal = parseFloat(monto_5) + parseFloat(monto_10) + parseFloat(monto_25) +
        parseFloat(monto_50) + parseFloat(monto_100) + parseFloat(monto_500) +
        parseFloat(monto_1000) + parseFloat(monto_2000) + parseFloat(monto_5000) + parseFloat(monto_10000) +
        parseFloat(monto_20000) + parseFloat(monto_50000);
    $('#monto_efectivo_cierre').val(montoTotal);

    calcularCaja();
}

function calcularCaja() {
    let efectivo = $('#monto_efectivo_cierre').val();
    let tarjeta = $('#monto_tarjeta_cierre').val();
    let sinpe = $('#monto_sinpe_cierre').val();

    if (efectivo == "") {
        efectivo = 0;
    }
    if (tarjeta == '') {
        tarjeta = 0;

    }
    if (sinpe == '') {
        sinpe = 0;
    }

    let subtotal = parseFloat(efectivo) + parseFloat(tarjeta) + parseFloat(sinpe);

    let total = parseFloat(subtotal);
    $('#totalCaja').val(subtotal);
    total = parseFloat(total).toFixed(2);
    subtotal = parseFloat(subtotal).toFixed(2);
    efectivo = parseFloat(efectivo).toFixed(2);
    tarjeta = parseFloat(tarjeta).toFixed(2);
    sinpe = parseFloat(sinpe).toFixed(2);

    if (parseFloat(total) >= 0) {
        $('#monto_total_lbl').html("CRC <strong>" + total.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
        $('#monto_efectivo').val(total);
    }
    if (parseFloat(efectivo) >= 0) {
        $('#monto_efectivo_lbl').html("CRC <strong>" + efectivo.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
    }
    if (parseFloat(tarjeta) >= 0) {
        $('#monto_tarjetas_lbl').html("CRC <strong>" + tarjeta.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
    }
    if (parseFloat(sinpe) >= 0) {
        $('#monto_sinpe_lbl').html("CRC <strong>" + sinpe.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
    }
    if (parseFloat(subtotal) >= 0) {
        $('#monto_subtotal_lbl').html("CRC <strong>" + subtotal.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
    }


}

function cargarCajaPrevia() {
    $.ajax({
        url: `${base_path}/caja/cajaPrevia`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        var datos = response['datos'];
        $('#monto_efectivo_lbl').html("CRC <strong>" + parseFloat(datos.total_efectivo).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
        $('#monto_tarjetas_lbl').html("CRC <strong>" + parseFloat(datos.total_tarjeta).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
        $('#monto_sinpe_lbl').html("CRC <strong>" + parseFloat(datos.total_sinpe).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
        var total = parseFloat(datos.total_efectivo) + parseFloat(datos.total_tarjeta) + parseFloat(datos.total_sinpe);
        $('#monto_total_lbl').html("CRC <strong>" + parseFloat(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
    }).fail(function (jqXHR, textStatus, errorThrown) { });
}

function enterDescuento(event) {
    if (event.keyCode === 13) {
        validarCodDescuento();
    }
}

function enterCampoPago(event) {
    if (event.keyCode === 13) {
        var pago_sinpe = parseFloat($('#monto_sinpe').val()); // Supongo que txt-sinpe es el campo para el pago con SINPE
        var pago_tarjeta = parseFloat($('#monto_tarjeta').val()); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
        var pago_efectivo = parseFloat($('#monto_efectivo').val()); // Supongo que txt-efectivo es el campo para el pago en efectivo
        var cliente = $('#txt-cliente').val();
        if (cliente == undefined || cliente == null || cliente == "") {
            $('#txt-cliente').focus();
            return;
        }
        if (isNaN(pago_tarjeta)) {
            $('#monto_tarjeta').val("0");
            $('#monto_tarjeta').focus();
            return;
        }

        if (isNaN(pago_efectivo)) {
            $('#monto_efectivo').val("0");
            $('#monto_efectivo').focus();
            return;
        }
        if (isNaN(pago_sinpe)) {
            $('#monto_sinpe').val("0");
            $('#monto_sinpe').focus();
            return;
        }

        $('#btnPago').focus();
        return;
    }
}

function changeNombreCliente(nombre) {
    ordenGestion.cliente = nombre;
    cambiosPendientes = true;
    validarVisibilidadBotonesGestion();
}

function abrirModalEnvio() {
    cargarInfoEnvio();
    $("#mdl_envio").modal("show");
}

function abrirModalFE() {
    cargarInfoFE();
    $("#mdl_fe").modal("show");
}

function cargarInfoEnvio() {
    $('#mdl_contacto_entrega').val(infoEnvio.contacto);
    $('#mdl_precio_envio').val(infoEnvio.precio);
    $('#mdl_precio_envio').val(infoEnvio.precio);
    $('#incluyeEnvio').prop("checked", infoEnvio.incluye_envio);
    $("#mdl_lugar_entrega").val(infoEnvio.descripcion_lugar);
    $("#mdl_lugar_entrega_maps").val(infoEnvio.descripcion_lugar_maps);
}

function cargarInfoFE() {
    $('#info_nombre_fe').val(infoFE.info_nombre_fe);
    $('#info_ced_fe').val(infoFE.info_ced_fe);
    $('#info_correo_fe').val(infoFE.info_correo_fe);
    $('#incluyeFE').prop("checked", infoFE.incluyeFE);
}

function guardarInfoEnvio() {
    infoEnvio.contacto = $('#mdl_contacto_entrega').val();
    infoEnvio.precio = $('#mdl_precio_envio').val();
    infoEnvio.incluye_envio = $('#incluyeEnvio').prop("checked");
    infoEnvio.descripcion_lugar = $("#mdl_lugar_entrega").val();
    infoEnvio.descripcion_lugar_maps = $("#mdl_lugar_entrega_maps").val();
    ordenGestion.envio = infoEnvio.precio;
    ordenGestion.mesa = $('#incluyeEnvio').prop("checked") ? -1 : ordenGestion.mesa;
    cerrarModalEnvio();
    actualizarOrden();
    iziToast.success({
        title: 'Información de envío',
        message: 'Se actualizo la información de envío',
        position: 'topRight'
    });
}


function guardarInfoFE() {
    infoFE.info_nombre_fe = $('#info_nombre_fe').val();
    infoFE.info_ced_fe = $('#info_ced_fe').val();
    infoFE.incluyeFE = $('#incluyeFE').prop("checked");
    infoFE.info_correo_fe = $("#info_correo_fe").val();

    cerrarModalFe();
    actualizarOrden();
    iziToast.success({
        title: 'Información de facturación electrónica',
        message: 'Se actualizo la información de  facturación electrónica',
        position: 'topRight'
    });
}

function cerrarModalEnvio() {
    $("#mdl_envio").modal("hide");
}

function cerrarModalFe() {
    $("#mdl_fe").modal("hide");
}

function cerrarCaja() {
    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/caja/cerrarcaja`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            cajaAbierta = true;
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se cerró la caja correctamente");
        cajaAbierta = false;
        cerrarModalCerrarCaja();
        location.reload();

    }).fail(function (jqXHR, textStatus, errorThrown) {
        cajaAbierta = true;
        showError("Algo salió mal");
    }).always(function () {
        $('#loader').fadeOut();
    });
    $('#loader').fadeOut();

}

function abrirCaja() {

    $.ajax({
        url: `${base_path}/caja/abrirCaja`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            cajaAbierta = false;
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se abrio la caja correctamente");
        cajaAbierta = true;
        validarCajaAbierta();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        cajaAbierta = false;
        showError("Algo salió mal");
    });
}


function cargarOrdenGestion(idOrden) {

    $.ajax({
        url: `${base_path}/facturacion/pos/cargarOrdenGestion`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idOrden: idOrden
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }

        ordenGestion = transformarEncabezado(response['datos']);
        detalles = transformarDetalles(response['datos']);
        cambiosPendientes = false;
        $('#btnIniciarOrden').fadeOut();
        $('#btnActualizarOrden').fadeIn();
        $('#mdl-ordenes').modal('hide');
        $('#infoHeaderOrden').html("Orden : " + ordenGestion.numero_orden);
        actualizarOrden();

    }).fail(function (jqXHR, textStatus, errorThrown) {
        cajaAbierta = false;
        showError("Algo salió mal");

    });
}

// Función para transformar solo el encabezado
function transformarEncabezado(phpObject) {
    return {
        id: phpObject.id,
        subtotal: phpObject.subtotal,
        total: phpObject.total,
        cliente: phpObject.nombre_cliente,
        codigo_descuento: phpObject.codigo_descuento || '',
        envio: phpObject.envio,
        nueva: phpObject.nueva,
        mesa: phpObject.mesa,
        numero_orden: phpObject.numero_orden,
        mto_pagado: phpObject.mto_pagado,
        pagado: phpObject.pagado == 1
    };
}

// Función para transformar solo los detalles
function transformarDetalles(phpObject) {
    return phpObject.detalles.map(detalle => ({
        indice: 0,
        id: detalle.id,
        cantidad: detalle.cantidad,
        impuestoServicio: detalle.impuestoServicio || 'N',
        impuesto: detalle.impuesto.toString(),
        precio_unidad: detalle.precio_unidad.toString(),
        total: detalle.total,
        observacion: detalle.observacion || '',
        tipo: detalle.tipo,
        tipoComanda: detalle.tipoComanda || '',
        cantidad_preparada: detalle.cantidad_preparada,
        cantidad_pagada: detalle.cantidad_pagada,
        nueva: 0,
        objRowAux: null,
        producto: {
            id: detalle.producto.id,
            nombre: detalle.producto.nombre,
            impuesto: detalle.producto.impuesto,
            precio: detalle.producto.precio,
            codigo: detalle.producto.codigo,
            tipoComanda: detalle.producto.tipo_comanda || '',
            cantidad: detalle.producto.cantidad ? detalle.producto.cantidad : '-1',
            cantidad_original: detalle.producto.cantidad_original ? detalle.producto.cantidad_original : '-1',
            tipoProducto: detalle.producto.tipoProducto || detalle.tipo,
            extras: transformarExtras(detalle.producto.extras),
            es_promocion: detalle.producto.es_promocion || 'N'
        },
        extras: detalle.extras.map(extra => ({
            id: extra.extra,
            descripcion: extra.descripcion_extra,
            precio: extra.total / detalle.cantidad,
            materia_prima: extra.materia_prima ? extra.materia_prima : '',
            cant_mp: extra.cant_mp ? extra.cant_mp : '',
            grupo: extra.dsc_grupo,
            requerido: extra.requerido,
            tipo_producto: extra.tipo_producto,
            idProd: extra.id_producto,
            seleccionado: false
        }))
    }));
}

// Función para transformar la estructura de los extras dentro del producto
function transformarExtras(extras) {
    return extras.map(grupo => ({
        dsc_grupo: grupo.grupo,
        requerido: grupo.requerido.toString(),
        multiple: grupo.multiple.toString(),
        extras: grupo.extras.map(extra => ({
            id: extra.id.toString(),
            descripcion: extra.descripcion,
            precio: extra.precio.toString(),
            materia_prima: extra.materia_prima ? extra.materia_prima.toString() : '',
            cant_mp: extra.cant_mp ? extra.cant_mp.toString() : '',
            grupo: extra.dsc_grupo,
            requerido: extra.es_requerido.toString(),
            tipo_producto: 'RE',
            idProd: extra.producto.toString(),
            seleccionado: false
        }))
    }));
}

function cambiarMesa() {
    const mesaSeleccionada = $('#select_mesa').val();

    // Verificar si incluye envío
    if (infoEnvio.incluye_envio) {
        $('#select_mesa').val(-1);
        ordenGestion.mesa = -1;
        showError("No se puede asignar una mesa a una orden PARA LLEVAR (Debes deseleccionar la opción de envío)");
        return;
    }

    // Validar si el monto pagado es mayor a 0
    if (ordenGestion.mto_pagado > 0) {
        // Si la mesa actual es -1 o null, no permitir cambiar a otra mesa
        if ((ordenGestion.mesa === -1 || ordenGestion.mesa === null) && mesaSeleccionada != -1) {
            $('#select_mesa').val(ordenGestion.mesa);
            showError("No se puede asignar una mesa ya que la orden está configurada PARA LLEVAR y ya ha sido pagada.");
            return;
        }
        // Si la mesa actual es diferente de -1, no permitir cambiar a PARA LLEVAR
        if (ordenGestion.mesa != -1 && mesaSeleccionada == -1) {
            $('#select_mesa').val(ordenGestion.mesa);
            showError("No se puede cambiar la orden a PARA LLEVAR ya que la mesa ya ha sido asignada y la orden está pagada.");
            return;
        }
    }

    $('#loader').fadeIn();
    
    // Asignar la nueva mesa
    ordenGestion.mesa = mesaSeleccionada;
    cambiosPendientes = true;
    actualizarOrden();
    $('#loader').fadeOut();
}


function iniciarOrden() {
    if (ordenGestion.pagado != 0) {
        showError("Error");
        return false;
    }
    let nombreCliente = document.getElementById('txt-cliente').value;

    if (detalles.length === 0) {
        showError("Debe agregar al menos un detalle a la orden.");
        return false;
    }

    if (nombreCliente.trim() === "") {
        showError("Debe escribir el nombre del cliente.");
        return false;
    }

    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/facturacion/pos/iniciarOrden`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            orden: ordenGestion,
            detalles: detalles
        }
    }).done(function (res) {
        if (!res['estado']) {
            showError(res['mensaje']);
        } else {
            id = res['datos'];
            showSuccess(res['mensaje']);
            cargarOrdenGestion(id);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    }).always(function () {
        $('#loader').fadeOut();
    });
}

function cerrarMdlPago() {
    $('#mdl-pago').modal("hide");
}

function actualizarOrdenGestion() {
    if (ordenGestion.pagado != 0) {
        showError("Error");
        return false;
    }

    if (detalles.length === 0) {
        showError("Debe agregar al menos un detalle a la orden.");
        return false;
    }

    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/facturacion/pos/actualizarOrden`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            orden: ordenGestion,
            detalles: detalles
        }
    }).done(function (res) {
        id = res['datos'];
        if (!res['estado']) {
            showError(res['mensaje']);
            return;
        } else {
            showSuccess(res['mensaje']);
            cargarOrdenGestion(ordenGestion.id);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    }).always(function () {
        $('#loader').fadeOut();
    });
}

function cargarDetallesDividirCuentas() {
    const tabla = document.getElementById('tabla-detalles-dividir-cuentas');
    tabla.innerHTML = '';

    detalles.forEach((detalle, index) => {
        // Verificar si los checkboxes deben ser deshabilitados
        const cantPendientePagar = detalle.cantidad - (detalle.cantidad_pagada ?? 0);
        const checkboxDisabled = (ordenGestion.nueva || cantPendientePagar < 1) ? 'disabled' : '';
        const checkboxChecked = (ordenGestion.nueva || cantPendientePagar > 0) ? 'checked' : '';

        let totalExtrasAux = 0;
        detalle.extras.forEach(extra => {
            totalExtrasAux = totalExtrasAux + parseFloat(extra.precio);
        });

        let precioExtras = parseFloat(detalle.precio_unidad) + parseFloat(totalExtrasAux);

        let precioFinal = (detalle.cantidad * parseFloat(precioExtras));
        const row = `<tr>
                        <td><input type="checkbox" class="detalle-checkbox" data-id="${detalle.id}" ${checkboxChecked} ${checkboxDisabled} onchange="actualizarOrden()" /></td>
                        <td>${detalle.producto.nombre}</td>
                        <td>${detalle.cantidad}</td>
                        <td>${(detalle.cantidad_pagada ?? 0)}</td>
                        <td>
                            <input type="number" class="form-control cantidad-a-pagar" min="0" max="${cantPendientePagar}" ${checkboxDisabled}
                                value="${cantPendientePagar}" data-precio="${precioExtras}" data-id="${detalle.id}" data-indice="${detalle.indice}"
                                onchange="actualizarOrden()" />
                        </td>
                        <td>${currencyCRFormat(precioExtras)}</td>
                        <td class="total-parcial">${currencyCRFormat(precioFinal)}</td>
                     </tr>`;
        tabla.innerHTML += row;
    });
    actualizarOrden(); // Calcular el total inicialmente
}

function seleccionarTodasLasLineas(seleccionar) {
    document.querySelectorAll('.detalle-checkbox').forEach(cb => {
        if (!cb.disabled) {
            cb.checked = seleccionar;
        }
    });
    actualizarOrden();
}

function cargarDetallesSeleccionados() {
    detallesSeleccionados = [];

    document.querySelectorAll('.detalle-checkbox:checked').forEach(cb => {
        const fila = cb.closest('tr');
        const id = cb.dataset.id;

        const indice = parseFloat(fila.querySelector('.cantidad-a-pagar').dataset.indice) || 0;
        const cantidad = parseFloat(fila.querySelector('.cantidad-a-pagar').value) || 0;
        let detalleOriginal = false;
        if (ordenGestion.nueva) {
            detalleOriginal = detalles.find(det => det.indice == indice);
        } else {
            detalleOriginal = detalles.find(det => det.id == id);
        }


        if (detalleOriginal) {
            // Crear una copia del objeto encontrado
            const detalleCopia = { ...detalleOriginal };

            const subTotalFraccion = detalleCopia.subTotal / detalleOriginal.cantidad;

            // Actualizar la cantidad en la copia
            detalleCopia.cantidad = cantidad;
            detalleCopia.subTotal = (detalleOriginal.subTotal / detalleOriginal.cantidad) * cantidad;
            detalleCopia.montoIva = (detalleOriginal.montoIva / detalleOriginal.cantidad) * cantidad;
            detalleCopia.total = (detalleOriginal.total / detalleOriginal.cantidad) * cantidad;
            detalleCopia.objRowAux = fila;
            // Añadir la copia con la cantidad actualizada a la lista de seleccionados
            detallesSeleccionados.push(detalleCopia);
        }
    });
    descuentoAplicado = 0;
    if (ordenGestion.codigo_descuento && ordenGestion.codigo_descuento.cod_general) {
        let subTotalSeleccionado = detallesSeleccionados.reduce((acc, detalle) => acc + detalle.subTotal, 0);
        if (ordenGestion.codigo_descuento.cod_general === 'DESCUENTO_PORCENTAJE') {
            descuentoAplicado = subTotalSeleccionado * (ordenGestion.codigo_descuento.descuento / 100);
        } else if (ordenGestion.codigo_descuento.cod_general === 'DESCUENTO_ABSOLUTO') {
            descuentoAplicado = ordenGestion.codigo_descuento.descuento;
        }
        $('#txt-dsc_promo').html(`<small>Descuento Aplicado : ${ordenGestion.codigo_descuento.descripcion}</small>`);
        $('#cont-dsc_promo').fadeIn(1000);

        detallesSeleccionados.forEach(detalle => {
            let porcentajeDescuento = detalle.subTotal / subTotalSeleccionado;
            let montoDescuentoLinea = porcentajeDescuento * descuentoAplicado;

            // Recalcular el submontoIva e IVA con el descuento aplicado
            let subtotalConDescuento = detalle.subTotal - montoDescuentoLinea;
            let ivaConDescuento = 0;

            if (detalle.impuesto > 0 && sucursalFacturaIva) {
                ivaConDescuento = subtotalConDescuento * parseFloat(`0.${detalle.impuesto}`);
            }

            detalle.montoIva = ivaConDescuento;
            detalle.subTotal = subtotalConDescuento;
            detalle.total = subtotalConDescuento + ivaConDescuento;

        });
    } else {
        $('#cont-dsc_promo').fadeOut(1000);
        $('#txt-dsc_promo').val("");
    }

    var totalSeleccionadoAux = 0;

    detallesSeleccionados.forEach(detalle => {
        detalle.objRowAux.querySelector('.total-parcial').innerText = currencyCRFormat(detalle.total);
        detalle.objRowAux = null;
        totalSeleccionadoAux += detalle.total;
    });


    totalSeleccionado = totalSeleccionadoAux;
    mtoDescuentoGen = descuentoAplicado;
    $('#txt-descuento-pagar_mdl').html(`Descuento: ${descuentoAplicado.toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    document.getElementById('txt-total-seleccionado').innerText = `Total Seleccionado a Pagar : ${currencyCRFormat(totalSeleccionadoAux)}`;
}

function realizarPagoDividido(montoSinpe, montoEfectivo, montoTarjeta) {
    if (!ordenGestion.nueva) {
        if (cambiosPendientes) {
            showError("Existen modificaciones sin guardar. Por favor, guarde los cambios antes de continuar.");
            return;
        }
    }

    var sumaPagos = parseFloat(montoSinpe) + parseFloat(montoTarjeta) + parseFloat(montoEfectivo);

    if (sumaPagos == (totalSeleccionado + parseFloat(ordenGestion.envio))) {
        ordenGestion.cliente = document.getElementById('nombreCliente').value;

        if (detallesSeleccionados.length === 0) {
            showError('Seleccione al menos un detalle.');
            return;
        }
        $('#mdl-loader-pago').modal("show");
        $.ajax({
            url: `${base_path}/facturacion/pos/pagarOrden`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                orden: ordenGestion,
                infoFE: infoFE,
                envio: infoEnvio,
                detalles: detallesSeleccionados,
                mto_sinpe: montoSinpe,
                mto_efectivo: montoEfectivo,
                mto_tarjeta: montoTarjeta
            }
        }).done(function (res) {
            $('#mdl-loader-pago').modal("hide");
            if (!res['estado']) {
                showError(res['mensaje']);
                return;
            } else {
                data = res['datos'];
                showSuccess("Se generó el pago");
                if (data.pago_completo) {
                    if (!data.variasFacturas) {
                        imprimirTicket(data.idOrden);
                    } else {
                        imprimirTicket(data.numFactura);
                    }
                    location.reload();
                } else {
                    imprimirTicket(data.numFactura);
                    cerrarMdlPago();
                    cargarOrdenGestion(ordenGestion.id);
                    cargarProductosPos();
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#mdl-loader-pago').modal("hide");
            showError("Algo salió mal");
        });
    } else {
        showError("La suma de los pagos no coincide con el total de la orden.");
        return;
    }
}

function recargarOrden() {

    cargarOrdenGestion(ordenGestion.id);
    showSuccess("Se recargo la orden " + ordenGestion.numero_orden);

}

function cargarProductosPos() {

    $.ajax({
        url: '${base_path}/facturacion/pos/cargarPosProductos',
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (respuesta) {

        if (!respuesta['estado']) {
            showError(respuesta.mensaje);
            return;
        }
        procesarDatosAjax(respuesta['datos']);

    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}
function procesarDatosAjax(tiposAux) {
    // Limpiar el arreglo de tipos
    tipos = [];
    productosGeneral = []; // Asegurarse de limpiar también productosGeneral

    tiposAux.forEach(tipo => {
        // Reiniciar el arreglo de categorías para cada tipo
        var categorias = [];

        // Convertir categorías a un arreglo, si es necesario
        var categoriasArray = Array.isArray(tipo.categorias) ? tipo.categorias : Object.values(tipo.categorias);
        if (Array.isArray(categoriasArray)) {
            // Recorrer las categorías dentro de cada tipo
            categoriasArray.forEach(categoria => {
                // Reiniciar el arreglo de productos para cada categoría
                var productos = [];

                // Convertir productos a un arreglo, si es necesario
                var productosArray = Array.isArray(categoria.productos) ? categoria.productos : Object.values(categoria.productos);
                if (Array.isArray(productosArray)) {
                    productosArray.forEach(producto => {
                        var extrasAux = [];

                        // Verificar si el producto es una promoción
                        if (producto.es_promocion === 'S') {
                            producto.detallesRestaurante?.forEach(dr => {
                                var extrasTransf = transformarExtras(dr.extras || [], 'R', dr.id_producto || '');
                                extrasAux = extrasAux.concat(extrasTransf);
                            });

                            producto.detallesExternos?.forEach(de => {
                                var extrasTransf = transformarExtras(de.extras || [], 'E', de.id_producto || '');
                                extrasAux = extrasAux.concat(extrasTransf);
                            });
                        } else {
                            extrasAux = transformarExtras(producto.extras || [], 'RE', producto.id || '');
                        }

                        // Crear el objeto de producto
                        var auxProducto = {
                            id: producto.id,
                            nombre: producto.nombre || '',
                            impuesto: producto.impuesto || 0,
                            precio: producto.precio || 0,
                            codigo: producto.codigo || '',
                            tipoComanda: producto.tipoComanda || '',
                            cantidad: producto.cantidad || -1,
                            cantidad_original: producto.cantidad || -1,
                            tipoProducto: producto.tipoProducto || -1,
                            extras: extrasAux,
                            es_promocion: producto.es_promocion || 'N'
                        };
                        productos.push(auxProducto);
                        productosGeneral.push(auxProducto); // Agregar el producto a productosGeneral
                    });
                }

                // Agregar la categoría actual con sus productos
                categorias.push({
                    id: categoria.id,
                    categoria: categoria.categoria,
                    productos: productos
                });
            });
        } else {
            console.warn("tipo.categorias no es un arreglo:", tipo.categorias);
        }

        // Agregar el tipo actual con sus categorías
        tipos.push({
            nombre: tipo.nombre,
            codigo: tipo.codigo,
            color: tipo.color,
            categorias: categorias
        });
    });

    // Generar la interfaz y seleccionar el primer tipo
    generarTipos();
    seleccionarTipo(0);
}
