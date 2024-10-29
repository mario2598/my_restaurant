window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var sucursalInventario = -1;

function initialice() {

}

function desechoVacio() {
    let cantidad = 0;
    desechos.forEach(p => {
      if(p['cantidad'] > 0){
        desechosAplicar.push(p);
        cantidad++;
      }
    });
    if (cantidad > 0) {
        return false;
    } else {
        return true;
    }
}

function cambiarInventario(sucursal) {
    sucursalInventario = sucursal;
    if (sucursal != -1) {
        inventarioLotes = [];
        desechos = [];
        actualizarPedido();
        $.ajax({
            url: `${base_path}/desechos/cambiarInventario`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                suc: sucursal
            }
        }).done(function (lotes) {
            llenarTablas(lotes);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            setError('Error!', 'Algo salio mal!');
        });
    } else {
        setInfo('Sucursal vacía!', 'Selecciona una sucursal para cargar el inventario.');
    }
}

function llenarTablas(lotes) {
    inventarioLotes = [];
    desechos = [];

    lotes.forEach(l => {

        inventarioLotes.push({
            "id": l['id'],
            "codigo": l['codigo'],
            "nombre": l['nombre'],
            "cantidad": l['cantidad'],
        });

        desechos.push({
            "id": l['id'],
            "codigo": l['codigo'],
            "nombre": l['nombre'],
            "cantidad": 0,
        });


    });
    actualizarPedido();
}

function aplicarDesechos() {
    if (!desechoVacio()) {
        if (sucursalInventario != -1) {
            let detalle = $("#detalle").val();
            $.ajax({
                url: `${base_path}/desechos/confirmar`,
                type: 'post',
                data: {
                    _token: CSRF_TOKEN,
                    sucursal: sucursalInventario,
                    det: detalle,
                    des: desechosAplicar
                }
            }).done(function (response) {
                if (response == '-1') {
                    window.location.href = `${base_path}/`;
                } else if (response == 'transactionError') {
                    setError('Error!', 'Algo salio mal, reintentalo!');
                    window.location.href = `${base_path}/desechos/agregar`;
                } else if (response == 'noSucursal') {
                    setError('Sucursal vacía!', 'Debe seleccionar la sucursal!');
                } else if (response == 'noDesechos') {
                    setError('Sucursal vacía!', 'Debe seleccionar la sucursal!');
                } else {
                    setSuccess('Desecho generado', 'Redirigendo al detalle de movimiento!');
                    goMovimientoInv(response);
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                setError('Error!', 'Algo salio mal!');
                window.location.href = `${base_path}/desechos/agregar`;
            });
        } else {
            setInfo('Sucursal vacía!', 'Selecciona una sucursal.');
        }
    } else {
        setError('Desecho vacío!', 'Debe indicar los productos a desechar.');
    }
}

function eliminarProducto(id) {
    const productoDevAux = desechos.find(element => element.id == id);
    if (productoDevAux.cantidad < 1) {
        setError('Vacío!', 'Desecho esta vacío');
        return;
    } else {
        inventarioLotes.find(element => element.id == id).cantidad++;
        desechos.find(element => element.id == id).cantidad--;
    }
    actualizarPedido();
}

function agregarProducto(id) {
    const productoInvAux = inventarioLotes.find(element => element.id == id);
    if (productoInvAux.cantidad < 1) {
        setError('Vacío!', 'El lote esta vacío');
        return;
    } else {
        desechos.find(element => element.id == id).cantidad++;
        inventarioLotes.find(element => element.id == id).cantidad--;
    }
    actualizarPedido();
}

function crearColumnaInv(producto) {
    let texto = "<tr>";
    texto += "<td class='text-center'>" + producto.codigo + "</td>";
    texto += "<td class='text-center'>" + producto.nombre + "</td>";
    texto += "<td  class='text-center''>" + producto.cantidad + "</td>";
    texto += '<td class="text-center"><a href="#" class="btn btn-icon btn-success" onclick="agregarProducto(' + producto.id + ')"' +
        'style="color: blanchedalmond"><i class="fas fa-plus"></i></a></td>';
    texto += "</tr>";
    $("#tbody_inventario").append(texto);

}

function crearColumnaDesecho(producto) {
    let texto = "<tr>";
    texto += "<td class='text-center'>" + producto.codigo + "</td>";
    texto += "<td class='text-center'>" + producto.nombre + "</td>";
    texto += "<td class='text-center'>" + producto.cantidad + "</td>";
    texto += '<td class="text-center"><a href="#" class="btn btn-icon btn-success" onclick="eliminarProducto(' + producto.id + ')"' +
        'style="color: blanchedalmond"><i class="fas fa-minus"></i></a></td>';
    texto += "</tr>";
    $("#tbody_desechos").append(texto);
}


function actualizarPedido() {
    $("#tbody_desechos").empty();
    $("#tbody_inventario").empty();
    inventarioLotes.forEach(p => {
        crearColumnaInv(p);
    });
    desechos.forEach(p => {
        crearColumnaDesecho(p);
    });

}
