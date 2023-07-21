window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {
}



function rechazarIngresoGasto(gasto,ingreso) {
  
  swal({
    title: 'Confirmar?',
    text: 'Deseas rechazar este gasto ? ',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        $('#idIngreso').val(ingreso);
        $('#idIngresoGastoRechazar').val(gasto);
        $('#formIngresoGastoRechazar').submit();
      } else {
        swal('No se rechazo el gasto!');
      }
    });
}

function eliminarIngresoAdmin(ingreso) {
  
  swal({
    title: 'Eliminar Ingreso?',
    text: 'Se eliminaran los gastos relacionados. ',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        $('#idIngresoEliminar').val(ingreso);
        $('#formEliminarIngreso').submit();
      } else {
        swal('No se elimino el ingreso!');
      }
    });
}



function confirmarIngreso(ingreso) {
  
  swal({
    title: 'Confirmar Ingreso?',
    text: 'Se confirmaran los gastos relacionados. ',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        $('#idIngresoAprobar').val(ingreso);
        $('#formAprobarIngreso').submit();
      } else {
        swal('No se aprobo el ingreso!');
      }
    });
}


function rechazarIngreso(ingreso) {
  
  swal({
    title: 'Rechazar Ingreso?',
    text: 'Se rechazaran los gastos relacionados. ',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        $('#idIngresoRechazar').val(ingreso);
        $('#formRechazarIngreso').submit();
      } else {
        swal('No se aprobo el ingreso!');
      }
    });
}


function tickete(id) {
  $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/${id}`);
  document.getElementById('btn-pdf').click();
}

function ticketeParcial(id) {
  $("#btn-pdf").prop('href', `${base_path}/impresora/tiquete/ruta/parcial/${id}`);
  document.getElementById('btn-pdf').click();
}
