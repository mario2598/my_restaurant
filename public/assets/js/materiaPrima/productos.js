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
  var t=  document.getElementById('nombre');
  t.addEventListener('input',function(){ // 
    if (this.value.length > 30) 
       this.value = this.value.slice(0,30); 
  });

  t=  document.getElementById('codigo');
  t.addEventListener('input',function(){ // 
    if (this.value.length > 15) 
       this.value = this.value.slice(0,15); 
  });


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