window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

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


function eliminarProdcutoDeMenu(id){
  swal({
    title: 'Seguro de remover el producto del menú?',
    text: 'Requieres permisos de administrados para realizar la solicitud!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#producto_menu_eliminar').val(id);
        $('#form_eliminar_menu').submit();
        
      } else {
        swal.close();
      }
    });
 
  
}