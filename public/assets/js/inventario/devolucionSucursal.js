window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {
   
}

function devolucionConProductos(){
    let cantidad = 0;
    devolucion.forEach(p => {
        cantidad ++;
    });

    if(cantidad > 0){
        return true;
    }
    else{
        return false;
    }
  
}

function aplicarDevoulucionSucursal(sucursalAuth) {
    let detalle = $("#detalle_devolucion").val();
    let sucursal = $("#sucursal").val();
  
    if(!devolucionConProductos()){
      setError('Pedido vacío!','Debe seleccionar los productos de la devolución.');
    }
    else if(sucursal == "-1" || sucursal == null){
      setError('Destino vacío!','Debe seleccionar el destino de la devolución.');
      $("#sucursal").focus();
    }
    else if(sucursal == sucursalAuth){
      setError('Destino incorrecto!','El destino no puede ser el mismo que el despacho.');
      $("#sucursal").focus();
    }
    else {
      $.ajax({
        url: `${base_path}/iniciarDevolucion`,
        type: 'post',
        data: {_token: CSRF_TOKEN,det:detalle,devoluciones:devolucion,destino:sucursal}
      }).done(function( mov ) {
      
        if(mov == 'noInv'){
          window.location.href = `${base_path}/`;
        }else if(mov == 'error'){
          setError('Error!','Algo salio mal!');
          window.location.href = `${base_path}/`;
        }else{
          setSuccess('Pedido generado','Redirigendo al pedido!');
          goMovimientoInv(mov);
        }
      }).fail(function (jqXHR, textStatus, errorThrown){
        setError('Error!','Algo salio mal!');
      });
    }
    
  }

function eliminarProductoDevolucion(id){
   
    const productoDevAux = devolucion.find(element => element.id == id);
    if(productoDevAux.cantidad < 1){
        setError('Vacío!','Devolución esta vacío');
        return;
    }else{
        inventarioLotes.find(element => element.id == id).cantidad ++;
        devolucion.find(element => element.id == id).cantidad --;
    }

    actualizarPedido();
  }

function agregarProductoDevolucion(id){
   
    const productoInvAux = inventarioLotes.find(element => element.id == id);
    if(productoInvAux.cantidad < 1){
        setError('Vacío!','El lote esta vacío');
        return;
    }else{
        devolucion.find(element => element.id == id).cantidad ++;
        inventarioLotes.find(element => element.id == id).cantidad --;
    }
    actualizarPedido();
  }

function crearColumnaInv(producto){
    let texto = "<tr>";
    texto +="<td class='text-center'>"+producto.codigo+"</td>";
    texto +="<td class='text-center'>"+producto.nombre+"</td>";
    texto +="<td  class='text-center''>"+producto.cantidad+"</td>";
    texto +='<td class="text-center"><button  class="btn btn-icon btn-success" onclick="agregarProductoDevolucion('+producto.id+')"'+
    'style="color: blanchedalmond"><i class="fas fa-plus"></i></button></td>';
    texto +="</tr>";
   
    $("#tbody_inventario").append(texto);

}

function crearColumnaDev(producto){
    let texto = "<tr>";
    texto +="<td class='text-center'>"+producto.codigo+"</td>";
    texto +="<td class='text-center'>"+producto.nombre+"</td>";
    texto +="<td class='text-center'>"+producto.cantidad+"</td>";
    texto +='<td class="text-center"><button  class="btn btn-icon btn-success" onclick="eliminarProductoDevolucion('+producto.id+')"'+
    'style="color: blanchedalmond"><i class="fas fa-minus"></i></button></td>';
    texto +="</tr>";
   
    $("#tbody_devolucion").append(texto);
}


function actualizarPedido(){
    $("#tbody_devolucion").empty();
    $("#tbody_inventario").empty();
    inventarioLotes.forEach(p => {
        crearColumnaInv(p);
    });

    devolucion.forEach(p => {
        crearColumnaDev(p);
    });
  
  }
