window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {
 // console.log(salones);
}

function cambiarSalon(salon) {
  $("#restaurante_contenedor_mobiliario").empty();
  limpiarModalEditarSalon();
  salones.forEach(s => {
    if (s.id == salon) {
    //  console.log("Cantidad de mobiliario: " + s.mobiliario.length);
      s.mobiliario.forEach(m => {
       // console.log("Mobiliario -> " + m);
        $("#restaurante_contenedor_mobiliario").append(crearMobiliario(m));
      });
      $("#restaurante_contenedor_mobiliario").append(crearBotonAgregar(salon));
      actualizarEditarSalon(s.id, s.nombre, s.ubicacion_detallada);
    }
  });

}

function limpiarModalEditarSalon() {
  //nuevo
  $("#btn-agregar-salon").html("Agregar");
  $("#mdl_salon_btn_eliminar").hide();
  $("#mdl_salon_ipt_id").val("-1");
  $("#mdl_salon_ipt_nombre").val("");
  $("#mdl_salon_ipt_ubicacion").val("");
}

function actualizarEditarSalon(id, nombre, ubicacion) {
  if (id > 0) {
    //editar
    $("#btn-agregar-salon").html("Editar");
    $("#mdl_salon_btn_eliminar").show();
    $("#mdl_salon_ipt_id").val(id);
    $("#mdl_salon_ipt_nombre").val(nombre);
    $("#mdl_salon_ipt_ubicacion").val(ubicacion);
    //Eliminar
    $("#frm_eliminar_salon_id_salon").val(id);
  }
}

function eliminarSalon(){
  swal({
    title: 'Seguro que desea eliminar el salón?',
    text: 'No podrá deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $("#frmEliminarSalon").submit();
      } else {
        swal.close();
      }
    });
}

function crearMobiliario(mobiliario) {
  let cardMobiliario = '<div class="col-12 col-md-6 col-lg-3"> <div class="card card-primary"> <div class="card-header"> <h4>';
  cardMobiliario += mobiliario.nombre;
  cardMobiliario += '</h4>  </div> <div class="card-body">';
  cardMobiliario += '<p> No.Mesa <strong>' + mobiliario.numero_mesa + '<strong><br> ';
  cardMobiliario += ' Estado <strong>';
  if (mobiliario.estado == "D") {
    cardMobiliario += 'Disponible';
  } else if (mobiliario.estado == "O") {
    cardMobiliario += 'Ocupada';

  } else if (mobiliario.estado == "I") {
    cardMobiliario += 'Inactiva';

  } else {
    cardMobiliario += 'Error.';
  }
  cardMobiliario += '<strong></p>';
  cardMobiliario += ' <a class="btn btn-info" style="width:100%; text-aling:center; margin-bottom:15px; color:white;" onclick="goEditarMobiliario(' + mobiliario.id_mxs + ')"> Editar </a>';
  cardMobiliario += ' <a class="btn btn-warning" style="width:100%; text-aling:center; margin-bottom:15px; color:white;" onclick="inactivarMobiliario(' + mobiliario.id_mxs + ')"> Inactivar </a>';
  cardMobiliario += ' <a href="#" class="btn btn-danger" style="width:100%;text-aling:center;color:white;" onclick="eliminarMobiliario(' + mobiliario.id_mxs + ')"> Eliminar </a>';

  cardMobiliario += ' </div> </div> </div>';

  return cardMobiliario;
}

function goEditarMobiliario(mobiliario) {
  if (mobiliario != null && mobiliario != undefined) {
    $("#idEditarMobiliario").val(mobiliario);
    $("#frmgoEditarMobiliario").submit();
  }

}

function crearBotonAgregar(salon) {
  return '<div class="d-flex flex-column justify-content-center"> <a href="javascript:void(0)" onclick="goAgregarMobiliario(' + salon + ')" class="menu-toggle nav-link has-dropdown"><i class="fa fa-plus-circle" style="font-size:4.3rem;opacity:0.9"></i></a></div>'

}

function goAgregarMobiliario(salon) {
  if (salon != null && salon != undefined) {
    $("#idSalonAgregarMobiliario").val(salon);
    $("#frmgoAgregarMobiliario").submit();
  }
}

function inactivarMobiliario(idMobiliario) {
  $("#id_mxs_inactivar").val(idMobiliario);
  $("#frmInactivarMobiliario").submit();
}

function eliminarMobiliario(idMobiliario) {
  swal({
    title: 'Seguro que desea eliminar el mobiliario de este salón?',
    text: 'No podrá deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $("#id_mxs_eliminar").val(idMobiliario);
        $("#frmEliminarMobiliario").submit();
      } else {
        swal.close();
      }
    });
}