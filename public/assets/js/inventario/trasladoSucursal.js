window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {
   
}

function trasladoConProductos(){
    let cantidad = 0;
    traslado.forEach(p => {
        cantidad ++;
    });

    if(cantidad > 1){
        return true;
    }
    else{
        return false;
    }
  
}

function aplicarTrasladoSucursal(sucursalAuth) {
    let detalle = $("#detalle_traslado").val();
    let sucursal = $("#sucursal").val();
  
    if(!trasladoConProductos()){
      setError('Pedido vacío!','Debe seleccionar los productos del traslado.');
    }
    else if(sucursal == "-1" || sucursal == null){
      setError('Destino vacío!','Debe seleccionar el destino del traslado.');
      $("#sucursal").focus();
    }
    else if(sucursal == sucursalAuth){
      setError('Destino incorrecto!','El destino no puede ser el mismo que el despacho.');
      $("#sucursal").focus();
    }
    else {
      $.ajax({
        url: `${base_path}/iniciarTrasladoSucursal`,
        type: 'post',
        data: {_token: CSRF_TOKEN,det:detalle,traslados:traslado,destino:sucursal}
      }).done(function( mov ) {
        if(mov == 'noInv'){
          window.location.href = `${base_path}/`;
        }else if(mov == 'error'){
         // setError('Error!','Algo salio mal!');
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

function eliminarProductoTraslado(id){
   
    const productoDevAux = traslado.find(element => element.id == id);
    if(productoDevAux.cantidad < 1){
        setError('Vacío!','Devolución esta vacío');
        return;
    }else{
        inventarioLotes.find(element => element.id == id).cantidad ++;
        traslado.find(element => element.id == id).cantidad --;
    }

    actualizarPedido();
  }

function agregarProductoTraslado(id){
   
    const productoInvAux = inventarioLotes.find(element => element.id == id);
    if(productoInvAux.cantidad < 1){
        setError('Vacío!','El lote esta vacío');
        return;
    }else{
        traslado.find(element => element.id == id).cantidad ++;
        inventarioLotes.find(element => element.id == id).cantidad --;
    }
    actualizarPedido();
  }

function crearColumnaInv(producto){
    let texto = "<tr>";
    texto +="<td class='text-center'>"+producto.codigo+"</td>";
    texto +="<td class='text-center'>"+producto.nombre+"</td>";
    texto +="<td  class='text-center''>"+producto.cantidad+"</td>";
    texto +='<td class="text-center"><button  class="btn btn-icon btn-success" onclick="agregarProductoTraslado('+producto.id+')"'+
    'style="color: blanchedalmond"><i class="fas fa-plus"></i></button></td>';
    texto +="</tr>";
   
    $("#tbody_inventario").append(texto);

}

function crearColumnaTra(producto){
    let texto = "<tr>";
    texto +="<td class='text-center'>"+producto.codigo+"</td>";
    texto +="<td class='text-center'>"+producto.nombre+"</td>";
    texto +="<td class='text-center'>"+producto.cantidad+"</td>";
    texto +='<td class="text-center"><button  class="btn btn-icon btn-success" onclick="eliminarProductoTraslado('+producto.id+')"'+
    'style="color: blanchedalmond"><i class="fas fa-minus"></i></button></td>';
    texto +="</tr>";
   
    $("#tbody_traslado").append(texto);
}


function actualizarPedido(){
    $("#tbody_traslado").empty();
    $("#tbody_inventario").empty();
    inventarioLotes.forEach(p => {
        crearColumnaInv(p);
    });

    traslado.forEach(p => {
       crearColumnaTra(p);
    });
  
  }
