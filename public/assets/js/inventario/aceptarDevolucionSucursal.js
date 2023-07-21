window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {
   
}

function devolucionConProductos(){
    let cantidad = 0;
    devolucion.forEach(p => {
        cantidad ++;
    });

    if(cantidad > 1){
        return true;
    }
    else{
        return false;
    }
  
}

function aplicarDevoulucionSucursal(mov) {
    let detalle = $("#detalle").val();
    $.ajax({
      url: `${base_path}/aceptarDevolucionSucursal`,
      type: 'post',
      data: {_token: CSRF_TOKEN,mov_id:mov,det:detalle,inv:inventarioLotes,des:desechos}
    }).done(function( mov ) {
      if(mov == 'noInv'){
        window.location.href = `${base_path}/`;
      }else if(mov == 'error'){
        setError('Error!','Algo salio mal!');
        window.location.href = `${base_path}/`;
      }else{
        setSuccess('Pedido generado','Redirigendo al pedido!');
        window.location.href = `${base_path}/`;
      }
    }).fail(function (jqXHR, textStatus, errorThrown){
      setError('Error!','Algo salio mal!');
    });

    
  }

function eliminarProducto(id){
   
    const productoDevAux = desechos.find(element => element.id == id);
    if(productoDevAux.cantidad < 1){
        setError('Vacío!','Desecho esta vacío');
        return;
    }else{
        inventarioLotes.find(element => element.id == id).cantidad ++;
        desechos.find(element => element.id == id).cantidad --;
    }

    actualizarPedido();
  }

function agregarProducto(id){
   
    const productoInvAux = inventarioLotes.find(element => element.id == id);
    if(productoInvAux.cantidad < 1){
        setError('Vacío!','El lote esta vacío');
        return;
    }else{
        desechos.find(element => element.id == id).cantidad ++;
        inventarioLotes.find(element => element.id == id).cantidad --;
    }
    actualizarPedido();
  }

function crearColumnaInv(producto){
    let texto = "<tr>";
    texto +="<td class='text-center'>"+producto.codigo+"</td>";
    texto +="<td class='text-center'>"+producto.nombre+"</td>";
    texto +="<td  class='text-center''>"+producto.cantidad+"</td>";
    texto +='<td class="text-center"><button  class="btn btn-icon btn-success" onclick="agregarProducto('+producto.id+')"'+
    'style="color: blanchedalmond"><i class="fas fa-plus"></i></button></td>';
    texto +="</tr>";
   
    $("#tbody_inventario").append(texto);

}

function crearColumnaDesecho(producto){
    let texto = "<tr>";
    texto +="<td class='text-center'>"+producto.codigo+"</td>";
    texto +="<td class='text-center'>"+producto.nombre+"</td>";
    texto +="<td class='text-center'>"+producto.cantidad+"</td>";
    texto +='<td class="text-center"><button  class="btn btn-icon btn-success" onclick="eliminarProducto('+producto.id+')"'+
    'style="color: blanchedalmond"><i class="fas fa-minus"></i></button></td>';
    texto +="</tr>";
   
    $("#tbody_desechos").append(texto);
}


function actualizarPedido(){
    $("#tbody_desechos").empty();
    $("#tbody_inventario").empty();
    inventarioLotes.forEach(p => {
        crearColumnaInv(p);
    });

    desechos.forEach(p => {
        crearColumnaDesecho(p);
    });
  
  }
