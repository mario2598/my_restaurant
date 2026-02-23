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
    incluyeFE: false,
    estaConfiguradoFE: false,
    info_ced_fe: "",
    info_nombre_fe: "",
    info_correo_fe: "",
    info_fe: {
        id: "",
        codigo_actividad: "",
        tipo_identificacion: "",
        identificacion: "",
        nombre_comercial: "",
        direccion: "",
        correo: ""
    }
};

var guardandoOrden = false;
var idOrdenAnular = 0;
var detallesAnular = [];
var cambiosPendientes = false;

function tieneIncidentes() {
    return (typeof ordenGestion !== 'undefined' && (ordenGestion.incidentes || []).length > 0);
}
function tienePagosFraccionados() {
    return (typeof ordenGestion !== 'undefined' && !ordenGestion.nueva && (detalles || []).some(function (d) { return (d.cantidad_pagada || 0) > 0; }));
}
function bloquearEdicionPorIncidentesOPagos() {
    return tieneIncidentes() || tienePagosFraccionados();
}

window.addEventListener("load", init, false);

document.addEventListener('DOMContentLoaded', function () {

    //Inicializa scroll para las dos listas
    inicializarScroller('scrl-categorias');
    inicializarScroller('scrl-productos');
    inicializarScroller('scrl-orden');

    // Inicializar el estado del botón de FE
    actualizarEstadoBotonFE();
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
        cards += generarHTMLProducto(producto.nombre, producto.codigo, producto.precio, producto.cantidad, producto.tipoProducto, producto.descripcion || '');
        contador++;
    });

    $(contenedores.get("productos")).html(cards);
}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLProducto(nombre, codigo, precio, cantidad, tipoProd, descripcion = '') {
    var text = `<tr class="filaProductos">
    <td width="40%">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span onclick="seleccionarProducto('N','${codigo}')" style="cursor: pointer; flex: 1;">${nombre}</span>`;
    
    // Agregar botón para ver descripción solo si existe
    if (descripcion && descripcion.trim() !== '') {
        // Escapar correctamente para atributos HTML usando base64
        var descripcionBase64 = btoa(unescape(encodeURIComponent(descripcion)));
        var nombreBase64 = btoa(unescape(encodeURIComponent(nombre)));
        text += `<button type="button" 
                    class="btn btn-sm btn-link p-0 ml-2 btn-ver-descripcion" 
                    style="color: #6c757d; font-size: 0.85em; min-width: auto; padding: 2px 4px !important;"
                    data-descripcion="${descripcionBase64}"
                    data-nombre="${nombreBase64}"
                    title="Ver descripción">
                    <i class="fas fa-info-circle"></i>
                </button>`;
    }
    
    text += `</div>`;
    if (tipoProd == "E") {
        text += `<br> <small `;
        if (cantidad < 15) {
            text += `style="color:red;"`;
        }
        text += `> Cantidad : <strong> ${cantidad}</strong></small>`;
    }
    text += `</td><td width="30%" style="text-align: center" onclick="seleccionarProducto('N','${codigo}')" style="cursor: pointer;">${parseFloat(precio).toFixed(2)}</td></tr>`;

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
    if (bloquearEdicionPorIncidentesOPagos()) {
        showError('No se puede eliminar ni modificar líneas cuando hay incidentes o pagos fraccionados en la orden.');
        return;
    }
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
    if (bloquearEdicionPorIncidentesOPagos()) {
        showError('No se puede modificar la cantidad cuando hay incidentes o pagos fraccionados en la orden.');
        return;
    }
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
    if (bloquearEdicionPorIncidentesOPagos()) {
        showError('No se puede modificar la cantidad cuando hay incidentes o pagos fraccionados en la orden.');
        return;
    }
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

        var bloquearEdicionCantidad = bloquearEdicionPorIncidentesOPagos();
        cards += generarHTMLProductoOrden(contador, detalle.producto.nombre, parseFloat(detalle.producto.precio).toFixed(2), detalle.cantidad,
            parseFloat(detalle.total).toFixed(2), detalle.producto.codigo, detalle.impuestoServicio, totalExtrasAux, textoExtras, detalle.cantidad_preparada,
            detalle.cantidad_pagada, bloquearEdicionCantidad);
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
    var totalRebajar = parseFloat(ordenGestion.totalRebajarIncidentes) || 0;
    var totalConRebaja = Math.max(0, ordenGestion.total - totalRebajar);

    // Actualizar los valores en el DOM
    $('#txt-cliente').val(ordenGestion.cliente);
    $('#select_mesa').val(ordenGestion.mesa ?? -1);

    $('#txt-subtotal-pagar').html(`SubTotal: ${(ordenGestion.subTotal).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    $('#txt-mto-envio_mdl').html(infoEnvio.incluye_envio ? `Envío: ${(parseFloat(ordenGestion.envio)).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}` : "Envío: No aplica");
    $('#txt-total-pagar').html(`Total Orden: ${(ordenGestion.total).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    $('#txt-mto-pagado_mdl').html(`Monto Pagado : ${(ordenGestion.mto_pagado).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    $('#txt-total-pagar_mdl').html(`Total: ${(totalConRebaja).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);

    if (totalRebajar > 0) {
        $('#row-rebajar-incidentes-mdl').show();
        $('#txt-rebajar-incidentes-mdl').html(`Total a rebajar en incidentes: ${(totalRebajar).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    } else {
        $('#row-rebajar-incidentes-mdl').hide();
    }

    var incidentes = ordenGestion.incidentes || [];
    if (incidentes.length > 0) {
        var inc = incidentes[0];
        $('#cont-incidente-orden-mdl').show();
        $('#incidente-descripcion-mdl').text((inc.descripcion || '').substring(0, 80) + ((inc.descripcion || '').length > 80 ? '...' : ''));
        $('#incidente-monto-mdl').text((parseFloat(inc.monto_afectado) || 0).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' }));
        $('#btn-eliminar-incidente-mdl').data('incidente-id', inc.id);
    } else {
        $('#cont-incidente-orden-mdl').hide();
    }

    $(contenedores.get("orden")).html(cards);

    // Actualizar el botón de Factura Electrónica
    $('#btn_fe').html(`<i class="fas fa-pay" aria-hidden="true"></i> Factura Electrónica : ${infoFE.incluyeFE ? 'SÍ' : 'NO'}`);

    validarVisibilidadBotonesGestion();
    mtoDescuentoGen = descuentoAplicado;
}


function generarHTMLProductoOrden(indice, detalle, precio, cantidad, total, codigo, impuestoServicio = "N", totalExtrasAux,
    textoExtras, cantidad_preparada, cantidad_pagada, bloquearEdicionCantidad) {
    bloquearEdicionCantidad = !!bloquearEdicionCantidad;
    const pendiente = cantidad - cantidad_preparada;
    var icono = "fas fa-box text-secondary";
    if (impuestoServicio == "S") {
        icono = "fas fa-utensils text-secondary";
    }

    var deshabilitarCantidad = (cantidad_pagada >= cantidad) || bloquearEdicionCantidad;

    let texto = `<tr style="border-bottom: 1px solid grey;">
                    <td> 
                    <small>
                        ${detalle}
                        <div class="input-group w-auto justify-content-center align-items-center" 
                        style="padding: 0px !important; display: block!important; margin-top:2px; margin-bottom:2px;">`;

    const botonMenosDisabled = deshabilitarCantidad ? "disabled" : "";

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
                            data-field="quantity" onclick="agregarDetalleInpt(${indice},'${codigo}',true)" ${botonMenosDisabled}>`;
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
                                                    ${deshabilitarCantidad ? "disabled" : ""}>
                                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <div class="col-sm-6 col-md-6 col-lg-6 justify-content-center align-items-center">
                                                <button type="button" class="btn btn-warning px-2" 
                                                    title="Agregar observación a la orden"  ${deshabilitarCantidad ? "disabled" : ""}
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
        "codigo_descuento": null,
        "idCliente": -1,
        "incidentes": [],
        "totalRebajarIncidentes": 0
    };
    $('#monto_sinpe').val(""); // Supongo que txt-sinpe es el campo para el pago con SINPE
    $('#monto_tarjeta').val(""); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
    $('#monto_efectivo').val("");
    $("#txt-cliente").val("");
    $('#select_mesa').val(-1);

    // Limpiar cliente seleccionado
    limpiarClienteSeleccionado();

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


    $('#monto_sinpe').val(""); // Supongo que txt-sinpe es el campo para el pago con SINPE
    $('#monto_tarjeta').val(""); // Supongo que txt-tarjeta es el campo para el pago con tarjeta
    $('#monto_efectivo').val("");

    // Sincronizar el input del modal con el estado del cliente seleccionado
    const inputClienteModal = document.getElementById('nombreCliente');
    const inputClientePrincipal = document.getElementById('txt-cliente');

    if (inputClienteModal) {
        inputClienteModal.value = ordenGestion.cliente ?? "";

        // Si el cliente principal está deshabilitado, también deshabilitar el del modal
        if (inputClientePrincipal && inputClientePrincipal.disabled) {
            inputClienteModal.disabled = true;
            inputClienteModal.style.backgroundColor = '#f8f9fa';
            inputClienteModal.style.cursor = 'not-allowed';
        } else {
            inputClienteModal.disabled = false;
            inputClienteModal.style.backgroundColor = '';
            inputClienteModal.style.cursor = '';
            inputClienteModal.placeholder = 'Nombre del Cliente';
        }
    }

    cargarDetallesDividirCuentas(detalles);
    if (!ordenGestion.nueva && ordenGestion.id && !tieneIncidentes() && !tienePagosFraccionados()) {
        $('#cont-btn-incidente-pago').show();
    } else {
        $('#cont-btn-incidente-pago').hide();
    }
    $('#mdl-pago').modal("show");
}

function abrirModalIncidentePago() {
    if (!ordenGestion.id || ordenGestion.nueva) {
        showError('Debe tener una orden cargada para agregar un incidente.');
        return;
    }
    if (tieneIncidentes() || tienePagosFraccionados()) {
        showError('No se pueden agregar incidentes cuando ya existen incidentes o hay pagos fraccionados en la orden.');
        return;
    }
    $('#incidente_pago_descripcion').val('');
    $('#incidente_pago_monto').val('0');
    $('#incidente_pago_clave_maestra').val('');
    $('#mdl-incidente-pago').modal('show');
}

function cerrarModalIncidentePago() {
    $('#mdl-incidente-pago').modal('hide');
}

function guardarIncidenteDesdeModalPago() {
    var ordenId = ordenGestion.id;
    if (!ordenId) {
        showError('No hay orden cargada.');
        return;
    }
    var descripcion = $('#incidente_pago_descripcion').val().trim();
    var monto = parseFloat($('#incidente_pago_monto').val()) || 0;
    var claveMaestra = $('#incidente_pago_clave_maestra').val();

    if (!descripcion) {
        showError('La descripción del incidente es obligatoria.');
        return;
    }
    if (!claveMaestra) {
        showError('Debe ingresar la clave maestra.');
        return;
    }
    $.ajax({
        url: `${base_path}/facturacion/pos/incidentes/guardar`,
        type: 'post',
        dataType: 'json',
        data: {
            _token: CSRF_TOKEN,
            orden: ordenId,
            descripcion: descripcion,
            monto_afectado: monto,
            clave_maestra_ingresada: claveMaestra
        }
    }).done(function (response) {
        if (!response.estado) {
            showError(response.mensaje || 'Error al guardar.');
            return;
        }
        showSuccess(response.mensaje || 'Incidente registrado.');
        $('#incidente_pago_descripcion').val('');
        $('#incidente_pago_monto').val('0');
        $('#incidente_pago_clave_maestra').val('');
        cerrarModalIncidentePago();
        window._preservarCodigoDescuento = ordenGestion.codigo_descuento;
        cargarOrdenGestion(ordenGestion.id);
    }).fail(function () {
        showError('Error al guardar el incidente.');
    });
}

function eliminarIncidenteOrden() {
    var idIncidente = $('#btn-eliminar-incidente-mdl').data('incidente-id');
    if (!idIncidente) {
        showError('No hay incidente para eliminar.');
        return;
    }
    if (!confirm('¿Eliminar este incidente?')) return;
    $.ajax({
        url: `${base_path}/facturacion/pos/incidentes/eliminar`,
        type: 'post',
        dataType: 'json',
        data: { _token: CSRF_TOKEN, id: idIncidente }
    }).done(function (response) {
        if (!response.estado) {
            showError(response.mensaje || 'Error al eliminar.');
            return;
        }
        showSuccess(response.mensaje || 'Incidente eliminado.');
        ordenGestion.incidentes = [];
        ordenGestion.totalRebajarIncidentes = 0;
        actualizarOrden();
        $('#cont-btn-incidente-pago').show();
    }).fail(function () {
        showError('Error al eliminar el incidente.');
    });
}

function calcularCambio(montoRecibido, montoTotal, montoTarjeta = 0, montoSinpe = 0) {
    // Calcular el monto que falta por pagar (total - tarjeta - sinpe)
    let montoFaltante = montoTotal - montoTarjeta - montoSinpe;
    // Calcular el cambio basado en el monto recibido en efectivo menos el monto faltante
    let cambio = parseFloat(montoRecibido) - montoFaltante;
    return cambio >= 0 ? cambio.toFixed(2) : 0;
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

    if (isNaN(pago_sinpe) || pago_sinpe == "" || pago_sinpe == null || pago_sinpe == undefined) {
        $('#monto_sinpe').val("0");
        pago_sinpe = 0;
    }

    if (isNaN(pago_tarjeta) || pago_tarjeta == "" || pago_tarjeta == null || pago_tarjeta == undefined) {
        $('#monto_tarjeta').val("0");
        pago_tarjeta = 0;
    }
    if (isNaN(pago_efectivo) || pago_efectivo == "" || pago_efectivo == null || pago_efectivo == undefined) {
        $('#monto_efectivo').val("0");
        pago_efectivo = 0;
    }

    // Calcular el cambio si hay pago en efectivo
    if (parseFloat(pago_efectivo) > 0) {
        let montoTotal = totalSeleccionado + parseFloat(ordenGestion.envio);
        let cambio = calcularCambio(pago_efectivo, montoTotal, parseFloat(pago_tarjeta), parseFloat(pago_sinpe));
        let montoFaltante = montoTotal - parseFloat(pago_tarjeta) - parseFloat(pago_sinpe);

        if (parseFloat(cambio) > 0) {
            swal({
                title: 'Cambio a entregar',
                text: ` Total a pagar: ${parseFloat(montoTotal).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}  
                        
                        Pago en tarjeta: ${parseFloat(pago_tarjeta).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })} 
                        Pago en sinpe: ${parseFloat(pago_sinpe).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })} 
                        Monto en efectivo: ${parseFloat(pago_efectivo).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}   
                        El cambio en efectivo a entregar es: ${parseFloat(cambio).toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`,
                icon: 'info',
                buttons: {
                    cancel: "Cancelar",
                    confirm: "Continuar con el pago"
                },
                dangerMode: true,
            })
                .then((willProceed) => {
                    if (willProceed) {
                        pago_efectivo = montoFaltante;
                        procesarPago();
                    }
                });
            return;
        } else {
            procesarPago();
        }
    } else {
        procesarPago();
    }

}

function procesarPagoMixto() {
    verificarAbrirModalPago($('#monto_sinpe').val(), $('#monto_tarjeta').val(), $('#monto_efectivo').val());
}

function procesarPago() {
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

function verificarAbrirModalPagoEfectivo() {
    cargarDetallesSeleccionados();

    // Solo establecer el valor si el input está vacío o es 0
    if (!$('#monto_efectivo').val() || $('#monto_efectivo').val() === "0") {
        $('#monto_efectivo').val(totalSeleccionado + parseFloat(ordenGestion.envio));
    }
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

        infoFE.info_ced_fe = (infoFE.info_fe && infoFE.info_fe.identificacion) ? infoFE.info_fe.identificacion : (infoFE.info_ced_fe || '');
        infoFE.info_nombre_fe = (infoFE.info_fe && infoFE.info_fe.nombre_comercial && infoFE.info_fe.nombre_comercial !== '') ?
            infoFE.info_fe.nombre_comercial :
            (infoFE.info_nombre_fe || ordenGestion.cliente || '');
        infoFE.info_correo_fe = (infoFE.info_fe && infoFE.info_fe.correo) ? infoFE.info_fe.correo : (infoFE.info_correo_fe || '');

        $('#mdl-loader-pago').modal("show");
        ordenGestion.cliente = $('#nombreCliente').val();
        var idCliente = window.clienteSeleccionadoId == null ? -1 : window.clienteSeleccionadoId;
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
                mto_tarjeta: mto_tarjeta,
                idCliente: idCliente
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

        $('#mdl-loader-pago').modal("hide");
    } else {
        showError("La suma de los pagos no coincide con el total de la orden.");
        $('#mdl-loader-pago').modal("hide");
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

        // Determinar el estilo y icono según el estado de la orden
        let estiloFila = "";
        let iconoEstado = "";
        let colorEstado = "";

        if (orden.cod_general == "ORD_ANULADA") {
            estiloFila = "background-color: #ffebee; border-left: 5px solid #f44336;";
            iconoEstado = "fas fa-ban";
            colorEstado = "#f44336";
        } else if (orden.pagado == 1) {
            estiloFila = "background-color: #e8f5e8; border-left: 5px solid #4caf50;";
            iconoEstado = "fas fa-check-circle";
            colorEstado = "#4caf50";
        } else {
            estiloFila = "background-color: #fff3e0; border-left: 5px solid #ff9800;";
            iconoEstado = "fas fa-clock";
            colorEstado = "#ff9800";
        }

        var iconoIncidente = (orden.tiene_incidentes || (orden.incidentes && orden.incidentes.length > 0))
            ? ' <i class="fas fa-exclamation-triangle text-warning" title="Orden con incidente(s)"></i>'
            : '';
        texto = texto +
            `<tr style="${estiloFila} border-bottom: 1px solid grey;">
                <td class="text-center"  onclick="cargarOrdenGestion(${orden.id})" style="cursor:pointer; text-decoration : underline; ">
                   <i class="fas fa-cog" aria-hidden="true"> </i> ${orden.numero_orden}${iconoIncidente}
                </td> 
                 <td class="text-center">
                    ${orden.numero_mesa ?? 'PARA LLEVAR'}
                </td>
                 <td class="text-center">
                <i class="${iconoEstado}" style="color: ${colorEstado}; margin-right: 5px;"></i>
                ${orden.cod_general == "ORD_ANULADA" ? "Anulada" : (orden.pagado == 1 ? "Pagado" : "Pendiente de Pagar")}
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
                ${orden.cod_general == "ORD_ANULADA" ? 0 : (orden.total_con_descuento ?? 0)}
            </td> 
             <td class="text-center">
                ${orden.cod_general == "ORD_ANULADA" ? 0 : (orden.mto_pagado ?? 0)}
            </td>
                <td class="text-center">
                ${orden.cod_general == "ORD_ANULADA" ? 0 : ((orden.total_con_descuento ?? 0) - (orden.mto_pagado ?? 0))}
            </td> 
               
           
            <td class="text-center" style="cursor:pointer; text-decoration : underline;" onclick="imprimirTicket( ${orden.id})"> 
                <i class="fas fa-print" aria-hidden="true"> </i> Imprimir Tiquete
            </td>
           `;


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
    let tarjeta = $('#monto_tarjeta_cierre').val();
    let sinpe = $('#monto_sinpe_cierre').val();

    if (tarjeta == '') {
        tarjeta = 0;

    }
    if (sinpe == '') {
        sinpe = 0;
    }
    subtotal = parseFloat(subtotal).toFixed(2);
    sinpe = parseFloat(sinpe).toFixed(2);

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
        $('#monto_tarjetas_lbl').html("CRC <strong>" + parseFloat(datos.total_tarjeta).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
        $('#monto_sinpe_lbl').html("CRC <strong>" + parseFloat(datos.total_sinpe).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
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

function changeNombreCliente(nombre,cambioPend = false) {
    ordenGestion.cliente = nombre;

    // Actualizar el estado del botón de FE
    actualizarEstadoBotonFE();

    // Si el campo está vacío, ocultar el panel de información del cliente y habilitar el input
    if (!nombre || nombre.trim() === '') {
        const inputCliente = document.getElementById('txt-cliente');
        inputCliente.disabled = false;
        inputCliente.style.backgroundColor = '';
        inputCliente.style.cursor = '';
        inputCliente.placeholder = 'Nombre cliente...';

        // Habilitar también el input del modal de pago si existe
        const inputClienteModal = document.getElementById('nombreCliente');
        if (inputClienteModal) {
            inputClienteModal.disabled = false;
            inputClienteModal.style.backgroundColor = '';
            inputClienteModal.style.cursor = '';
            inputClienteModal.placeholder = 'Nombre del Cliente';
        }

        // Restaurar el botón de búsqueda
        const btnBuscar = inputCliente.parentElement.querySelector('button');
        if (btnBuscar) {
            btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
            btnBuscar.title = 'Buscar Cliente';
            btnBuscar.classList.remove('btn-success');
            btnBuscar.classList.add('btn-outline-primary');
        }

        ocultarPanelClienteSeleccionado();
    }

    cambiosPendientes = cambioPend;
    validarVisibilidadBotonesGestion();
}

// Variables globales para paginación
var currentPage = 1;
var currentSearchTerm = "";
var totalPages = 1;
var totalRecords = 0;
var searchTimeout = null;

// Funciones para búsqueda de clientes con lazy loading
function abrirModalBuscarCliente() {
    console.log('Función abrirModalBuscarCliente llamada');
    $("#mdl-buscar-cliente").modal("show");
    // Resetear variables
    currentPage = 1;
    currentSearchTerm = "";
    document.getElementById('txt-buscar-cliente').value = "";
    // Cargar primera página de clientes
    buscarClientes("", 1);
}

function buscarClientes(termino, page = 1) {
    // Mostrar loading
    mostrarLoading(true);

    // Actualizar variables
    currentPage = page;
    currentSearchTerm = termino;

    $.ajax({
        url: '/facturacion/buscar-clientes',
        method: 'POST',
        data: {
            _token: CSRF_TOKEN,
            search: termino,
            page: page
        },
        success: function (response) {
            mostrarLoading(false);
            if (response.estado) {
                mostrarClientes(response.datos.clientes);
                actualizarPaginacion(response.datos.pagination);
            } else {
                mostrarError('Error al buscar clientes: ' + response.mensaje);
            }
        },
        error: function (xhr, status, error) {
            mostrarLoading(false);
            mostrarError('Error de conexión al buscar clientes');
        }
    });
}

// Función con debounce para búsqueda en tiempo real
function buscarClientesConDebounce(termino) {
    // Limpiar timeout anterior
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // Si el término es muy corto, no buscar (excepto si está vacío)
    if (termino.length < 2 && termino !== "") {
        return;
    }

    // Configurar nuevo timeout
    searchTimeout = setTimeout(function () {
        buscarClientes(termino, 1); // Siempre buscar desde la página 1
    }, 300); // 300ms de delay
}

function mostrarClientes(clientes) {
    const tbody = document.getElementById('tbody-clientes');
    const noResultados = document.getElementById('no-resultados');
    const paginationContainer = document.getElementById('pagination-container');

    tbody.innerHTML = '';

    if (clientes.length === 0) {
        noResultados.style.display = 'block';
        paginationContainer.style.display = 'none';
        return;
    }

    noResultados.style.display = 'none';
    paginationContainer.style.display = 'flex';

    clientes.forEach(cliente => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${cliente.nombre} ${cliente.apellidos || ''}</td>
            <td>${cliente.telefono || '-'}</td>
            <td>${cliente.correo || '-'}</td>
            <td>${cliente.ubicacion || '-'}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre} ${cliente.apellidos || ''}')">
                    <i class="fas fa-check"></i> Seleccionar
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function actualizarPaginacion(pagination) {
    totalPages = pagination.total_pages;
    totalRecords = pagination.total_records;

    // Actualizar información de paginación
    document.getElementById('pagination-info').textContent =
        `Mostrando ${pagination.per_page} de ${pagination.total_records} clientes`;
    document.getElementById('page-info').textContent =
        `Página ${pagination.current_page} de ${pagination.total_pages}`;

    // Actualizar botones
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    btnPrev.disabled = pagination.current_page <= 1;
    btnNext.disabled = !pagination.has_more;
}

function cambiarPagina(direction) {
    const newPage = currentPage + direction;
    if (newPage >= 1 && newPage <= totalPages) {
        buscarClientes(currentSearchTerm, newPage);
    }
}

function mostrarLoading(mostrar) {
    const loading = document.getElementById('loading-clientes');
    const tbody = document.getElementById('tbody-clientes');
    const pagination = document.getElementById('pagination-container');

    if (mostrar) {
        loading.style.display = 'block';
        tbody.innerHTML = '';
        pagination.style.display = 'none';
    } else {
        loading.style.display = 'none';
    }
}

function seleccionarCliente(id, nombre) {
    // Cerrar el modal
    $("#mdl-buscar-cliente").modal("hide");
    
    // Cargar la información del cliente en el formulario
    cargarClienteSeleccionado(id, nombre);
    cambiosPendientes = true;
    actualizarOrden();
}

function cargarClienteSeleccionado(id, nombre) {

    window.clienteSeleccionadoId = id;

    if(id == null || id == undefined || id == -1) {
        actualizarPanelClienteInfo({
            nombre: nombre || '',
            apellidos:  '',
            telefono: '-',
            correo: '-',
            ubicacion: '-'
        });
        return;
    }

    $('#loader').show();
    $.ajax({
        url: `${base_path}/facturacion/obtener-cliente`,
        method: 'POST',
        data: {
            _token: CSRF_TOKEN,
            search: id.toString()
        },
        success: function (response) {
            $('#loader').hide();
            if (!response.estado) {
                showError(response.mensaje || 'Error al cargar información del cliente.');
                return;
            }

            if (response.datos) {
                const cliente = response.datos;

                // Actualizar la información en el panel
                actualizarPanelClienteInfo(cliente);

                // Actualizar la variable infoFE con los datos del cliente
                infoFE.incluyeFE = false;
                if (cliente.info_fe) {
                    infoFE.estaConfiguradoFE = true;
                    // Actualizar la estructura completa de info_fe
                    infoFE.info_fe = {
                        id: cliente.info_fe.id || '',
                        codigo_actividad: cliente.info_fe.codigo_actividad || '',
                        tipo_identificacion: cliente.info_fe.tipo_identificacion || '',
                        identificacion: cliente.info_fe.identificacion || '',
                        nombre_comercial: cliente.info_fe.nombre_comercial || '',
                        direccion: cliente.info_fe.direccion || '',
                        correo: cliente.correo || ''
                    };
                    infoFE.info_ced_fe = infoFE.info_fe.identificacion || '';
                    infoFE.info_nombre_fe = infoFE.info_fe.nombre_comercial || (cliente.nombre ? `${cliente.nombre} ${cliente.apellidos || ''}`.trim() : (ordenGestion?.cliente || ''));
                    infoFE.info_correo_fe = infoFE.info_fe.correo || cliente.correo || '';
                } else {
                    infoFE.estaConfiguradoFE = false;
                    infoFE.info_fe = {
                        id: -1,
                        codigo_actividad: '',
                        tipo_identificacion: '',
                        identificacion: '',
                        nombre_comercial: '',
                        direccion: '',
                        correo: cliente.correo || ''
                    };
                    infoFE.info_ced_fe = '';
                    infoFE.info_nombre_fe = cliente.nombre ? `${cliente.nombre} ${cliente.apellidos || ''}`.trim() : (ordenGestion?.cliente || '');
                    infoFE.info_correo_fe = cliente.correo || '';
                }

                mostrarInfoFacturacionElectronica(cliente);
            } else {
                $('#loader').hide();
                showError('No se encontraron datos del cliente en la respuesta:' + response);
                // Mostrar información básica con el nombre que tenemos
                actualizarPanelClienteInfo({
                    nombre: nombre || '',
                    apellidos: '',
                    telefono: '-',
                    correo: '-',
                    ubicacion: '-'
                });
            }
        },
        error: function (xhr, status, error) {
            $('#loader').hide();
            showError('Error al cargar información adicional del cliente');
            // Mostrar información básica aunque falle la carga adicional
            actualizarPanelClienteInfo({
                nombre: nombre|| '',
                apellidos:  '',
                telefono: '-',
                correo: '-',
                ubicacion: '-'
            });
        }
    });

    const inputCliente = document.getElementById('txt-cliente');
    if (inputCliente) {
        inputCliente.value = nombre;
        console.log('Campo de cliente actualizado:', inputCliente.value);

        // Deshabilitar el input para evitar edición
        inputCliente.disabled = true;
        inputCliente.style.backgroundColor = '#f8f9fa';
        inputCliente.style.cursor = 'not-allowed';

        // Cambiar el botón de búsqueda para indicar que hay cliente seleccionado
        const btnBuscar = inputCliente.parentElement.querySelector('button');
        if (btnBuscar) {
            btnBuscar.innerHTML = '<i class="fas fa-user-check"></i>';
            btnBuscar.title = 'Cliente seleccionado - Click para cambiar';
            btnBuscar.classList.remove('btn-outline-primary');
            btnBuscar.classList.add('btn-success');
            console.log('Botón de búsqueda actualizado');
        }
    }

    // Deshabilitar también el input del modal de pago si existe
    const inputClienteModal = document.getElementById('nombreCliente');
    if (inputClienteModal) {
        inputClienteModal.value = nombre;
        inputClienteModal.disabled = true;
        inputClienteModal.style.backgroundColor = '#f8f9fa';
        inputClienteModal.style.cursor = 'not-allowed';
    }

    changeNombreCliente(nombre,false);

    // Actualizar el estado del botón de FE
    actualizarEstadoBotonFE();

    // Mostrar el panel de información del cliente
    mostrarPanelClienteSeleccionado();


}

// Funciones para el panel de información del cliente
function mostrarPanelClienteSeleccionado() {
    document.getElementById('cliente-info-panel').style.display = 'block';
    document.getElementById('cliente-info-panel-2').style.display = 'block';
}

function ocultarPanelClienteSeleccionado() {
    document.getElementById('cliente-info-panel').style.display = 'none';
    document.getElementById('cliente-info-panel-2').style.display = 'none';
}

function actualizarPanelClienteInfo(cliente) {
    
    // Verificar que el panel exist e
    const panel = document.getElementById('cliente-info-panel');
    const panel2 = document.getElementById('cliente-info-panel-2');
    if (!panel) {
        console.error('Panel de cliente no encontrado');
        return;
    }
    if (!panel2) {
        console.error('Panel de cliente no encontrado');
        return;
    }
    // Actualizar nombre completo
    const nombreElement = document.getElementById('cliente-nombre-info');
    const nombreElement2 = document.getElementById('cliente-nombre-info-2');
    if (nombreElement) {
        const nombreCompleto = `${cliente.nombre || ''} ${cliente.apellidos || ''}`.trim();
        nombreElement.textContent = nombreCompleto || '-';
    }
    if (nombreElement2) {
        const nombreCompleto = `${cliente.nombre || ''} ${cliente.apellidos || ''}`.trim();
        nombreElement2.textContent = nombreCompleto || '-';
    }

    // Actualizar teléfono
    const telefonoElement = document.getElementById('cliente-telefono-info');
    const telefonoElement2 = document.getElementById('cliente-telefono-info-2');
    if (telefonoElement) {
        telefonoElement.textContent = cliente.telefono || '-';
    }
    if (telefonoElement2) {
        telefonoElement2.textContent = cliente.telefono || '-';
    }
    // Actualizar correo
    const correoElement = document.getElementById('cliente-correo-info');
    if (correoElement) {
        correoElement.textContent = cliente.correo || '-';
    } 


    // Mostrar detalles extra si hay información adicional
    const detallesExtra = document.getElementById('cliente-detalles-extra');
    const detallesExtra2 = document.getElementById('cliente-detalles-extra-2');
    if (detallesExtra) {
        if (cliente.correo || cliente.ubicacion) {
            detallesExtra.style.display = 'block';
        } else {
            detallesExtra.style.display = 'none';
        }
    } 
}

function mostrarInfoFacturacionElectronica(cliente) {

    const feInfo = document.getElementById('cliente-fe-info');
    const feInfo2 = document.getElementById('cliente-fe-info-2');


    if (infoFE.estaConfiguradoFE) {
        feInfo2.innerHTML = '<i class="fas fa-file-invoice mr-1"></i> Factura Electrónica configurada';
        feInfo2.className = 'btn btn-sm badge-success border-0';
        feInfo2.title = 'Click para editar configuración de FE';
    } else {
        feInfo2.innerHTML = '<i class="fas fa-file-invoice mr-1"></i> Factura Electrónica sin configurar';
        feInfo2.className = 'btn btn-sm badge-warning border-0';
        feInfo2.title = 'Click para configurar FE';
    }

    // Mostrar el panel
    feInfo.style.display = 'block';
}


function limpiarClienteSeleccionado() {
    
    window.clienteSeleccionadoId = null;
   
    const inputCliente = document.getElementById('txt-cliente');
    inputCliente.value = '';
    inputCliente.disabled = false;
    inputCliente.style.backgroundColor = '';
    inputCliente.style.cursor = '';
    inputCliente.placeholder = 'Nombre cliente...';

    // Habilitar también el input del modal de pago si existe
    const inputClienteModal = document.getElementById('nombreCliente');
    if (inputClienteModal) {
        inputClienteModal.value = '';
        inputClienteModal.disabled = false;
        inputClienteModal.style.backgroundColor = '';
        inputClienteModal.style.cursor = '';
        inputClienteModal.placeholder = 'Nombre del Cliente';
    }

    // Restaurar el botón de búsqueda
    const btnBuscar = inputCliente.parentElement.querySelector('button');
    if (btnBuscar) {
        btnBuscar.innerHTML = '<i class="fas fa-search"></i>';
        btnBuscar.title = 'Buscar Cliente';
        btnBuscar.classList.remove('btn-success');
        btnBuscar.classList.add('btn-outline-primary');
    }

    changeNombreCliente('',false);

    // Ocultar ambos paneles de información
    ocultarPanelClienteSeleccionado();

    // Limpiar información de facturación electrónica
    infoFE.incluyeFE = false;
    infoFE.estaConfiguradoFE = false;
    infoFE.info_fe = {
        id: -1,
        codigo_actividad: '',
        tipo_identificacion: '',
        identificacion: '',
        nombre_comercial: '',
        direccion: '',
        correo: ''
    };
    infoFE.info_ced_fe = '';
    infoFE.info_nombre_fe = '';
    infoFE.info_correo_fe = '';


    // Restaurar el botón de FE
    const btnFE = document.getElementById('btn_fe');
    if (btnFE) {
        btnFE.innerHTML = '<i class="fas fa-user"></i> Factura Electrónica: NO';
        btnFE.classList.remove('btn-warning');
        btnFE.classList.add('btn-success');
        console.log('Botón de FE restaurado');
    } else {
        console.error('Botón de FE no encontrado');
    }

    // Limpiar información de envío si es necesario
    infoEnvio.incluye_envio = false;
    infoEnvio.contacto = '';
    infoEnvio.precio = 0;
    infoEnvio.descripcion_lugar = '';
    infoEnvio.descripcion_lugar_maps = '';
    ordenGestion.envio = 0;
    cambiosPendientes = true;
    // Actualizar la orden
    actualizarOrden();
}

function abrirModalEnvio() {
    cargarInfoEnvio();
    $("#mdl_envio").modal("show");
}

function abrirModalFE() {
    try {

        if (!window.clienteSeleccionadoId) {
            showError('Debe seleccionar un cliente antes de configurar la facturación electrónica.');
            return;
        }

        cargarInfoFE();
        $("#mdl_fe").modal("show");
    } catch (error) {
        console.error('Error al abrir modal de FE:', error);
        showError('Error al abrir el modal de facturación electrónica.');
    }
}

function actualizarEstadoBotonFE() {
    const inputCliente = document.getElementById('txt-cliente');
    const btnFE = document.getElementById('btn_fe');
    const clienteSeleccionado = inputCliente && inputCliente.value.trim() !== '';

    if (btnFE) {
        if (!clienteSeleccionado) {
            // Deshabilitar botón cuando no hay cliente
            btnFE.disabled = true;
            btnFE.style.opacity = '0.5';
            btnFE.style.cursor = 'not-allowed';
            btnFE.title = 'Debe seleccionar un cliente primero';
        } else {
            // Habilitar botón cuando hay cliente
            btnFE.disabled = false;
            btnFE.style.opacity = '1';
            btnFE.style.cursor = 'pointer';
            btnFE.title = 'Configurar Facturación Electrónica';
        }
    }
}

function abrirModalNuevoClienteDesdeBusqueda() {
    console.log('Abriendo modal de nuevo cliente desde búsqueda...');

    // Cerrar el modal de búsqueda
    $("#mdl-buscar-cliente").modal("hide");

    // Limpiar el formulario
    limpiarFormularioNuevoCliente();

    // Abrir el modal de nuevo cliente
    $("#mdl-nuevo-cliente").modal("show");
}

function limpiarFormularioNuevoCliente() {
    document.getElementById('nombre_cliente').value = '';
    document.getElementById('apellidos_cliente').value = '';
    document.getElementById('telefono_cliente').value = '';
    document.getElementById('correo_cliente').value = '';
    document.getElementById('ubicacion_cliente').value = '';
}

function guardarNuevoCliente() {

    // Validar campos requeridos
    const nombre = document.getElementById('nombre_cliente').value.trim();
    if (!nombre) {
        showError('El nombre del cliente es obligatorio.');
        return;
    }


    $('#loader').show();

    // Enviar datos al servidor
    $.ajax({
        url: `${base_path}/facturacion/clientes/guardar`,
        method: 'POST',
        data: {
            _token: CSRF_TOKEN,
            mdl_generico_ipt_id: -1,
            mdl_generico_ipt_nombre: document.getElementById('nombre_cliente').value.trim(),
            mdl_generico_ipt_apellidos: document.getElementById('apellidos_cliente').value.trim(),
            mdl_generico_ipt_tel: document.getElementById('telefono_cliente').value.trim(),
            mdl_generico_ipt_correo: document.getElementById('correo_cliente').value.trim(),
            mdl_generico_ipt_ubicacion: document.getElementById('ubicacion_cliente').value.trim()
        },
        success: function (response) {
            $('#loader').hide();

            if (response.estado) {
                showSuccess('Cliente creado exitosamente.');

                // Cerrar el modal de nuevo cliente
                $("#mdl-nuevo-cliente").modal("hide");

                // Cargar el cliente recién creado en el POS
                if (response.datos) {
                    const nombreCompleto = document.getElementById('nombre_cliente').value.trim() + ' ' + document.getElementById('apellidos_cliente').value.trim();
                    setTimeout(() => {
                        // Cargar el cliente recién creado
                        cargarClienteSeleccionado(response.datos, nombreCompleto);

                        showInfo(`Cliente "${nombreCompleto}" ha sido creado y seleccionado automáticamente.`);
                    }, 300);
                } else {
                    console.error('No se recibió ID del cliente creado:', response);
                    showError('Cliente creado pero no se pudo seleccionar automáticamente.');
                }
            } else {
                showError(response.mensaje || 'Error al crear el cliente.');
            }
        },
        error: function (xhr, status, error) {
            $('#loader').hide();
            showError('Error al conectar con el servidor. Inténtalo de nuevo.');
        }
    });
}

function cargarInfoEnvio() {
    $('#mdl_contacto_entrega').val(infoEnvio.contacto);
    $('#mdl_precio_envio').val(infoEnvio.precio);
    $('#mdl_precio_envio').val(infoEnvio.precio);
    $('#incluyeEnvio').prop("checked", infoEnvio.incluye_envio);
    $("#mdl_lugar_entrega").val(infoEnvio.descripcion_lugar);
    $("#mdl_lugar_entrega_maps").val(infoEnvio.descripcion_lugar_maps);
}

function cargarInfoFEConfigurada() {
    console.log('Cargando información de FE configurada para cliente ID:', window.clienteSeleccionadoId);

    $('#loader').show();
    $.ajax({
        url: `${base_path}/facturacion/clientes/obtener-info-fe-cliente`,
        method: 'POST',
        data: {
            _token: CSRF_TOKEN,
            cliente_id: window.clienteSeleccionadoId
        },
        success: function (response) {
            $('#loader').hide();

            if (!response.estado) {
                showError(response.mensaje || 'Error al cargar información de FE');
                cargarInfoFENoConfigurada();
                cargarHtmlInfoFE();
                return;
            }

            // Llenar el modal con los datos de la base de datos
            if (response.datos) {

                infoFE.info_fe = {
                    id: response.datos.id || '',
                    codigo_actividad: response.datos.codigo_actividad || '',
                    tipo_identificacion: response.datos.tipo_identificacion || '',
                    identificacion: response.datos.identificacion || '',
                    nombre_comercial: response.datos.nombre_comercial || '',
                    direccion: response.datos.direccion || '',
                    correo: response.datos.correo || ''
                };
                infoFE.info_ced_fe = infoFE.info_fe.identificacion || '';
                infoFE.info_nombre_fe = infoFE.info_fe.nombre_comercial || (ordenGestion?.cliente || '');
                infoFE.info_correo_fe = infoFE.info_fe.correo || '';
            } else {
                cargarInfoFENoConfigurada();
            }

            cargarHtmlInfoFE();
        },
        error: function (xhr, status, error) {
            $('#loader').hide();
            showError('Error al conectar con el servidor para cargar información de FE');
            cargarInfoFENoConfigurada();
            cargarHtmlInfoFE();
        }
    });
}

function cargarInfoFENoConfigurada() {
    infoFE.info_fe = {
        id: -1,
        codigo_actividad: '722003',
        tipo_identificacion: '01',
        identificacion: '',
        nombre_comercial: '',
        direccion: '',
        correo: infoFE.info_fe.correo || ''
    };
    infoFE.info_ced_fe = '';
    infoFE.info_nombre_fe = ordenGestion?.cliente || '';
    infoFE.info_correo_fe = infoFE.info_fe.correo || '';
}

function cargarHtmlInfoFE() {
    // Llenar los campos del modal
    $('#codigo_actividad').val(infoFE.info_fe.codigo_actividad || '722003');
    $('#tipo_identificacion').val(infoFE.info_fe.tipo_identificacion || '01');
    $('#numero_identificacion').val(infoFE.info_fe.identificacion || '');
    $('#nombre_comercial').val(infoFE.info_fe.nombre_comercial || '');
    $('#direccion').val(infoFE.info_fe.direccion || '');
    $('#correo_electronico').val(infoFE.info_fe.correo || '');
}

function cargarInfoFE() {

    // Verificar si el cliente ya tiene información de FE
    if (infoFE.estaConfiguradoFE) {
        cargarInfoFEConfigurada();
    } else {
        cargarInfoFENoConfigurada();
    }
    cargarHtmlInfoFE();
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
    console.log('Guardando información de FE...');

    // Obtener el ID del cliente seleccionado
    const inputCliente = document.getElementById('txt-cliente');
    if (!inputCliente || !inputCliente.value.trim()) {
        showError('No hay cliente seleccionado.');
        return;
    }

    // Obtener el ID del cliente (necesitamos buscarlo)
    const clienteId = obtenerIdClienteSeleccionado();
    if (!clienteId) {
        showError('No se pudo obtener el ID del cliente seleccionado.');
        return;
    }

    // Recopilar datos del formulario según la estructura de la base de datos
    const datosFE = {
        cliente_id: clienteId,
        codigo_actividad: $('#codigo_actividad').val(),
        tipo_identificacion: $('#tipo_identificacion').val(),
        identificacion: $('#numero_identificacion').val(), // Campo 'identificacion' en la BD
        nombre_comercial: $('#nombre_comercial').val(),
        direccion: $('#direccion').val(),
        correo: $('#correo_electronico').val(),
        incluye_fe: $('#incluyeFE').prop("checked")
    };

    console.log('Datos a enviar:', datosFE);

    // Mostrar loader
    $('#loader').show();

    // Enviar datos al servidor
    $.ajax({
        url: `${base_path}/facturacion/clientes/guardar-info-fe-cliente`,
        method: 'POST',
        data: {
            _token: CSRF_TOKEN,
            ...datosFE
        },
        success: function (response) {
            $('#loader').hide();
            console.log('Respuesta del servidor:', response);

            if (response.estado) {
                // Actualizar variables locales
                infoFE.incluyeFE = datosFE.incluye_fe;
                infoFE.info_fe = {
                    id: response.datos?.id || infoFE.info_fe.id || '', // Mantener ID existente o usar el nuevo
                    codigo_actividad: datosFE.codigo_actividad,
                    tipo_identificacion: datosFE.tipo_identificacion,
                    identificacion: datosFE.identificacion, // Usar 'identificacion' como en la BD
                    nombre_comercial: datosFE.nombre_comercial,
                    direccion: datosFE.direccion,
                    correo: datosFE.correo
                };
                infoFE.info_ced_fe = infoFE.info_fe.identificacion || '';
                infoFE.info_nombre_fe = infoFE.info_fe.nombre_comercial || (ordenGestion?.cliente || '');
                infoFE.info_correo_fe = infoFE.info_fe.correo || '';

                // Actualizar el panel del cliente
                const cliente = {
                    info_fe: infoFE.info_fe
                };
                mostrarInfoFacturacionElectronica(cliente);

                cerrarModalFe();
                actualizarOrden();

                showSuccess('Información de facturación electrónica guardada correctamente.');
            } else {
                showError(response.mensaje || 'Error al guardar la información de FE.');
            }
        },
        error: function (xhr, status, error) {
            $('#loader').hide();
            console.error('Error al guardar FE:', error);
            showError('Error al conectar con el servidor. Inténtalo de nuevo.');
        }
    });
}

// Función auxiliar para obtener el ID del cliente seleccionado
function obtenerIdClienteSeleccionado() {
    console.log('Obteniendo ID del cliente seleccionado...');
    console.log('ID almacenado:', window.clienteSeleccionadoId);
    return window.clienteSeleccionadoId;
}

function cerrarModalEnvio() {
    $("#mdl_envio").modal("hide");
}

function cerrarModalFe() {
    $("#mdl_fe").modal("hide");
}

function cerrarCaja() {
    var efectivoR = $('#monto_efectivo_input').val();
    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/caja/cerrarcaja`,
        type: 'post',
        dataType: "json",
        data: {
            efectivoReportado: efectivoR,
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
    }).always(function () {
        $('#loader').fadeOut();
    });
}


function cargarOrdenGestion(idOrden) {
    $('#loader').show();
    $.ajax({
        url: `${base_path}/facturacion/pos/cargarOrdenGestion`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idOrden: idOrden
        }
    }).done(function (response) {
        $('#loader').hide();
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }

        ordenGestion = transformarEncabezado(response['datos']);
        if (window._preservarCodigoDescuento !== undefined && window._preservarCodigoDescuento !== null && window._preservarCodigoDescuento !== '') {
            ordenGestion.codigo_descuento = window._preservarCodigoDescuento;
        }
        window._preservarCodigoDescuento = null;
        detalles = transformarDetalles(response['datos']);
        cambiosPendientes = false;
        $('#btnIniciarOrden').fadeOut();
        $('#btnActualizarOrden').fadeIn();
        $('#mdl-ordenes').modal('hide');
        $('#infoHeaderOrden').html("Orden : " + ordenGestion.numero_orden);
        actualizarOrden();
        cargarClienteSeleccionado(ordenGestion.idCliente,ordenGestion.cliente);

    }).fail(function (jqXHR, textStatus, errorThrown) {
        cajaAbierta = false;
        showError("Algo salió mal");

    }).always(function () {
        $('#loader').fadeOut();
    });
}

// Función para transformar solo el encabezado
function transformarEncabezado(phpObject) {
    return {
        id: phpObject.id,
        subtotal: phpObject.subtotal,
        total: phpObject.total,
        total_con_descuento: phpObject.total_con_descuento,
        cliente: phpObject.nombre_cliente,
        codigo_descuento: phpObject.codigo_descuento || '',
        envio: phpObject.envio,
        nueva: phpObject.nueva,
        mesa: phpObject.mesa,
        numero_orden: phpObject.numero_orden,
        mto_pagado: phpObject.mto_pagado,
        pagado: phpObject.pagado == 1,
        idCliente: phpObject.cliente,
        incidentes: phpObject.incidentes || [],
        totalRebajarIncidentes: parseFloat(phpObject.total_rebajar_incidentes) || 0
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
    var idCliente = window.clienteSeleccionadoId == null ? -1 : window.clienteSeleccionadoId;
    $('#loader').fadeIn();

    $.ajax({
        url: `${base_path}/facturacion/pos/iniciarOrden`,
        type: 'post',
        dataType: "json",
        timeout: 60000,
        data: {
            _token: CSRF_TOKEN,
            orden: ordenGestion,
            detalles: detalles,
            idCliente: idCliente
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

    var idCliente = window.clienteSeleccionadoId == null ? -1 : window.clienteSeleccionadoId;

    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/facturacion/pos/actualizarOrden`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            orden: ordenGestion,
            detalles: detalles,
            idCliente: idCliente
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

    var bloqueadoPorIncidente = tieneIncidentes();
    if (bloqueadoPorIncidente) {
        tabla.innerHTML = '<tr><td colspan="7" class="alert alert-warning py-2 mb-0"><i class="fas fa-exclamation-triangle"></i> No se permiten pagos fraccionados cuando hay un incidente. Debe pagar el total completo.</td></tr>';
    }

    detalles.forEach((detalle, index) => {
        const cantPendientePagar = detalle.cantidad - (detalle.cantidad_pagada ?? 0);
        var checkboxDisabled = (ordenGestion.nueva || cantPendientePagar < 1) ? 'disabled' : '';
        if (bloqueadoPorIncidente) {
            checkboxDisabled = 'disabled';
        }
        const checkboxChecked = (ordenGestion.nueva || cantPendientePagar > 0) ? 'checked' : '';

        let totalExtrasAux = 0;
        detalle.extras.forEach(extra => {
            totalExtrasAux = totalExtrasAux + parseFloat(extra.precio);
        });

        let precioExtras = parseFloat(detalle.precio_unidad) + parseFloat(totalExtrasAux);

        let precioFinal = (detalle.cantidad * parseFloat(precioExtras));
        const inputDisabled = checkboxDisabled;
        const row = `<tr>
                        <td><input type="checkbox" class="detalle-checkbox" data-id="${detalle.id}" ${checkboxChecked} ${checkboxDisabled} onchange="actualizarOrden()" /></td>
                        <td>${detalle.producto.nombre}</td>
                        <td>${detalle.cantidad}</td>
                        <td>${(detalle.cantidad_pagada ?? 0)}</td>
                        <td>
                            <input type="number" class="form-control cantidad-a-pagar" min="0" max="${cantPendientePagar}" ${inputDisabled}
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
    var totalRebajarIncidentes = parseFloat(ordenGestion.totalRebajarIncidentes) || 0;

    detallesSeleccionados.forEach(detalle => {
        detalle.objRowAux.querySelector('.total-parcial').innerText = currencyCRFormat(detalle.total);
        detalle.objRowAux = null;
        totalSeleccionadoAux += detalle.total;
    });

    // Aplicar rebaja por incidente al total (igual que el descuento por código)
    totalSeleccionadoAux = Math.max(0, totalSeleccionadoAux - totalRebajarIncidentes);

    totalSeleccionado = totalSeleccionadoAux;
    mtoDescuentoGen = descuentoAplicado + totalRebajarIncidentes;
    var descuentoMasIncidente = descuentoAplicado + totalRebajarIncidentes;
    $('#txt-descuento-pagar_mdl').html(`Descuento: ${descuentoMasIncidente.toLocaleString('es-CR', { style: 'currency', currency: 'CRC' })}`);
    document.getElementById('txt-total-seleccionado').innerText = `Total Seleccionado a Pagar : ${currencyCRFormat(totalSeleccionadoAux)}`;
}

function realizarPagoDividido(montoSinpe, montoEfectivo, montoTarjeta) {
    if (!ordenGestion.nueva) {
        if (cambiosPendientes) {
            showError("Existen modificaciones sin guardar. Por favor, guarde los cambios antes de continuar.");
            return;
        }
    }

    if (tieneIncidentes()) {
        var pagoCompleto = true;
        detalles.forEach(function (detalle) {
            var pendiente = detalle.cantidad - (detalle.cantidad_pagada ?? 0);
            if (pendiente <= 0) return;
            var cb = document.querySelector('.detalle-checkbox[data-id="' + detalle.id + '"]');
            if (!cb || !cb.checked) {
                pagoCompleto = false;
                return;
            }
            var fila = cb.closest('tr');
            var inputCant = fila ? fila.querySelector('.cantidad-a-pagar') : null;
            var cantSel = inputCant ? (parseFloat(inputCant.value) || 0) : 0;
            if (cantSel < pendiente) {
                pagoCompleto = false;
            }
        });
        if (!pagoCompleto) {
            showError('No se permiten pagos fraccionados cuando hay un incidente. Debe pagar el total completo.');
            return;
        }
    }

    var sumaPagos = parseFloat(montoSinpe) + parseFloat(montoTarjeta) + parseFloat(montoEfectivo);

    if (sumaPagos == (totalSeleccionado + parseFloat(ordenGestion.envio))) {
        ordenGestion.cliente = document.getElementById('nombreCliente').value;

        if (detallesSeleccionados.length === 0) {
            // Verificar si todos los detalles están completamente pagados
            const todosPagados = detalles.length > 0 && detalles.every(detalle => {
                const cantidadPagada = detalle.cantidad_pagada ?? 0;
                return cantidadPagada >= detalle.cantidad;
            });
            
            if (!todosPagados) {
                showError('Seleccione al menos un detalle.');
                return;
            }
        }
        var idCliente = window.clienteSeleccionadoId == null ? -1 : window.clienteSeleccionadoId;
        // Asegurar montos como número (evita que no se envíen si eran undefined)
        var mtoSinpe = parseFloat(montoSinpe) || 0;
        var mtoEfectivo = parseFloat(montoEfectivo) || 0;
        var mtoTarjeta = parseFloat(montoTarjeta) || 0;
        // Incluir montos también en orden por si el body se trunca o se pierden en la raíz
        ordenGestion.mto_sinpe = mtoSinpe;
        ordenGestion.mto_efectivo = mtoEfectivo;
        ordenGestion.mto_tarjeta = mtoTarjeta;
        $('#mdl-loader-pago').modal("show");
        $.ajax({
            url: `${base_path}/facturacion/pos/pagarOrden`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                mto_sinpe: mtoSinpe,
                mto_efectivo: mtoEfectivo,
                mto_tarjeta: mtoTarjeta,
                idCliente: idCliente,
                orden: ordenGestion,
                infoFE: infoFE,
                envio: infoEnvio,
                detalles: detallesSeleccionados
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
            let mensajeError = "Algo salió mal";
            
            // Intentar extraer el mensaje del error desde la respuesta del servidor
            if (jqXHR.responseJSON && jqXHR.responseJSON.mensaje) {
                mensajeError = jqXHR.responseJSON.mensaje;
            } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                mensajeError = jqXHR.responseJSON.message;
            } else if (jqXHR.responseText) {
                try {
                    const respuesta = JSON.parse(jqXHR.responseText);
                    mensajeError = respuesta.mensaje || respuesta.message || mensajeError;
                } catch (e) {
                    // Si no es JSON válido, usar el texto de respuesta si existe
                    if (jqXHR.status === 0) {
                        mensajeError = "Error de conexión. Verifique su conexión a internet.";
                    } else if (jqXHR.status === 404) {
                        mensajeError = "Recurso no encontrado (404)";
                    } else if (jqXHR.status === 500) {
                        mensajeError = "Error interno del servidor (500)";
                    } else {
                        mensajeError = `Error ${jqXHR.status}: ${errorThrown || textStatus}`;
                    }
                }
            } else if (jqXHR.status === 0) {
                mensajeError = "Error de conexión. Verifique su conexión a internet.";
            } else {
                mensajeError = `Error ${jqXHR.status}: ${errorThrown || textStatus}`;
            }
            
            showError(mensajeError);
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
        url: `${base_path}/facturacion/pos/cargarPosProductos`,
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

    // Verificar si tiposAux existe
    if (!tiposAux) {
        console.warn("No hay datos para procesar en tiposAux");
        generarTipos();
        seleccionarTipo(0);
        return;
    }

    // Acceder directamente a los datos
    Object.values(tiposAux).forEach(tipo => {
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
                            tipoComanda: producto.comanda || '',
                            cantidad: producto.cantidad || -1,
                            cantidad_original: producto.cantidad || -1,
                            tipoProducto: producto.tipoProducto || -1,
                            extras: extrasAux,
                            es_promocion: producto.es_promocion || 'N',
                            categoria: categoria.categoria || '', // Agregar nombre de categoría
                            categoria_id: categoria.id || null, // Agregar ID de categoría
                            descripcion: producto.descripcion || '' // Agregar descripción
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

function cerrarModalFE() {
    $("#mdl_fe").modal("hide");
}

function changeFacturacionElectronica() {
    console.log('Cambiando estado de facturación electrónica...');
    console.log('Estado actual - estaConfiguradoFE:', infoFE.estaConfiguradoFE);
    console.log('Estado actual - incluyeFE:', infoFE.incluyeFE);

    // Solo cambiar estado si el cliente ya tiene FE configurado
    if (infoFE.estaConfiguradoFE) {
        // Cambiar el estado de incluyeFE
        infoFE.incluyeFE = !infoFE.incluyeFE;

        console.log('Nuevo estado - incluyeFE:', infoFE.incluyeFE);

        // Actualizar el botón btn_fe según el nuevo estado
        const btnFE = document.getElementById('btn_fe');
        if (btnFE) {
            if (infoFE.incluyeFE) {
                // FE activada
                btnFE.innerHTML = '<i class="fas fa-user"></i> Factura Electrónica: SÍ';
                btnFE.classList.remove('btn-success');
                btnFE.classList.add('btn-warning');
                btnFE.title = 'Factura Electrónica activada - Click para desactivar';
                console.log('Botón FE actualizado a: SÍ');
            } else {
                // FE desactivada
                btnFE.innerHTML = '<i class="fas fa-user"></i> Factura Electrónica: NO';
                btnFE.classList.remove('btn-warning');
                btnFE.classList.add('btn-success');
                btnFE.title = 'Factura Electrónica desactivada - Click para activar';
                console.log('Botón FE actualizado a: NO');
            }
        } else {
            console.error('Botón btn_fe no encontrado');
        }

        // Actualizar la orden para reflejar los cambios
        actualizarOrden();

        // Mostrar mensaje informativo
        const mensaje = infoFE.incluyeFE ?
            'Facturación electrónica activada para este cliente' :
            'Facturación electrónica desactivada para este cliente';
        showInfo(mensaje);

    } else {
        showInfo('Cliente no tiene FE configurado, abriendo modal para configurar...');
        // Si no está configurado, abrir el modal para configurar
        abrirModalFE();
    }
}