window.addEventListener("load", initialice, false);
var id_prod_seleccionado = 0;

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

function clickProducto(id){
  $('#idProductoEditar').val(id);
  $('#formEditarProducto').submit();
}

function eliminarProducto(id){
  swal({
    title: 'Seguro de inactivar el producto?',
    text: 'No podra deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#idProductoEliminar').val(id);
        $('#formEliminarProducto').submit();
        
      } else {
        swal.close();
      }
    });
 
  
}

function clickMateriaPrima(id) {
  id_prod_seleccionado = id;
  cargarMateriaPrima() ;
  $("#mdl-materia-prima").modal("show");
}

function cargarMateriaPrima() {

  $.ajax({
      url: '/productoExterno/productos/cargarMpProd',
      type: 'get',
      data: {
          _token: CSRF_TOKEN,
          id_prod_seleccionado: id_prod_seleccionado
      }
  }).done(function (respuesta) {

      if (!respuesta.estado) {
          showError(respuesta.mensaje);
          return;
      }

      $("#tbody-inv").html("");
      respuesta.datos.forEach(p => {
          crearMateriaPrima(p);
      });

  }).fail(function (jqXHR, textStatus, errorThrown) {
      showError("Ocurrió un error consultando el servidor");
  });
}

function crearMateriaPrima(producto) {
  let texto = "<tr>";
  texto += "<td class='text-center'>" + producto.nombre + "</td>";
  texto += "<td class='text-center'>" + producto.cantidad + "</td>";
  texto += "<td  class='text-center''>" +   producto.unidad_medida +"</td>";
  texto += '<td class="text-center"><button  class="btn btn-icon btn-secondary" onclick="eliminarProdMp(' + producto.id_mp_x_prod + ')"' +
      '><i class="fas fa-trash"></i></button></td>';
  texto += "</tr>";

  $("#tbody-inv").append(texto);

}

function eliminarProdMp(id_prod_mp) {
  $.ajax({
      url: '/productoExterno/productos/eliminarMpProd',
      type: 'post',
      data: {
          _token: CSRF_TOKEN,
          id_prod_mp: id_prod_mp
      }
  }).done(function (respuesta) {

      if (!respuesta.estado) {
          showError(respuesta.mensaje);
          return;
      }

      showSuccess("Se elimino correctamente");
      cargarMateriaPrima();
  }).fail(function (jqXHR, textStatus, errorThrown) {
      showError("Ocurrió un error consultando el servidor");
  });
}

function cerrarMateriaPrima(){
  $("#mdl-materia-prima").modal("hide");
}


function agregarMateriaPrimaProducto() {
  let id_prod = $('#select_prod_mp').val();
  let cant = $('#ipt_cantidad_req').val();
  let id_mp_prod = $('#ipt_id_prod_mp').val();
  $.ajax({
      url: '/productoExterno/productos/guardarMpProd',
      type: 'post',
      data: {
          _token: CSRF_TOKEN,
          id_prod_seleccionado: id_prod_seleccionado,
          id_mp_prod: id_mp_prod,
          id_prod: id_prod,
          cant: cant
      }
  }).done(function (respuesta) {

      if (!respuesta.estado) {
          showError(respuesta.mensaje);
          return;
      }

      showSuccess("Se agregó correctamente");
      cargarMateriaPrima();
  }).fail(function (jqXHR, textStatus, errorThrown) {
      showError("Ocurrió un error consultando el servidor");
  });
}