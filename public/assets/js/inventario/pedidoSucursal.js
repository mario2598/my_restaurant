window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var pedido_final = [];

function initialice() {

}

function pedidoConProductos() {
    let cantidad = 0;
    pedido.forEach(p => {
        if (p.cantidad > 0) {
            pedido_final.push(p);
            cantidad++;
        }
    });

    if (cantidad > 0) {
        return true;
    } else {
        return false;
    }

}

function aplicarPedidoSucursal(pedidoId) {
    let detalle = $("#detalle_pedido").val();
    let sucursal = $("#sucursal").val();

    if (!pedidoConProductos()) {
        setError('Pedido vacío!', 'Debe seleccionar los productos del pedido.');
    } else if (sucursal == "-1" || sucursal == null) {
        setError('Destino vacío!', 'Debe seleccionar la bodega de solicitud.');
        $("#sucursal").focus();
    } else {
        $.ajax({
            url: `${base_path}/inventario/pedido/iniciarPedido`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                det: detalle,
                id: pedidoId,
                pedido: pedido_final,
                bodega: sucursal
            }
        }).done(function (res) {
            //console.log(res);
            if (!res['estado']) {
                setError('Error!', res['mensaje']);
            } else {
                setSuccess('Realizar pedido.', res['mensaje']);
                window.setTimeout(function () {
                    window.location.href = `${base_path}/inventario/sucursal/pedidos/pendientes`;
                  }, 1500);
            }

            //console.log(res['estado']);

        }).fail(function (jqXHR, textStatus, errorThrown) {
            setError('Error!', 'Algo salio mal!');
            window.setTimeout(function () {
                window.location.href = `${base_path}/inventario/sucursal/pedido`;
              }, 1500);
        });
    }

}

function eliminarProductoPedido(id) {

    const productoDevAux = pedido.find(element => element.id == id);
    if (productoDevAux.cantidad < 1) {
        setError('Vacío!', 'Pedido esta vacío');
        return;
    } else {
        pedido.find(element => element.id == id).cantidad--;
    }

    actualizarPedido();
}

function agregarProductoPedido(id) {

    const productoInvAux = inventarioProductos.find(element => element.id == id);
    if (productoInvAux.cantidad < 1) {
        setError('Vacío!', 'El lote esta vacío');
        return;
    } else {
        pedido.find(element => element.id == id).cantidad++;
    }
    actualizarPedido();
}

function crearColumnaInv(producto) {
    let texto = "<tr>";
    texto += "<td class='text-center'>" + producto.codigo_barra + "</td>";
    texto += "<td class='text-center'>" + producto.nombre_categoria + "</td>";
    texto += "<td  class='text-center''>" + producto.nombre + "</td>";
    texto += '<td class="text-center"><button  class="btn btn-icon btn-success" onclick="agregarProductoPedido(' + producto.id + ')"' +
        'style="color: blanchedalmond"><i class="fas fa-plus"></i></button></td>';
    texto += "</tr>";

    $("#tbody_inventario").append(texto);

}

function crearColumnaDev(producto) {
    let texto = "<tr>";
    texto += "<td class='text-center'>" + producto.codigo_barra + "</td>";
    texto += "<td class='text-center'>" + producto.nombre_categoria + "</td>";
    texto += "<td class='text-center'>" + producto.nombre + "</td>";
    texto += "<td class='text-center'>" + producto.cantidad + "</td>";
    texto += '<td class="text-center"><button  class="btn btn-icon btn-success" onclick="eliminarProductoPedido(' + producto.id + ')"' +
        'style="color: blanchedalmond"><i class="fas fa-minus"></i></button></td>';
    texto += "</tr>";

    $("#tbody_pedido").append(texto);
}


function actualizarPedido() {
    $("#tbody_pedido").empty();
    $("#tbody_inventario").empty();
    inventarioProductos.forEach(p => {
        crearColumnaInv(p);
    });

    pedido.forEach(p => {
        crearColumnaDev(p);
    });

}
