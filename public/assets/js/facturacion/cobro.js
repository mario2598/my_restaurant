confirmarGenerarFactura
/**
 * Eventos iniciales
 */
window.addEventListener("load", init, false);
document.addEventListener('DOMContentLoaded', function () {
    //Inicializa scroll para las dos listas
    inicializarScroller('scrl-categorias');
    inicializarScroller('scrl-productos');
    inicializarScroller('scrl-orden-pendiente');
    inicializarScroller('scrl-orden-facturar');
});

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
var categoriaSeleccionada = 0;
var productoSeleccionado = 0;
var salonSeleccionado = 0;
var mobiliarioSeleccionado = 0;
var clienteSeleccionado = 0;
var contenedores = new Map();
var detallesPendientes = [];
var detallesFacturar = [];
var detallesOriginal = [];
var tipoCobro = "P";


function init() {
    inicializarMapaContenedores();
    limpiar();
    actualizarOrden("P");
}


/**
 * Asigna el valor por defecto de las variables
 */
function limpiar() {
    /* Variables para el control de productos a agregar */
    procentajeServicioRestaurante = 0.1;
    tipoSeleccionado = 0;
    categoriaSeleccionada = 0;
    productoSeleccionado = 0;

    /* Variables para el controlde las listas de detalles */
    detallesPendientes = orden["detalles"];
    detallesOriginal = [];
    detallesPendientes.forEach(detalle => {
        detallesOriginal.push(Object.assign({}, detalle));
    });
    detallesFacturar = [];
    tipoCobro = "P";
}

function inicializarMapaContenedores() {
    contenedores.set("pendiente", $("#tbody-orden-pendiente"));
    contenedores.set("total-pendiente", $("#txt-total-pendiente"));
    contenedores.set("facturar", $("#tbody-orden-facturar"));
    contenedores.set("total-facturar", $("#txt-total-facturar"));
    contenedores.set("categorias", $("#scrl-categorias"));
    contenedores.set("productos", $("#tbody-productos"));
    contenedores.set("tipos", $("#nv-tipos"));
}


/**************************************************************************
 * 
 *  Métodos para productos
 * 
 ***************************************************************************/

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
    //console.log("indice: " + indice + " , nombre: " + nombre + " , color: " + color);
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
        cards += generarHTMLProducto(producto.nombre, producto.codigo, producto.precio);
        contador++;
    });

    $(contenedores.get("productos")).html(cards);
}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLProducto(nombre, codigo, precio) {
    return `<tr>
                <td width="40%">${nombre}</td>
                <td width="30%" style="text-align: center">${parseFloat(precio).toFixed(2)}</td>
                <td width="30%" style="text-align: center"><button type="button" class="btn btn-success px-2" onclick="seleccionarProducto('N','${codigo}')"><i
                            class="fas fa-plus" aria-hidden="true"></i></button>
            </tr>`;
}

function validarCantidadProducto(producto) {
    let cantidad = parseInt(producto.cantidad);
    //No requiere validación
    if (cantidad == -1) {
        return true;
    } else if (cantidad == 0) {
        return false;
    } else {
        return true;
    }
}

function buscarDetallePrevio(impuestoServicio, codigoProducto, orden = "P") {
    var indice = -1;
    let detalles = detallesPendientes;
    let contador = 0;
    let aumentar = true;

    if (orden == "F") {
        detalles = detallesFacturar;
    }

    detalles.forEach(detalle => {
        if (detalle.producto.codigo == codigoProducto && detalle.impuestoServicio == impuestoServicio) {
            indice = contador;
            detalle.indice = contador;
            aumentar = false;
        }
        if (aumentar) {
            contador++;
        }
    });
    return indice;
}

function crearDetalleOrdenProducto(indice, cantidad, impuestoServicio, producto) {
    return {
        "id": -1,
        "cantidad": cantidad,
        "impuesto": producto.impuesto,
        "impuestoServicio": impuestoServicio,
        "indice": indice,
        "observacion": "",
        "orden": -1,
        "precio_unidad": producto.precio,
        "producto": producto,
        "tipo": tipos[tipoSeleccionado].codigo,
        "tipoComanda": producto.tipoComanda,
        "total": parseFloat(producto.precio * cantidad).toFixed(2)
    };
}

