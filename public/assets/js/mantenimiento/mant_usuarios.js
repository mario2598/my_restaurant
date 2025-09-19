var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_generico tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    cargarUsuarios();
});

function cargarUsuarios() {

    $.ajax({
        url: `${base_path}/mant/usuarios/cargarUsuarios`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        generarHTMLUsuarios(response['datos']);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function generarHTMLUsuarios(usuarios) {
    var texto = "";
    usuarios.forEach(usuario => {
        var lineas = "";
        var tablaDetalles = "";
        
        // Determinar el color del badge según el estado
        var estadoClass = usuario.estado_codigo === 'USU_ACT' ? 'badge-success' : 'badge-danger';
        var estadoTexto = usuario.estado_codigo === 'USU_ACT' ? 'Activo' : 'Inactivo';
        
        // Botones de activar/inactivar según el estado
        var botonesEstado = '';
        if (usuario.estado_codigo === 'USU_ACT') {
            botonesEstado = `<a onclick="inactivarUsuario(${usuario.id})" title="Inactivar" class="btn btn-warning" style="color:white;cursor:pointer;"><i class="fas fa-user-times"></i></a>`;
        } else {
            botonesEstado = `<a onclick="activarUsuario(${usuario.id})" title="Activar" class="btn btn-success" style="color:white;cursor:pointer;"><i class="fas fa-user-check"></i></a>`;
        }
        
        texto = texto +
            ` <tr>
              <td class="text-center">
                ${usuario.usuario ?? "S/A"}
              </td>
              <td class="text-center">
                ${usuario.cedula ?? "Sin asignar"}
              </td>
              <td class="text-center">
                ${usuario.nombre ?? ""} ${usuario.ape1 ?? ""} ${usuario.ape2 ?? ""}
              </td>
              <td class="text-center">
                ${usuario.correo ?? "Sin asignar"}
              </td>
              <td class="text-center">
                ${usuario.telefono ?? "Sin asignar"}
              </td>
              <td class="text-center">
                ${usuario.sucursal_nombre ?? ""}
              </td>
              <td class="text-center">
                ${usuario.rol_nombre} 
              </td>
              <td class="text-center">
                <span class="badge ${estadoClass}">${estadoTexto}</span>
              </td>
              <td class="text-center" >
                <a onclick="goEditarUsuario(${usuario.id})" title="Editar" class="btn btn-primary" style="color:white;cursor:pointer;"><i class="fas fa-cog"></i></a> 
                ${botonesEstado}
              </td>
            </tr>`;

    });

    $('#tbody_generico').html(texto);
}

function goEditarUsuario(id) {
    var form = document.createElement('form');
    form.setAttribute('method', 'post');
    form.setAttribute('action', `${base_path}/mant/usuarios/usuario`);
    form.style.display = 'none';

    // Agregamos el token CSRF
    var csrfField = document.createElement('input');
    csrfField.setAttribute('type', 'hidden');
    csrfField.setAttribute('name', '_token');
    csrfField.setAttribute('value', CSRF_TOKEN);
    form.appendChild(csrfField);

    // Agregamos el campo idUsuarioEditar
    var idField = document.createElement('input');
    idField.setAttribute('type', 'hidden');
    idField.setAttribute('name', 'idUsuarioEditar');
    idField.setAttribute('value', id);
    form.appendChild(idField);

    // Agregamos el formulario al cuerpo del documento
    document.body.appendChild(form);

    // Enviamos el formulario
    form.submit();

}


function goNuevoUsuario() {
    var form = document.createElement('form');
    form.setAttribute('method', 'post');
    form.setAttribute('action', `${base_path}/mant/usuarios/usuario`);
    form.style.display = 'none';

    // Agregamos el token CSRF
    var csrfField = document.createElement('input');
    csrfField.setAttribute('type', 'hidden');
    csrfField.setAttribute('name', '_token');
    csrfField.setAttribute('value', CSRF_TOKEN);
    form.appendChild(csrfField);

    // Agregamos el campo idUsuarioEditar
    var idField = document.createElement('input');
    idField.setAttribute('type', 'hidden');
    idField.setAttribute('name', 'idUsuarioEditar');
    idField.setAttribute('value', 0);
    form.appendChild(idField);

    // Agregamos el formulario al cuerpo del documento
    document.body.appendChild(form);

    // Enviamos el formulario
    form.submit();

}

function inactivarUsuario(id) {
    swal({
        title: '¿Estás seguro?',
        text: "¿Deseas inactivar este usuario?",
        icon: 'warning',
        buttons: {
            cancel: 'Cancelar',
            confirm: 'Sí, inactivar'
        },
        dangerMode: true
    }).then((confirmed) => {
        if (confirmed) {
            var form = document.createElement('form');
            form.setAttribute('method', 'post');
            form.setAttribute('action', `${base_path}/mant/usuarios/usuario/inactivar`);
            form.style.display = 'none';

            // Agregamos el token CSRF
            var csrfField = document.createElement('input');
            csrfField.setAttribute('type', 'hidden');
            csrfField.setAttribute('name', '_token');
            csrfField.setAttribute('value', CSRF_TOKEN);
            form.appendChild(csrfField);

            // Agregamos el campo idUsuario
            var idField = document.createElement('input');
            idField.setAttribute('type', 'hidden');
            idField.setAttribute('name', 'idUsuario');
            idField.setAttribute('value', id);
            form.appendChild(idField);

            // Agregamos el formulario al cuerpo del documento
            document.body.appendChild(form);

            // Enviamos el formulario
            form.submit();
        }
    });
}

function activarUsuario(id) {
    swal({
        title: '¿Estás seguro?',
        text: "¿Deseas activar este usuario?",
        icon: 'success',
        buttons: {
            cancel: 'Cancelar',
            confirm: 'Sí, activar'
        }
    }).then((confirmed) => {
        if (confirmed) {
            var form = document.createElement('form');
            form.setAttribute('method', 'post');
            form.setAttribute('action', `${base_path}/mant/usuarios/usuario/activar`);
            form.style.display = 'none';

            // Agregamos el token CSRF
            var csrfField = document.createElement('input');
            csrfField.setAttribute('type', 'hidden');
            csrfField.setAttribute('name', '_token');
            csrfField.setAttribute('value', CSRF_TOKEN);
            form.appendChild(csrfField);

            // Agregamos el campo idUsuario
            var idField = document.createElement('input');
            idField.setAttribute('type', 'hidden');
            idField.setAttribute('name', 'idUsuario');
            idField.setAttribute('value', id);
            form.appendChild(idField);

            // Agregamos el formulario al cuerpo del documento
            document.body.appendChild(form);

            // Enviamos el formulario
            form.submit();
        }
    });
}
