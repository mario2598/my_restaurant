window.addEventListener("load", initialice, false);
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