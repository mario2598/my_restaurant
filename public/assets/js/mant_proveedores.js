window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
  $("#input_buscar_proveedor").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_proveedor tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});


function initialice() {
   // Restringir tamaño  de los inputs
    var t=  document.getElementById('mdl_proveedor_ipt_nombre');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 50) 
           this.value = this.value.slice(0,50); 
    });

    t=  document.getElementById('mdl_proveedor_ipt_descripcion');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 200) 
           this.value = this.value.slice(0,200); 
    });

    
}


/** modales  */
/**
 * Abre el modal y carga los datos correspondientes
 * @param {id} id 
 * @param {nombre proveedor} id 
 * @param {descripcion  del proveedor} desc 
 */
function editarProveedor(id,nombre,desc) {
  $('#mdl_proveedor_ipt_nombre').val(nombre);
  $('#mdl_proveedor_ipt_descripcion').val(desc);
  $('#mdl_proveedor_ipt_id').val(id);
  $('#mdl_proveedor').modal('show');
}

/**
 * Cierra el modal 
 */
function cerrarModalProveedor(){
  $('#mdl_proveedor').modal('hide');
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevoProveedor(){
  $('#mdl_proveedor_ipt_nombre').val("");
  $('#mdl_sucursal_ipt_descripcion').val("");
  $('#mdl_sucursal_ipt_id').val('-1');
  $('#mdl_proveedor').modal('show');
}

function eliminarProveedor(id){
  swal({
    title: 'Seguro de inactivar el proveedor?',
    text: 'No podra deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#idProveedorEliminar').val(id);
        $('#frmEliminarProveedor').submit();
        
      } else {
        swal.close();
      }
    });
 
  
}