/*
    tipos[
        "nombre": "nombre",
        "color": "#000000",
        "categorias": [
            "id": 0,
            "categoria": "categoria",
            "productos": [
                "id": 0,
                "nombre": "nombre",
                "descripcion": "descripcion",
                "categoria": 0,
                "impuesto": 0,
                "imagen": "ruta",
                "precio": 0,
                "estado": "A",
                "codigo": "codigo"
            ]
        ]
    ]
*/

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-ordenes tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    validarCajaAbierta();

});

var guardandoOrden = false;
var idOrdenAnular = 0;
var detallesAnular = [];
/**
 * Eventos iniciales
 */
window.addEventListener("load", init, false);
document.addEventListener('DOMContentLoaded', function () {
    //Inicializa scroll para las dos listas
    inicializarScroller('scrl-categorias');
    inicializarScroller('scrl-productos');
    inicializarScroller('scrl-orden');
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

/**
 * Variables para el control de lógica
 */
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


function init() {
    limpiar();
    inicializarMapaContenedores();
    generarTipos();
    seleccionarTipo(0);

}




/**
 * Asigna el valor por defecto de las variables
 */
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
    //Por cada categoría, genera el HTML correspondiente para el card que será insertado en el scroller
    tipo.categorias.forEach(categoria => {
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
                            <input type="checkbox" name="${extra.dsc_grupo}" ${ (found == null || found == undefined)? "" : "checked"} 
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
                            <input type="radio" name="${extra.dsc_grupo}"  ${ (found == null || found == undefined)? "" : "checked"} 
                            onchange="seleccionarExtraRadio(this, '${extra.dsc_grupo}', ${extra.multiple}, ${extra1.id})"
                            value="${extra1.id}">${extra1.descripcion} ₡${extra1.precio}
                        </label>
                        `;
                cont++;
            });
            if (extra.requerido == 0) {
                texto += `<label style="margin-left:10px;">
                            <input type="radio" name="${extra.dsc_grupo}" ${ (!selecciono) ? "checked" : ""} 
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
        agregarProducto(productoSeleccionado, impServicioAux);
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

/**
 * Agrega un producto a la orden.
 * @param {Boolean} impuestoServicio Indica si es un producto que utiliza servicio a la mesa.
 * @param {String} codigo Código del producto.
 * @param {Boolean} todos Indica si debe buscar el producto en la lista de todos los productos.
 */
function seleccionarProducto(impuestoServicio, codigo, todos = false) {
    let producto = buscarProductoCodigo(codigo, todos);
    if (producto !== undefined) {

        if (validarCantidadProducto(producto)) {
            impServicioAux = impuestoServicio;
            if (producto.extras.length > 0) {
                abrirModalExtrasProd(producto);
            } else {
                extrasDetalleAux = [];
                agregarProducto(producto, impuestoServicio);
            }
        } else {
            swal('Agregar producto', "Existencias agotadas para este producto.", 'error');
        }
    } else {
        swal('Agregar producto', "No se ha podido encotrar el producto con el código indicado.", 'error');
    }
}

function agregarProducto(producto, impuestoServicio) {
    productoSeleccionado = producto;
    let indice = buscarDetallePrevio(impuestoServicio, producto);
    if (indice >= 0) {
        actualizarDetalleOrden(indice);
    } else {
        detalles.push(crearDetalleOrden(detalles.length, 1, impuestoServicio, producto, ""));
        reducirCantidadProducto(producto.codigo);
    }

    productoSeleccionado.extras.forEach(extra => {
        extra.extras.forEach(extra1 => {
            extra1.seleccionado = false;
        });
    });
    actualizarOrden();
}

function buscarProductoCodigo(codigo, todos = false) {
    if (codigo != null && codigo != undefined) {
        let productos;
        if (!todos) {
            productos = tipos[tipoSeleccionado].categorias[categoriaSeleccionada].productos;
        } else {
            productos = productosGeneral;
        }

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


function buscarDetallePrevio(impuestoServicio, producto) {
    let indice = -1;
    let contador = 0;
    let aumentar = true;

    detalles.forEach(detalle => {
        var existeDetalle = arraysSonIguales(extrasDetalleAux, detalle.extras);

        if (detalle.producto.codigo == producto.codigo && detalle.impuestoServicio == impuestoServicio && existeDetalle) {
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
    actualizarOrden();
    generarProductos();
}

function actualizarDetalleOrden(indice, aumenta = true) {

    var detalle = detalles[indice];
    if (aumenta) {
        detalle.cantidad = detalle.cantidad + 1;
        detalle.total = detalle.cantidad * detalle.producto.precio;
        if (detalle.impuestoServicio == 'S') {
            let impuestoMesa = 0;
            impuestoMesa = detalle.total - (detalle.total / 1.10);
            detalle.total = detalle.total + impuestoMesa;
        }
        detalles[indice] = detalle;
        reducirCantidadProducto(detalle.producto.codigo);
    } else {
        detalle.cantidad = detalle.cantidad - 1;
        if (detalle.cantidad <= 0) {
            detalles.splice(indice, 1);
        } else {
            detalle.total = detalle.cantidad * detalle.producto.precio;
            if (detalle.impuestoServicio == 'S') {
                let impuestoMesa = 0;
                impuestoMesa = detalle.total - (detalle.total / 1.10);
                detalle.total = detalle.total + impuestoMesa;
            }
            detalles[indice] = detalle;
        }
        aumentarCantidadProducto(detalle.producto.codigo);
    }
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
    if (detalle.impuestoServicio == 'S') {
        let impuestoMesa = 0;
        impuestoMesa = detalle.total - (detalle.total / 1.10);
        detalle.total = detalle.total + impuestoMesa;
    }
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

function crearDetalleOrden(indice, cantidad, impuestoServicio, producto, descripcion) {
    let totalAux = parseFloat(producto.precio * cantidad).toFixed(2);
    if (impuestoServicio == 'S') {
        let impuestoMesa = 0;
        impuestoMesa = totalAux - (totalAux / 1.10);
        totalAux = parseInt(totalAux) + parseInt(impuestoMesa);
    }
    return {
        "indice": indice,
        "cantidad": cantidad,
        "impuestoServicio": impuestoServicio,
        "impuesto": producto.impuesto,
        "precio_unidad": producto.precio,
        "total": parseFloat(totalAux).toFixed(2),
        "observacion": descripcion,
        //"tipo": tipos[tipoSeleccionado].codigo,
        "tipo": producto.tipoProducto,
        "tipoComanda": producto.tipoComanda,
        "producto": producto,
        "extras": extrasDetalleAux
    };
}

function actualizarOrden() {
    let cards = '';
    let contador = 0;
    let subTotal = 0;
    let total = 0;
    detalles.forEach(detalle => {
        let totalExtrasAux = 0;
        let textoExtras = "";
        detalle.extras.forEach(extra => {
            totalExtrasAux = totalExtrasAux + (detalle.cantidad * parseFloat(extra.precio));
            textoExtras += extra.descripcion + " " + (extra.precio > 0 ? currencyCRFormat((detalle.cantidad * parseFloat(extra.precio))) : " ") + " </br> ";
        });
        let totalAux = detalle.cantidad * parseFloat(detalle.precio_unidad);

        totalAux = totalAux + totalExtrasAux;
        subTotal = subTotal + totalAux;
        if (detalle.impuestoServicio == 'S') {
            impuestoMesa = totalAux - (totalAux / 1.10);
            subTotal = subTotal + impuestoMesa;
        }
        detalle.total = totalAux;
        cards += generarHTMLProductoOrden(contador, detalle.producto.nombre, parseFloat(detalle.producto.precio).toFixed(2), detalle.cantidad, parseFloat(totalAux).toFixed(2), detalle.producto.codigo, detalle.impuestoServicio, totalExtrasAux, textoExtras);

        contador++;
    });
    ordenGestion.subTotal = subTotal;
    total = subTotal;
    let aux = 0;

    if (ordenGestion.codigo_descuento != null) {
        if (ordenGestion.codigo_descuento.cod_general == 'DESCUENTO_PORCENTAJE') {
            const porcentaje = ordenGestion.codigo_descuento.descuento / 100;
            aux = subTotal * porcentaje;
        } else if (ordenGestion.codigo_descuento.cod_general == 'DESCUENTO_ABSOLUTO') {
            aux = ordenGestion.codigo_descuento.descuento;
        }
        $('#txt-dsc_promo').html("<small>Descuento Aplicado : " + ordenGestion.codigo_descuento.descripcion + "</small>");
        $('#cont-dsc_promo').fadeIn(1000);
        $('#txt-id-cliente').val(ordenGestion.cliente);
    } else {
        $('#cont-dsc_promo').fadeOut(1000);
        $('#txt-dsc_promo').val("");
    }

    total = subTotal - aux;
    ordenGestion.total = total;

    $('#txt-id-cliente').val(ordenGestion.cliente);

    $('#txt-subtotal-pagar').html("SubTotal: " + (ordenGestion.subTotal).toLocaleString('es-CR', {
        style: 'currency',
        currency: 'CRC',
    }));

    $('#txt-descuento-pagar').html("Descuento: " + (aux).toLocaleString('es-CR', {
        style: 'currency',
        currency: 'CRC',
    }));

    $('#txt-total-pagar').html("Total: " + (ordenGestion.total).toLocaleString('es-CR', {
        style: 'currency',
        currency: 'CRC',
    }));


    $(contenedores.get("orden")).html(cards);
}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLProductoOrden(indice, detalle, precio, cantidad, total, codigo, impuestoServicio = "N", totalExtrasAux, textoExtras) {
    var icono = "fas fa-box text-secondary";
    if (impuestoServicio == "S") {
        icono = "fas fa-utensils text-secondary";
    }

    return `<tr style="border-bottom: 1px solid grey; ">
                <td> 
                <small>
                    ${detalle}
                    <div class="input-group w-auto justify-content-center align-items-center" style="padding: 0px !important;display: block!important; margin-top:2px;margin-bottom:2px;">
                                    <input type="button" value="-" 
                                        class="button-minus border rounded-circle  icon-shape icon-sm mx-1 " 
                                        data-field="quantity" onclick="agregarDetalleInpt(${indice},'${codigo}',false)">
                                    <input type="number" step="1" min=1 value="${cantidad}"
                                        readonly
                                       name="quantity" class="quantity-field border-0 text-center w-25"
                                       style="width:28%!important;">
                                    <input type="button" value="+" class="button-plus border rounded-circle icon-shape icon-sm "
                                     data-field="quantity" onclick="agregarDetalleInpt(${indice},'${codigo}',true)">
                    </div>
                    
                    <p style="line-height: 1.5;">
                    Precio : ${currencyCRFormat(precio)}
                    </br>
                    ${totalExtrasAux > 0 ? "Extras : "+ currencyCRFormat(totalExtrasAux) + " </br>" : ""}
                   
                      Total : ${currencyCRFormat(total)} 
                        </small>
                </td>

                <td >
                <p style="line-height: 1.5;"><small>${textoExtras}</small></p>
                </td>
                <td >
                    <div class="row" style="padding: 0px !important;">
                        <div class="col-sm-12 col-md-12 col-lg-12">
                            <div class="input-group w-auto justify-content-center align-items-center">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-6  justify-content-center align-items-center">
                                        <button type="button" class="btn btn-danger px-2" onclick="eliminarLineaDetalleOrden(${indice})"><i
                                        class="fas fa-trash" aria-hidden="true"></i></button>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-6  justify-content-center align-items-center">
                                        <button type="button" class="btn btn-warning px-2" title="Agregar observación a la orden" onclick="agregarDescripcionDetalle(${indice})"><i
                                        class="fas fa-clipboard" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>`;
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



/* Métodos de botones de acción */

function seleccionarCliente(id, nombre) {
    $("#txt-id-cliente").val(id);
    $("#txt-cliente").val(nombre);
}

function limpiarOrden() {
    ordenGestion = {
        "id": null,
        "cliente": "",
        "nueva": true,
        "total": 0,
        "subTotal": 0,
        "codigo_descuento": null
    };
    $('#monto_sinpe').val(""); // Supongo que txt-sinpe es el campo para el pago con SINPE
    $('#monto_tarjeta').val(""); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
    $('#monto_efectivo').val("");
    reiniciarCantidadesProductos();
    detalles = [];
    actualizarOrden();
    generarProductos();
    $("#txt-cliente").val("");
    guardandoOrden = false;
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

function confirmarOrden() {
    if (!guardandoOrden) {
        $('#btn_facturar_confirmar').attr("disabled", true);
        iziToast.success({
            title: 'Crear orden',
            message: 'Procesando orden...',
            position: 'topRight'
        });
        guardandoOrden = true;
        generarOrden();
    } else {
        $('#btn_facturar_confirmar').attr("disabled", false);
        iziToast.error({
            title: 'Crear orden',
            message: 'Existe una orden en proceso de creación',
            position: 'topRight'
        });
    }

    /*swal({
            title: 'Desea confirmar la orden?',
            text: 'No podrá deshacer esta acción!',
            icon: 'info',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                swal.close();
                generarOrden();
            } else {
                swal.close();
            }
        });*/
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

function generarOrden() {
    if (validarFormularioOrden()) {
        try {
            var ordenProcesar = {
                "id": "null",
                "estado": "",
                "idCliente": $('#txt-id-cliente').val(),
                "nombreCliente": $('#txt-cliente').val(),
                "mesaId": $('#sel-mobiliario').val(),
                "detalles": detalles
            };

            $.ajax({
                url: `${base_path}/cocina/facturar/ordenes/crearOrden`,
                type: 'post',
                dataType: "json",
                data: {
                    _token: CSRF_TOKEN,
                    orden: ordenProcesar
                }
            }).done(function (res) {
                if (!res['estado']) {
                    swal('Generar Orden', res['mensaje'], 'error');
                } else {
                    let datos = res["datos"];
                    $('#btn_facturar_confirmar').attr("disabled", false);
                    if (!tienePedidosMesa()) {
                        swal('Orden realizada!', 'Redirigiendo al pago.', 'success');
                        redirigirCobro(datos);
                    } else {
                        //Orden completada
                        limpiarOrden();
                        swal('Orden realizada!', '', 'success');
                        //TODO agregar orden al sidebar

                    }
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                swal('Procesar Orden', "Algo salió mal.", 'error');
                $('#btn_facturar_confirmar').attr("disabled", false);
                guardandoOrden = false;
            });

        } finally {
            guardandoOrden = false;
        }
    } else {
        $('#btn_facturar_confirmar').attr("disabled", false);
        guardandoOrden = false;
    }
}

function redirigirCobro(id) {
    $("#ipt_id_orden").val(id);
    $("#frm-caja-rapida").submit();
}

function validarFormularioOrden() {

    if (detalles.length < 1) {
        swal('Datos incompletos', 'Debe seleccionar los productos para generar la orden.', 'error');
        return false;
    }

    return true;
}

function verificarAbrirModalPago() {
    if (detalles.length < 1) {
        showError('Debe seleccionar los productos para facturar');
        return false;
    }
    var cliente = $('#txt-cliente').val();
    if (cliente == "" || cliente == null || cliente == undefined) {
        $('#txt-cliente').focus();
        showError("Debe indicar el nombre del cliente");
        return;
    }

    var pago_sinpe = parseFloat($('#monto_sinpe').val()); // Supongo que txt-sinpe es el campo para el pago con SINPE
    var pago_tarjeta = parseFloat($('#monto_tarjeta').val()); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
    var pago_efectivo = parseFloat($('#monto_efectivo').val()); // Supongo que txt-efectivo es el campo para el pago en efectivo
    var ordenGestionTotal = parseFloat(ordenGestion.total); // Supongo que ordenGestion.total es el total de la orden

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

    var sumaPagos = pago_sinpe + pago_tarjeta + pago_efectivo;

    var textoPago = "Espere mientras se procesa la factura";

    if (pago_tarjeta > 0) {
        textoPago = "Esperando información de pago mediante tarjeta";
    }
    if (sumaPagos === ordenGestionTotal) {
        $('#mdl-loader-pago').modal("show");
        $('#texto_pago_aux').html(textoPago);
        procesarPago(pago_sinpe, pago_efectivo, pago_tarjeta);
    } else {
        showError("La suma de los pagos no coincide con el total de la orden.");
        return;
    }
}


function verificarAbrirModalPagoEfectivo() {
    if (detalles.length < 1) {
        showError('Debe seleccionar los productos para facturar');
        return false;
    }
    var cliente = $('#txt-cliente').val();
    if (cliente == "" || cliente == null || cliente == undefined) {
        $('#txt-cliente').focus();
        showError("Debe indicar el nombre del cliente");
        return;
    }
    $('#monto_sinpe').val(ordenGestion.total);
    var ordenGestionTotal = parseFloat(ordenGestion.total); // Supongo que ordenGestion.total es el total de la orden
    var pago_efectivo = ordenGestionTotal;
    $('#monto_tarjeta').val("0");
    var pago_sinpe = 0;

    $('#monto_efectivo').val("0");
    var pago_tarjeta = 0;

    var sumaPagos = pago_sinpe + pago_tarjeta + pago_efectivo;

    var textoPago = "Espere mientras se procesa la factura";

    if (pago_tarjeta > 0) {
        textoPago = "Esperando información de pago mediante tarjeta";
    }
    if (sumaPagos === ordenGestionTotal) {
        $('#mdl-loader-pago').modal("show");
        $('#texto_pago_aux').html(textoPago);
        procesarPago(pago_sinpe, pago_efectivo, pago_tarjeta);
    } else {
        showError("La suma de los pagos no coincide con el total de la orden.");
        return;
    }
}

function verificarAbrirModalPagoTarjeta() {
    if (detalles.length < 1) {
        showError('Debe seleccionar los productos para facturar');
        return false;
    }
    var cliente = $('#txt-cliente').val();
    if (cliente == "" || cliente == null || cliente == undefined) {
        $('#txt-cliente').focus();
        showError("Debe indicar el nombre del cliente");
        return;
    }
    $('#monto_tarjeta').val(ordenGestion.total);
    var ordenGestionTotal = parseFloat(ordenGestion.total); // Supongo que ordenGestion.total es el total de la orden
    var pago_tarjeta = ordenGestionTotal;
    $('#monto_sinpe').val("0");
    var pago_sinpe = 0;

    $('#monto_efectivo').val("0");
    var pago_efectivo = 0;

    var sumaPagos = pago_sinpe + pago_tarjeta + pago_efectivo;

    var textoPago = "Espere mientras se procesa la factura";

    if (pago_tarjeta > 0) {
        textoPago = "Esperando información de pago mediante tarjeta";
    }
    if (sumaPagos === ordenGestionTotal) {
        $('#mdl-loader-pago').modal("show");
        $('#texto_pago_aux').html(textoPago);
        procesarPago(pago_sinpe, pago_efectivo, pago_tarjeta);
    } else {
        showError("La suma de los pagos no coincide con el total de la orden.");
        return;
    }
}

function verificarAbrirModalPagoSinpe() {
    if (detalles.length < 1) {
        showError('Debe seleccionar los productos para facturar');
        return false;
    }
    var cliente = $('#txt-cliente').val();
    if (cliente == "" || cliente == null || cliente == undefined) {
        $('#txt-cliente').focus();
        showError("Debe indicar el nombre del cliente");
        return;
    }
    $('#monto_sinpe').val(ordenGestion.total);
    var ordenGestionTotal = parseFloat(ordenGestion.total); // Supongo que ordenGestion.total es el total de la orden
    var pago_sinpe = ordenGestionTotal;
    $('#monto_tarjeta').val("0");
    var pago_tarjeta = 0;

    $('#monto_efectivo').val("0");
    var pago_efectivo = 0;

    var sumaPagos = pago_sinpe + pago_tarjeta + pago_efectivo;

    var textoPago = "Espere mientras se procesa la factura";

    if (pago_tarjeta > 0) {
        textoPago = "Esperando información de pago mediante tarjeta";
    }
    if (sumaPagos === ordenGestionTotal) {
        $('#mdl-loader-pago').modal("show");
        $('#texto_pago_aux').html(textoPago);
        procesarPago(pago_sinpe, pago_efectivo, pago_tarjeta);
    } else {
        showError("La suma de los pagos no coincide con el total de la orden.");
        return;
    }
}


function procesarPago(mto_sinpe, mto_efectivo, mto_tarjeta) {
    $.ajax({
        url: `${base_path}/facturacion/pos/crearFactura`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            orden: ordenGestion,
            detalles: detalles,
            mto_sinpe: mto_sinpe,
            mto_efectivo: mto_efectivo,
            mto_tarjeta: mto_tarjeta
        }
    }).done(function (res) {
        console.log(res);
        if (!res['estado']) {
            showError(res['mensaje']);
            return;
        } else {
            id = res['datos'];
            imprimirTicket(id);
            showSuccess("Orden realizada!");
        }
        $('#mdl-loader-pago').modal("hide");
        location.reload();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        console.log(textStatus);
        console.log(errorThrown);
        showError("Algo salió mal");
        $('#mdl-loader-pago').modal("hide");
    });
    $('#mdl-loader-pago').modal("hide");

}

function recargarOrdenes() {
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
    });
}

function imprimirTicket(id) {
    $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
    document.getElementById('btn-pdf').click();
}

function generarHTMLOrdenes(ordenes) {
    var texto = "";

    ordenes.forEach(orden => {
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

        lineas.slice(0, -1);
        texto = texto +
            `<tr style="border-bottom: 1px solid grey;">
                <td class="text-center" onclick="imprimirTicket( ${orden.id})" style="cursor:pointer; text-decoration : underline; ">
                    ${orden.numero_orden}
                </td> 
                <td class="text-center">
                    ${orden.fecha_inicio}
                </td>
                <td class="text-center">
                    ${orden.nombre_cliente ?? ""}
                </td>
                <td class="text-center">
                ${orden.total_con_descuento ?? 0}
            </td> <td class="text-center">
            ${orden.estadoOrden ?? ""}
        </td>`;

        texto = texto + `</tr>`;
    });

    $('#tbody-ordenes').html(texto);
    $('#mdl-ordenes').modal('show');
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
    }).fail(function (jqXHR, textStatus, errorThrown) {});
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

function cerrarCaja() {
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
            showError(res['mensaje']);
            return;
        }
        showSuccess("Se cerró la caja correctamente");
        cajaAbierta = false;
        cerrarModalCerrarCaja();
        window.location.href = window.location.href;

    }).fail(function (jqXHR, textStatus, errorThrown) {
        cajaAbierta = true;
        showError("Algo salió mal");
    });
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
