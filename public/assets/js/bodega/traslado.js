window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var listaProductos = [];
var productoAgregar ;
$(document).ready(function () {
  $("#btn_buscar_pro").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_generico tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});


function initialice() {
 

}

function abrirInventario(sucursal){
  if(sucursal == -1){
    setError("Sucursal indefinida","Seleccione la sucursal de donde sacara los productos.");
    $("#sucursal_despacho").focus();
  }else{
    $("#modal_inventario").modal("show");
    $.ajax({
      url: `${base_path}/bodega/inventario/trasladar/cargarInventario`,
      type: 'post',
      data: {_token: CSRF_TOKEN,id:sucursal}
    }).done(function( inventario ) {
       if(inventario == "-1"){
        setError('Error!','No tienes permisos para realizar esta acción!');
        window.location.href = `${base_path}/bodega/inventario/trasladar`;
       }else{
        $("#tbody_inventario").html("");
        $("#tbody_inventario").html(inventario);
       }
      
    }).fail(function (jqXHR, textStatus, errorThrown){
      setError('Error!','Algo salio mal!');
      window.location.href = `${base_path}/bodega/inventario/trasladar`;
    });
  }
  
}

function agregarProductoCantidad(){
  let cantidadIngresar =  $("#cantidad_inventario_input").val();
  cantidadIngresar = parseInt(cantidadIngresar);
  var maximo = parseInt(productoAgregar.cantidad);
  var agregar = true;
  if(cantidadIngresar > maximo){
      setError('Error!','El máximo disponible es de '+maximo);
  }else{

  listaProductos.forEach(p => {
      if(p.codigo ==  productoAgregar.codigo){
        if((p.cantidad + cantidadIngresar) > maximo ){
          setError('Error!','El máximo disponible es de '+maximo);
        }else{
          p.cantidad = p.cantidad + cantidadIngresar;
        }
        agregar = false;
      }
     
    });
    if(agregar){
      listaProductos.push({
        "codigo": productoAgregar.codigo,
        "nombre":productoAgregar.nombre,
        "cantidad": cantidadIngresar
      });
    }
   
  }
  $("#modal_cantidad_inventario").modal("hide");
  actualizarPedido();
}

function actualizarPedido(){
  $("#tbody_pedido").empty();
  listaProductos.forEach(p => {
      crearColumna(p);
  });
  $("#cantidad_inventario_input").val("");

}

function cambiarDespacho(){
  listaProductos = [];
  actualizarPedido();  
}


function iniciarTraslado(){

  let sucEnt = $("#sucursal_entrega").val();
  let sucDes = $("#sucursal_despacho").val();

  if(listaProductos.length < 1){
    setError('Pedido vacío!','Debe seleccionar los productos del pedido.');
    $("#btn_abrir_inventario").focus();
  }
  else if(sucEnt < 1){
    setError('Sin destino!','Debe seleccionar la sucursal destino.');
    $("#sucursal_entrega").focus();
  }else if(sucDes == sucEnt){
    setError('Error!','La sucursal destino no puede ser la misma de despacho.');
    $("#sucursal_entrega").focus();
  }else{
    $.ajax({
      url: `${base_path}/iniciarTraslado`,
      type: 'post',
      data: {_token: CSRF_TOKEN,pedido:listaProductos,destino:sucEnt,despacho:sucDes}
    }).done(function( mov ) {
      if(mov == 'noInv'){
        window.location.href = `${base_path}/bodega/inventario/trasladar`;
      }else if(mov == 'error'){
        setError('Error!','Algo salio mal!');
        window.location.href = `${base_path}/bodega/inventario/trasladar`;
      }else{
        setSuccess('Pedido generado','Redirigendo al pedido!');
        goMovimientoInv(mov);
      }
    }).fail(function (jqXHR, textStatus, errorThrown){
      setError('Error!','Algo salio mal!');
    });
  }
  
}

function crearColumna(producto){
    let texto = "<tr>";
    texto +="<td>"+producto.codigo+"</td>";
    texto +="<td>"+producto.nombre+"</td>";
    texto +="<td onclick='alert('hola');'>"+producto.cantidad+"</td>";
    texto +='<td style="cursor:pointer;" onclick="eliminarColumna('+producto.codigo+')"><i class="fas fa-trash"></i></td>';
    texto +="</tr>";
   
    $("#tbody_pedido").append(texto);

}

function eliminarColumna(codigo){
  var contColumna = 0;
  var columnaEliminar;
  listaProductos.forEach(p => {
    if(p.codigo ==  codigo){
      columnaEliminar = contColumna;
    }
    contColumna++;
  });
  delete listaProductos[columnaEliminar]; 
  actualizarPedido();  
}
function seleccionarProductoInventario(codigo,descripcion,cantidad){
    $("#cantidad_inventario_lbl").html(" Cantidad de "+ descripcion + " a transportar");
    $("#cantidad_inventario_small").html(" Cantidad disponible : "+ cantidad );
    $("#modal_inventario").modal("hide");
    $("#modal_cantidad_inventario").modal("show");
    productoAgregar = {
      "codigo": codigo,
      "nombre":descripcion,
      "cantidad": cantidad
    };
}