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
   // Restringir tamaño  de los inputs
    var t=  document.getElementById('mdl_generico_ipt_nombre');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 50) 
           this.value = this.value.slice(0,50); 
    });

    t=  document.getElementById('mdl_generico_ipt_correo');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 100) 
           this.value = this.value.slice(0,100); 
    });

    t=  document.getElementById('mdl_generico_ipt_ubicacion');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 300) 
           this.value = this.value.slice(0,300); 
    });

    t=  document.getElementById('mdl_generico_ipt_tel');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 12) 
           this.value = this.value.slice(0,12); 
    });



}


/** modales  */
/**
 * Abre el modal y carga los datos correspondientes
 * @param {id} id 
 * @param {nombre proveedor} id 
 * @param {descripcion  del proveedor} desc 
 */
function editarGenerico(id,nombre,telefono,correo,ubicacion) {
  $('#mdl_generico_ipt_nombre').val(nombre);
  $('#mdl_generico_ipt_correo').val(correo);
  $('#mdl_generico_ipt_tel').val(telefono);
  $('#mdl_generico_ipt_ubicacion').html(ubicacion);
  $('#mdl_generico_ipt_id').val(id);
  $('#mdl_generico').modal('show');
}

/**
 * Cierra el modal 
 */
function cerrarModalGenerico(){
  $('#mdl_generico').modal('hide');
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevoGenerico(){
  $('#mdl_generico_ipt_id').val('-1');
  $('#mdl_generico_ipt_nombre').val("");
  $('#mdl_generico_ipt_correo').val("");
  $('#mdl_generico_ipt_tel').val("");
  $('#mdl_generico_ipt_ubicacion').html("");
  $('#mdl_generico').modal('show');
}

function eliminarGenerico(id){
  swal({
    title: 'Seguro de inactivar el usuario?',
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

function editarUsuario(id){
  $('#idUsuarioEditar').val(id);
  $('#frmEditarUsuario').submit();
}