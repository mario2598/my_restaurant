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

var guardandoOrden = false;

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
var detalles = [];


function init() {
    limpiar();
    inicializarMapaContenedores();
    generarTipos();
    seleccionarTipo(0);
    generarSalones();

    inicializarEventoScanner();
}

function inicializarEventoScanner() {
    let is_event = false; // for check just one event declaration
    let input = document.getElementById("scanner");

    input.addEventListener("focus", function () {
        if (!is_event) {
            is_event = true;
            input.addEventListener("keypress", function (e) {
                setTimeout(function () {
                    if (e.keyCode == 13) {
                        scanner(input.value); // use value as you need
                        input.select();
                    }
                }, 500)
            })
        }
    });
    document.addEventListener("keypress", function (e) {
        if (e.target.tagName !== "INPUT") {
            input.focus();
        }
    });
}

function scanner(value) {
    if (value == '') return;
    seleccionarProducto('N', value, true);
}

/**
 * Asigna el valor por defecto de las variables
 */
function limpiar() {
    procentajeServicioRestaurante = 0.1;
    tipoSeleccionado = 0;
    categoriaSeleccionada = 0;
    productoSeleccionado = 0;
    salonSeleccionado = 0;
    mobiliarioSeleccionado = 0;
    clienteSeleccionado = 0;
}

function inicializarMapaContenedores() {
    contenedores.set("categorias", $("#scrl-categorias"));
    contenedores.set("productos", $("#tbody-productos"));
    contenedores.set("tipos", $("#nv-tipos"));
    contenedores.set("orden", $("#tbody-orden"));
    contenedores.set("salones", $("#sel-salones"));
    contenedores.set("mobiliario", $("#sel-mobiliario"));
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
                            class="fas fa-box" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-info px-2" onclick="seleccionarProducto('S','${codigo}')"><i class="fas fa-utensils"
                        aria-hidden="true"></i></button></td>
            </tr>`;
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
            let indice = buscarDetallePrevio(impuestoServicio, codigo);
            if (indice >= 0) {
                actualizarDetalleOrden(indice);
            } else {
                detalles.push(crearDetalleOrden(detalles.length, 1, impuestoServicio, producto, ""));
            }
            //reduce la cantidad para el manejo de existencias mientras se vuelve a refrescar la vista.
            reducirCantidadProducto(codigo);
            actualizarOrden();
        } else {
            swal('Agregar producto', "Existencias agotadas para este producto.", 'error');
        }
    } else {
        swal('Agregar producto', "No se ha podido encotrar el producto con el código indicado.", 'error');
    }
}

function buscarProductoCodigo(codigo, todos = false) {
    if (!isEmpty(codigo)) {
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
    if (cantidad == -1) {
        return true;
    } else if (cantidad == 0) {
        return false;
    } else {
        return true;
    }
}

function buscarDetallePrevio(impuestoServicio, codigoProducto) {
    let indice = -1;
    let contador = 0;
    let aumentar = true;
    detalles.forEach(detalle => {
        if (detalle.producto.codigo == codigoProducto && detalle.impuestoServicio == impuestoServicio) {
            indice = contador;
            aumentar = false;
        }
        if (aumentar) {
            contador++;
        }
    });
    return indice;
}

function actualizarDetalleOrden(indice, aumenta = true) {
    var detalle = detalles[indice];
    if (aumenta) {
        detalle.cantidad = detalle.cantidad + 1;
        detalle.total = detalle.cantidad * detalle.producto.precio;
        if (detalle.impuestoServicio == 'S') {
            let impuestoMesa = 0;
            impuestoMesa = detalle.total -(detalle.total / 1.10);
            detalle.total = detalle.total + impuestoMesa;
        }
        detalles[indice] = detalle;
    } else {
        detalle.cantidad = detalle.cantidad - 1;
        if (detalle.cantidad <= 0) {
            detalles.splice(indice, 1);
        } else {
            detalle.total = detalle.cantidad * detalle.producto.precio;
            if (detalle.impuestoServicio == 'S') {
                let impuestoMesa = 0;
                impuestoMesa = detalle.total -(detalle.total / 1.10);
                detalle.total = detalle.total + impuestoMesa;
            }
            detalles[indice] = detalle;
        }
        aumentarCantidadProducto(detalle.producto.codigo);
    }
    actualizarOrden();
}

function agregarDescripcionDetalle(indice) {
    var detalle = detalles[indice];
    if (detalle.tipo == 'R') {
        swal({
            title: 'Observaciones de la orden?',
            animation: "slide-from-top",
            content: {
                element: 'input',
                attributes: {
                    placeholder: 'Sin cebolla... ',
                    type: 'text',
                },
            },
        }).then((data) => {

            if (data === "" || data == null) {
                return false
            }

            detalle.observacion = data;
            swal('Agregar observación', 'Se agrego la observación.', 'success');

        });
    } else {
        swal('Agregar observación', 'Solo se puede agregar observación a los productos de menú', 'error');
    }
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
        impuestoMesa =  totalAux -(totalAux / 1.10); 
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
        "producto": producto
    };
}

function actualizarOrden() {
    let cards = '';
    let contador = 0;
    let total = 0;
    detalles.forEach(detalle => {
        cards += generarHTMLProductoOrden(contador, detalle.producto.nombre, parseFloat(detalle.producto.precio).toFixed(2), detalle.cantidad, parseFloat(detalle.total).toFixed(2), detalle.producto.codigo, detalle.impuestoServicio);
        let totalAux = detalle.cantidad * parseFloat(detalle.precio_unidad);
        total = total + totalAux;
        if (detalle.impuestoServicio == 'S') {
            impuestoMesa =  totalAux -(totalAux / 1.10); 
            total = total + impuestoMesa;
        }
        contador++;
    });

    $('#txt-total-pagar').html((total).toLocaleString('es-CR', {
        style: 'currency',
        currency: 'CRC',
    }));

    $(contenedores.get("orden")).html(cards);
}

/**
 * Genera el elemento HTML correspondiente a la categoría
 */
function generarHTMLProductoOrden(indice, detalle, precio, cantidad, total, codigo, impuestoServicio = "N") {
    var icono = "fas fa-box text-secondary";
    if (impuestoServicio == "S") {
        icono = "fas fa-utensils text-secondary";
    }
    return `<tr>
                <td><i class="${icono} text-secondary" aria-hidden="true"></i> ${detalle}</td>
                <td style="text-align: center">${cantidad}</th>
                <td style="text-align: center">${precio}</td>
                <td style="text-align: center">${total}</td>
                <td style="text-align: center"><button type="button" class="btn btn-danger px-2" onclick="actualizarDetalleOrden(${indice},false)"><i
                            class="fas fa-trash" aria-hidden="true"></i></button>
                            <button type="button" class="btn btn-warning px-2" title="Agregar observación a la orden" onclick="agregarDescripcionDetalle(${indice})"><i
                            class="fas fa-clipboard" aria-hidden="true"></i></button>
                            
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
}

