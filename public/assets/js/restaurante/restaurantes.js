window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
  $("#input_buscar_generico").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_generico tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});


function initialice() {

}

function editarRestaurante(id) {
  if(id != null && id != undefined){
    $('#idEditarRestaurante').val(id);
    $('#frmEditarRestaurante').submit();
  }else{
    setError("Editar Restaurante","ID incorrecto.")
  }
}

function editarMobiliario(id) {
  if(id != null && id != undefined){
    $('#idEditarRestauranteMobiliario').val(id);
    $('#frmEditarRestauranteMobiliario').submit();
  }else{
    setError("Editar Restaurante","ID incorrecto.")
  }
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevo(){
  alert('nuevo');
}

function inactivar(id){
  swal({
    title: 'Seguro de inactivar el restaurante?',
    text: 'No podra deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#idGenericoEliminar').val(id);
        $('#frmEliminarGenerico').submit();
        
      } else {
        swal.close();
      }
    });
 
  
}