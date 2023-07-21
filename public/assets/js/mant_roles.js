window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var bkPermisosRol = '';
$(document).ready(function () {
  $("#input_buscar_generico").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#tbody_generico tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});


function initialice() {
  bkPermisosRol = $('#cont_permisos_roles').html();
   // Restringir tamaño  de los inputs
    var t=  document.getElementById('mdl_generico_ipt_codigo');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 15) 
           this.value = this.value.slice(0,15); 
    });

    t=  document.getElementById('mdl_generico_ipt_rol');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 50) 
           this.value = this.value.slice(0,50); 
    });

}


/** modales  */
/**
 * Abre el modal y carga los datos correspondientes
 * @param {id} id 
 * @param {nombre proveedor} id 
 * @param {descripcion  del proveedor} desc 
 */
function editarGenerico(id,codigo,rol,tipo_gasto,tipo_ingreso,administrador,cierra_caja) {
  $('#formRoles').trigger("reset");
  bkPermisos = $('#cont_permisos_roles').html();
  $('#mdl_generico_ipt_codigo').val(codigo);
  $('#mdl_generico_ipt_rol').val(rol);
  $('#mdl_generico_ipt_id').val(id);
 
  if(administrador == 'S'){
    document.getElementById("administrador").checked = true;
  }
  if(cierra_caja == 'S'){
    document.getElementById("cierra_caja").checked = true;
  }

  $('#mdl_generico_slc_tipo_gasto option[value="'+tipo_gasto+'"]').prop("selected", "selected");
  $('#mdl_generico_slc_tipo_ingreso option[value="'+tipo_ingreso+'"]').prop("selected", "selected");
  $.ajax({
    url: `${base_path}/cargarPermisosRoles`,
    type: 'post',
    data: {_token: CSRF_TOKEN,idRol:id}
  }).done(function( response ) {
    
  if(response == 0){
    iziToast.error({
      title: 'Error!',
      message: 'Algo salio mal, reintentalo..',
      position: 'topRight'
    });
    $('#mdl_generico').modal('hide');

  }else if(response == -1){
    iziToast.error({
      title: 'Error!',
      message: 'Algo salio mal, reintentalo..',
      position: 'topRight'
    });
    $('#mdl_generico').modal('hide');

  }else{
    $('#cont_permisos_roles').html(response);
    $('#mdl_generico').modal('show');

  }

  }).fail(function (jqXHR, textStatus, errorThrown){
    iziToast.error({
      title: 'Error!',
      message: 'Algo salio mal, reintentalo..',
      position: 'topRight'
    });
    $('#mdl_generico').modal('hide');
  });  

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
  $('#cont_permisos_roles').html(bkPermisosRol);
  $('#mdl_generico_ipt_codigo').val("");
  $('#mdl_generico_ipt_rol').val("");
  $('#mdl_generico_ipt_id').val('-1');
  $('#formRoles').trigger("reset");
  $('#mdl_generico').modal('show');
}

function eliminarGenerico(id){
  swal({
    title: 'Seguro de inactivar el rol?',
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