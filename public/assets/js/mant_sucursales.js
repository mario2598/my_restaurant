window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
  $("#input_buscar_sucursal").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_sucursal tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});


function initialice() {
    var t=  document.getElementById('mdl_sucursal_ipt_descripcion');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 50) 
           this.value = this.value.slice(0,50); 
    });

    
}


/** modales Sucursal */
/**
 * Abre el modal y carga los datos correspondientes
 * @param {id de la sucursal} id 
 * @param {descripcion o nombre de la sucursal} desc 
 */
function editarSucursal(id,desc,bodega,impresora) {
  $('#mdl_sucursal_ipt_descripcion').val(desc);
  $('#mdl_sucursal_ipt_id').val(id);

  $('#mdl_sucursal').modal('show');
}

/**
 * Cierra el modal de sucursales
 */
function cerrarModalSucursal(){
  $('#mdl_sucursal').modal('hide');
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevaSucursal(){
  $('#mdl_sucursal_ipt_descripcion').val("");
  $('#mdl_sucursal_ipt_id').val('-1');
  $('#mdl_sucursal').modal('show');
}

function eliminarSucursal(id){
  swal({
    title: 'Seguro de inactivar la sucursal?',
    text: 'No podra deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#idSucursalEliminar').val(id);
        $('#frmEliminarSucursal').submit();
        
      } else {
        swal.close();
      }
    });
 
  
}