function generarSalones() {
    var opciones = generarHTMLOpcion("-1", "Seleccionar");
    salones.forEach(salon => {
        opciones += generarHTMLOpcion(salon.id, salon.nombre);
    });
    $(contenedores.get("salones")).html(opciones);
}

function generarHTMLOpcion(valor, detalle) {
    return `<option value='${valor}'>${detalle}</option>`;
}

function seleccionarSalon() {

    salonSeleccionado = $(contenedores.get("salones")).val();

    if (salonSeleccionado != undefined && salonSeleccionado > 0) {
        $.ajax({
            url: '/facturacion/mobiliario',
            type: 'post',
            data: {
                _token: CSRF_TOKEN,
                idSalon: salonSeleccionado
            }
        }).done(function (mobiliario) {
            if (mobiliario != '-1') {
                actualizarMobliario(mobiliario);
            } else {
                actualizarMobliario();
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            actualizarMobliario();
        });
    } else {
        actualizarMobliario();
    }
}

function actualizarMobliario(mobiliario = null) {
    $(contenedores.get("mobiliario")).html(generarHTMLOpcion("-1", "Seleccionar"));
    if (mobiliario != null) {
        var opciones = generarHTMLOpcion("-1", "Seleccionar");
        mobiliario.forEach(mobiliario => {
            opciones += generarHTMLOpcion(mobiliario.id, "#" + mobiliario.numero_mesa + " - " + mobiliario.nombre);
        });
        $(contenedores.get("mobiliario")).html(opciones);
    }
}

/* Métodos de botones de acción */

function seleccionarCliente(id, nombre) {
    $("#txt-id-cliente").val(id);
    $("#txt-cliente").val(nombre);
}

function limpiarOrden() {
    detalles = [];
    reiniciarCantidadesProductos();
    actualizarOrden();
    mobiliario = [];
    mobiliarioSeleccionado = -1;
    $(contenedores.get("mobiliario")).html(generarHTMLOpcion("-1", "Seleccionar"));
    salonSeleccionado = -1;
    $(contenedores.get("salones")).val("-1");
    $("#txt-id-cliente").val("-1");
    guardandoOrden = false;
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
    if (tienePedidosMesa() && $('#sel-mobiliario').val() < 1) {
        swal('Datos incompletos', 'Debe seleccionar la mesa.', 'error');
        return false;
    }

    if (detalles.length < 1) {
        swal('Datos incompletos', 'Debe seleccionar los productos para generar la orden.', 'error');
        return false;
    }

    return true;
}

function tienePedidosMesa() {
    let tienePedidosMesa = false;
    detalles.forEach(detalle => {
        if (detalle.impuestoServicio == "S") {
            tienePedidosMesa = true;
        }
    });

    return tienePedidosMesa;
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