/**************************************************************************
 * 
 *  Métodos para orden
 * 
 ***************************************************************************/

/**
 * Rellena los rows de la tabla de detalles de alguna de las dos órdenes.
 * @param {*} tipo 
 */
function actualizarOrden(tipo = "P") {
    var cards = '';
    var contador = 0;
    let total = 0;
    let detalles;
    let contenedor;
    let txtTotal;

    if (tipo == "P") {
        detalles = detallesPendientes;
        contenedor = "pendiente";
        txtTotal = "total-pendiente";
    } else {
        detalles = detallesFacturar;
        contenedor = "facturar";
        txtTotal = "total-facturar";
    }

    detalles.forEach(detalle => {
        cards += generarHTMLDetalleOrden(contador, detalle.producto.nombre, parseFloat(detalle.precio_unidad).toFixed(2), detalle.cantidad, parseFloat(detalle.cantidad * detalle.precio_unidad).toFixed(2), detalle.producto.codigo, detalle.impuestoServicio, tipo);
        total += detalle.cantidad * parseFloat(detalle.precio_unidad);
        contador++;
    });
    $(contenedores.get(contenedor)).html(cards);
    $(contenedores.get(txtTotal)).html(parseFloat(total).toFixed(2));
}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLDetalleOrden(indice, detalle, precio, cantidad, total, codigo, impuestoServicio = "N", tipo = "P") {
    var icono = "fas fa-box text-secondary";
    let iconoBoton = "plus";
    let colorBoton = "success";

    if (impuestoServicio == "S") {
        icono = "fas fa-utensils text-secondary";
    }

    if (tipo == "F") {
        iconoBoton = "trash";
        colorBoton = "danger";
    }

    return `<tr>
                <td><i class="${icono} text-secondary" aria-hidden="true"></i> ${detalle}</td>
                <td style="text-align: center">${cantidad}</th>
                <td style="text-align: center">${precio}</td>
                <td style="text-align: center">${total}</td>
                <td style="text-align: center"><button type="button" class="btn btn-${colorBoton} px-2" onclick="actualizarDetalleOrden(${indice},false,'${tipo}')"><i
                            class="fas fa-${iconoBoton}" aria-hidden="true"></i></button>
                </td>
            </tr>`;
}

function actualizarDetalleOrden(indice, aumenta = true, tipo = "P") {

    let detallesDestino = [];
    let detallesOrigen = [];
    let tipoDestino = "";

    if (tipo == "P") {
        detallesOrigen = detallesPendientes;
        detallesDestino = detallesFacturar;
        tipoDestino = "F";
    } else {
        detallesOrigen = detallesFacturar;
        detallesDestino = detallesPendientes;
        tipoDestino = "P";
    }

    var detalle = detallesOrigen[indice];

    if (aumenta) {
        detalle.cantidad = detalle.cantidad + 1;
        detallesOrigen[indice] = detalle;
    } else {
        detalle.cantidad = detalle.cantidad - 1;
        agregarDetalle(detalle, tipoDestino);
        if (detalle.cantidad <= 0) {
            detallesOrigen.splice(indice, 1);
        } else {
            detallesOrigen[indice] = detalle;
        }
    }
    //Actualiza ambas órdenes
    actualizarOrden("P");
    actualizarOrden("F");
}

function agregarDetalle(detalle, tipo = "P") {

    let detalles = [];
    let detalleAgregar;
    let indice = -1;

    if (tipo == "P") {
        detalles = detallesPendientes;
    } else {
        detalles = detallesFacturar;
    }

    indice = buscarDetallePrevio(detalle.impuestoServicio, detalle.producto.codigo, tipo);

    if (indice < 0) {
        detalleAgregar = crearDetalleOrdenDetalle(detalle, detalles.length, 0);
    } else {
        detalleAgregar = detalles[indice];
    }

    detalleAgregar.cantidad += 1;
    detalles[detalleAgregar.indice] = detalleAgregar;

}

