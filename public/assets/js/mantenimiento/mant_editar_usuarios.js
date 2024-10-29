var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var usuarioGestion = {
    "id": 0,
    "nombre": "",
    "ape1": "",
    "ape2": "",
    "cedula": "",
    "telefono": "",
    "fecha_nacimiento": "",
    "usuario": "",
    "correo": "",
    "rol": "",
    "sucursal": "",
    "tip_u_co": "U_A_G"
};

$(document).ready(function () {
    cargarUsuario();
});


function cargarUsuario() {
    if (idUsuario > 0) {

        $.ajax({
            url: `${base_path}/mant/usuarios/cargarUsuario`,
            type: 'post',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                idUsuario: idUsuario
            }
        }).done(function (response) {
            if (!response['estado']) {
                cargarUsuarioNuevo();
                showError(response['mensaje']);
                return;
            }
            cargarUsuarioGestion(response['datos']);
            cargarHtmlUsuario();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            showError("Algo salió mal");
        });

    } else {
        cargarUsuarioNuevo();
    }


}

function cargarUsuarioGestion(usuarioAux) {
    idUsuario = usuarioAux.id;
    usuarioGestion = {
        "id": usuarioAux.id,
        "nombre": usuarioAux.nombre,
        "ape1": usuarioAux.ape1,
        "ape2": usuarioAux.ape2,
        "cedula": usuarioAux.cedula,
        "telefono": usuarioAux.telefono,
        "fecha_nacimiento": usuarioAux.fecha_nacimiento,
        "usuario": usuarioAux.usuario,
        "correo": usuarioAux.correo,
        "contra": usuarioAux.contra,
        "rol": usuarioAux.rol,
        "sucursal": usuarioAux.sucursal,
        "tip_u_co": "U_A_G"
    };

}

function cargarUsuarioNuevo() {
    idUsuario = 0;
    $('#lblCambiarPss').fadeOut();
    $('#text-form').html("Crear Usuario Administrativo");
    usuarioGestion = {
        "id": 0,
        "nombre": "",
        "ape1": "",
        "ape2": "",
        "cedula": "",
        "telefono": "",
        "fecha_nacimiento": "",
        "usuario": "",
        "correo": "",
        "contra": "",
        "rol": "",
        "sucursal": "",
        "tip_u_co": "U_A_G"
    };
}

function cargarHtmlUsuario() {

    $('#text-form').html(usuarioGestion.id != 0 ? "Editar Usuario Administrativo" : "Crear Usuario Administrativo");
    $('#nombre').val(usuarioGestion.nombre != null ? usuarioGestion.nombre : "");
    $('#ape1').val(usuarioGestion.ape1 != null ? usuarioGestion.ape1 : "");
    $('#ape2').val(usuarioGestion.ape2 != null ? usuarioGestion.ape2 : "");
    $('#cedula').val(usuarioGestion.cedula != null ? usuarioGestion.cedula : "");
    $('#telefono').val(usuarioGestion.telefono != null ? usuarioGestion.telefono : "");
    $('#nacimiento').val(usuarioGestion.fecha_nacimiento != null ? usuarioGestion.fecha_nacimiento : "");
    $('#usuario').val(usuarioGestion.usuario != null ? usuarioGestion.usuario : "");
    $('#contra').val(usuarioGestion.contra != null ? usuarioGestion.contra : "");
    $('#correo').val(usuarioGestion.correo != null ? usuarioGestion.correo : "");
    $('#rol').val(usuarioGestion.rol != null ? usuarioGestion.rol : "");
    $('#sucursal').val(usuarioGestion.sucursal != null ? usuarioGestion.sucursal : "");
    $('#contra').prop('readonly', usuarioGestion.id != null);
    if (usuarioGestion.id != 0) {
        $('#lblCambiarPss').fadeIn();
    } else {
        $('#lblCambiarPss').fadeOut();
    }
}

function cargarUsuarioHtml() {
    usuarioGestion.id = idUsuario;
    usuarioGestion.nombre = $('#nombre').val();
    usuarioGestion.ape1 = $('#ape1').val();
    usuarioGestion.ape2 = $('#ape2').val();
    usuarioGestion.cedula = $('#cedula').val();
    usuarioGestion.telefono = $('#telefono').val();
    usuarioGestion.contra = $('#contra').val();
    usuarioGestion.fecha_nacimiento = $('#nacimiento').val();
    usuarioGestion.usuario = $('#usuario').val();
    usuarioGestion.correo = $('#correo').val();
    usuarioGestion.rol = $('#rol').val();
    usuarioGestion.sucursal = $('#sucursal').val();
    usuarioGestion.tip_u_co = "U_A_G";
}

function initialice() {

    var t = document.getElementById('mdl_generico_ipt_nombre');
    t.addEventListener('input', function () { // 
        if (this.value.length > 50)
            this.value = this.value.slice(0, 50);
    });

    t = document.getElementById('mdl_generico_ipt_correo');
    t.addEventListener('input', function () { // 
        if (this.value.length > 100)
            this.value = this.value.slice(0, 100);
    });

    t = document.getElementById('mdl_generico_ipt_ubicacion');
    t.addEventListener('input', function () { // 
        if (this.value.length > 300)
            this.value = this.value.slice(0, 300);
    });

    t = document.getElementById('mdl_generico_ipt_tel');
    t.addEventListener('input', function () { // 
        if (this.value.length > 12)
            this.value = this.value.slice(0, 12);
    });
}

function guardarUsuario() {
    cargarUsuarioHtml();

    $.ajax({
        url: `${base_path}/mant/usuarios/usuario/guardar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            usuario: usuarioGestion
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess(response['mensaje']);
        idUsuario = response['datos'];
        cargarUsuario();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function cambiarContraDefault() {
    $("#nueva_contra").val("changeit");
    cambiarContra();
}

function cambiarContra() {
    var contraNew = $("#nueva_contra").val();

    $.ajax({
        url: `${base_path}/mant/usuarios/usuario/seg`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idUsuarioEditar: idUsuario,
            nueva_contra: contraNew
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess(response['mensaje']);
        cerrarModalCambioPss();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function cerrarModalCambioPss() {
    $("#modal_cambio_contra").modal("hide");
}
