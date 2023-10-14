window.addEventListener("load", initialice, false);


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