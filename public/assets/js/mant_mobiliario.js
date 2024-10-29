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
    var t=  document.getElementById('mdl_mobiliario_ipt_descripcion');
      t.addEventListener('input',function(){ // 
        if (this.value.length > 240) 
           this.value = this.value.slice(0,240); 
    });
}


/** modales Sucursal */
/**
 * Abre el modal y carga los datos correspondientes
 * @param {id de la sucursal} id 
 * @param {descripcion o nombre de la sucursal} desc 
 */
function editarMobiliario(id,nombre,descripcion,personas,filas,columnas) {
  $('#mdl_mobiliario_ipt_nombre').val(nombre);
  $('#mdl_mobiliario_ipt_personas').val(personas);
  $('#mdl_mobiliario_ipt_filas').val(filas);
  $('#mdl_mobiliario_ipt_columnas').val(columnas);
  $('#mdl_mobiliario_ipt_descripcion').val(descripcion);
  $('#mdl_mobiliario_ipt_id').val(id);

  $('#mdl_mobiliario').modal('show');
  
}

/**
 * Cierra el modal de sucursales
 */
function cerrarModalMobiliario(){
  $('#mdl_mobiliario').modal('hide');
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevoMobiliario(){
  $('#mdl_mobiliario_ipt_nombre').val("");
  $('#mdl_mobiliario_ipt_personas').val("1");
  $('#mdl_mobiliario_ipt_filas').val("1");
  $('#mdl_mobiliario_ipt_columnas').val("1");
  $('#mdl_mobiliario_ipt_descripcion').val("");
  $('#mdl_mobiliario_ipt_id').val('-1');
  $('#mdl_mobiliario').modal('show');
}

function eliminarMobiliario(id){
  swal({
    title: 'Seguro de inactivar el mobiliario?',
    text: 'No podrá deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#idMobiliarioEliminar').val(id);
        $('#frmEliminarMobiliario').submit();
        
      } else {
        swal.close();
      }
    });
}