function buscarIndiceDetalle(id, tipo = "P") {
    let indice = -1;
    let contador = 0;

    if (tipo == "P") {
        detalles = detallesPendientes;
    } else {
        detalles = detallesFacturar;
    }

    if (!isEmpty(id)) {
        detalles.forEach(detalle => {
            if (detalle.id == id) {
                indice = contador;
            }
            contador++;
        });
        return indice;
    }
    return indice;
}

function crearDetalleOrdenDetalle(detalle, indice, cantidad) {
    return crearDetalleOrden(
        indice,
        detalle.id,
        detalle.orden,
        detalle.tipo,
        cantidad,
        detalle.precio_unidad,
        detalle.impuestoServicio,
        detalle.tipoComanda,
        detalle.producto,
        detalle.fechaCreacion);
}

function crearDetalleOrden(indice, id, orden, tipo, cantidad, precio_unidad, impuestoServicio, tipoComanda, producto,fechaCreacion) {
    return {
        "cantidad": cantidad,
        "id": id,
        "impuesto": producto.impuesto,
        "impuestoServicio": impuestoServicio,
        "indice": indice,
        "observacion": "",
        "orden": orden,
        "precio_unidad": precio_unidad,
        "producto": producto,
        "fechaCreacion": fechaCreacion,
        "tipo": tipo,
        "tipoComanda": tipoComanda,
        "total": parseFloat(precio_unidad * cantidad).toFixed(2)
    };
}

function buscarProductoCodigo(codigo) {
    if (!isEmpty(codigo)) {
        let productos = tipos[tipoSeleccionado].categorias[categoriaSeleccionada].productos;
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

/* Métodos de botones de acción */

function limpiarOrden() {
    detallesPendientes = [];
    detallesFacturar = [];
    detallesOriginal.forEach(detalle => {
        detallesPendientes.push(Object.assign({}, detalle));
    });
    reiniciarCantidadesProductos();
    actualizarOrden("P");
    actualizarOrden("F");
}

function reiniciarCantidadesProductos() {
    tipos.forEach(tipo => {
        tipo.categorias.forEach(categoria => {
            categoria.productos.forEach(producto => {
                producto.cantidad = producto.cantidad_original;
            })
        })
    })
}

function pasarDetalles(destino = "P") {
    //detallesPendientes = [];
    //let contador = 0;
    let detalles = [];

    if (destino == "P") {
        detalles = detallesFacturar;
    } else {
        detalles = detallesPendientes;
    }

    detalles.forEach(detalle => {
        pasarDetalleOrden(detalle, destino);
        /*indice = buscarDetallePrevio(detalle.impuestoServicio, detalle.producto.codigo, destino);
        if (parseInt(indice) < 0) {
            detallesFacturar.push(detalle);
        } else {
            detallePasar = detallesFacturar[indice];
            detallePasar.cantidad += parseInt(detalle.cantidad);
            detallesFacturar[indice] = detallePasar;
        }*/

        //contador++;
    });

    if (destino = "F") {
        detallesPendientes = [];
    } else {
        detallesFacturar = [];
    }
    detallesPendientes = [];
    actualizarOrden("P");
    actualizarOrden("F");
}

function pasarDetalleOrden(detalle, tipo = "P") {
    let detalles = [];
    let detallesRemover = [];
    let detallePasar;
    let indice = -1;
    let indiceRemover = -1;

    if (tipo == "P") {
        detalles = detallesPendientes;
        detallesRemover = detallesFacturar;
        //indiceRemover = buscarIndiceDetalle(detalle.id, "F");
        indiceRemover = buscarDetallePrevio(detalle.impuestoServicio, detalle.producto.codigo, "F");
    } else {
        detalles = detallesFacturar;
        detallesRemover = detallesPendientes;
        //indiceRemover = buscarIndiceDetalle(detalle.id, "P");
        indiceRemover = buscarDetallePrevio(detalle.impuestoServicio, detalle.producto.codigo, "P");
    }

    //indice = buscarIndiceDetalle(detalle.id, tipo);
    indice = buscarDetallePrevio(detalle.impuestoServicio, detalle.producto.codigo, tipo);

    if (indice < 0) {
        //detallePasar = crearDetalleOrdenDetalle(detalle, detalles.length, detalle.cantidad);
        detalles[detalles.length] = detalle;
    } else {
        detallePasar = detalles[indice];
        detallePasar.cantidad += parseInt(detalle.cantidad);
        detalles[indice] = detallePasar;
    }

    //detallesRemover.splice(indiceRemover, 1);
    actualizarIndicesDetalles();
    actualizarIndicesDetalles("F");
}

function actualizarIndicesDetalles(tipo = "P") {
    let contador = 0;
    let detalles = detallesPendientes;

    if (tipo == "F") {
        detalles = detallesFacturar;
    }

    detalles.forEach(detalle => {
        detalle.indice = contador;
        contador++;
    });

    // console.log("");
}

function confirmarOrden() {
    swal({
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
        });
}

function confirmarGenerarFactura() {
    if (validarFormularioFactura()) {
        swal({
                title: 'Desea facturar los artículos seleccionados?',
                text: 'No podrá deshacer esta acción!',
                icon: 'info',
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    swal.close();
                    generarFactura();
                } else {
                    swal.close();
                }
            });
    }
}

function generarFactura() {
    try {
        let tipoFactura = "P";
        swal('Procesar Orden', "Dividiendo orden, espere ..." , 'info');

        $.ajax({
            url: `${base_path}/cocina/facturar/preFacturar`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                id_orden: orden["id"],
                tipoFacturacion: tipoFactura,
                detallesOrden: detallesPendientes,
                detallesOrdenParcial: detallesFacturar
            }
        }).done(function (res) {
            if (!res['estado']) {
                swal('Generar Orden', res['mensaje'], 'error');
            } else {
                let id = res["datos"];

                if (!isEmpty(id)) {
                    swal('Procesar Orden', "Orden dividida correctamente" + id, 'success');
                    redirigirCobro(id);
                } else {
                    swal('Procesar Orden', "No se ha podido redirigir al pago de la nueva orden creada " + id, 'error');
                    setError('Procesar Orden', "No se ha podido redirigir al pago de la nueva orden creada " + id);
                }

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            swal('Procesar Orden', "Algo salió mal.", 'error');
        });

    } finally {

    }
}

function redirigirCobro(id) {
    $("#ipt_id_orden").val(id);
    $("#frm-factrar-orden").submit();
}


function validarFormularioFactura() {
    if (detallesFacturar.length < 1) {
        swal('Datos incompletos', 'Debe seleccionar los detalles a facturar.', 'error');
        return false;
    }

    if (detallesPendientes.length < 1) {
        swal('Datos incompletos', 'La orden principal no puede quedar vacía.', 'error');
        return false;
    }

    return true;
}


/** Métodos generales */

function isEmpty(str) {
    return (!str || 0 === str.length);
}

function isBlank(str) {
    return (!str || /^\s*$/.test(str));
}

/*
    tipos[
        "nombre": "nombre",
        "codigo": "P",
        "color": "#000000",
        "categorias": [
            "id": 0,
            "categoria": "categoria",
            "productos": [
                "id": 0,
                    "nombre": "nombre",
                    "impuesto": 0,
                    "precio": 0,
                    "codigo": "codigo"
            ]
        ]
    ]

    orden[
        "id" = null,
        "estado" = "CR",
        "idCliente" = 0,
        "nombreCliente" = "Nombre",
        "facturaElectronica" = "S",
        "mesaId" = 0,
        "detalles" = [{
            "indice": 0 (Integer),
            "cantidad": 0 (Integer),
            "impuestoServicio": "S" (String),
            "impuesto": % (Double),
            "total": 0 (Integer),
            "observacion" : "" (String)
            "tipo": "R" (String)
            "tipoComanda" : "CO" (String)
            "producto": {
                        "codigo": "codigo" (String)
                        "id": 0 (Integer),
                        "impuesto": 0 (Integer),
                        "nombre": "nombre" (String),
                        "precio": 0(Integer),
                        "tipoComanda":"" (String)
                    }
            }]
    ]

*/
