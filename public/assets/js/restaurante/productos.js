window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    $("#btn_buscar_pro").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_generico tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});


function initialice() {
    var t = document.getElementById('nombre');
    t.addEventListener('input', function () { // 
        if (this.value.length > 30)
            this.value = this.value.slice(0, 30);
    });

    t = document.getElementById('codigo');
    t.addEventListener('input', function () { // 
        if (this.value.length > 15)
            this.value = this.value.slice(0, 15);
    });


}

function clickProducto(id) {
    $('#idProductoEditar').val(id);
    $('#formEditarProducto').submit();
}

function eliminarProducto(id) {
    swal({
            title: 'Seguro de inactivar el producto?',
            text: 'No podra deshacer esta acción!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                swal.close();
                $('#idProductoEliminar').val(id);
                $('#formEliminarProducto').submit();

            } else {
                swal.close();
            }
        });


}

function clickMateriaPrima(id) {
    id_prod_seleccionado = id;
    cargarMateriaPrima() ;
    $("#mdl-materia-prima").modal("show");
}

function clickExtras(id) {
    id_prod_seleccionado = id;
    cargarExtras() ;
    $("#mdl-extras").modal("show");
}


function cargarMateriaPrima() {

    $.ajax({
        url: '/menu/productos/cargarMpProd',
        type: 'get',
        data: {
            _token: CSRF_TOKEN,
            id_prod_seleccionado: id_prod_seleccionado
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        $("#tbody-inv").html("");
        respuesta.datos.forEach(p => {
            crearMateriaPrima(p);
        });

    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function cargarExtras() {

    $.ajax({
        url: '/menu/productos/cargarExtras',
        type: 'get',
        data: {
            _token: CSRF_TOKEN,
            id_prod_seleccionado: id_prod_seleccionado
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        $("#tbody-ext").html("");
        respuesta.datos.forEach(p => {
            crearExtras(p);
        });

    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function crearExtras(producto) {
    let texto = "<tr>";
    texto += "<td class='text-center'>" + producto.descripcion + "</td>";
    texto += "<td class='text-center'>" + producto.precio + "</td>";
    texto += "<td class='text-center'>" + producto.dsc_grupo + "</td>";
    texto += "<td class='text-center'>" + (producto.es_requerido == 0 ? "No" : "Sí") + "</td>";
    texto += "<td class='text-center'>" + (producto.multiple == 0 ? "No" : "Sí") + "</td>";
    texto += '<td class="text-center"><button  class="btn btn-icon btn-secondary" onclick="eliminarExtra(' + producto.id + ')"' +
        '><i class="fas fa-trash"></i></button></td>';
    texto += "</tr>";

    $("#tbody-ext").append(texto);

}

function crearMateriaPrima(producto) {
    let texto = "<tr>";
    texto += "<td class='text-center'>" + producto.nombre + "</td>";
    texto += "<td class='text-center'>" + producto.cantidad + "</td>";
    texto += "<td  class='text-center''>" +   producto.unidad_medida +"</td>";
    texto += '<td class="text-center"><button  class="btn btn-icon btn-secondary" onclick="eliminarProdMp(' + producto.id_mp_x_prod + ')"' +
        '><i class="fas fa-trash"></i></button></td>';
    texto += "</tr>";

    $("#tbody-inv").append(texto);

}

function cerrarMateriaPrima(){
  $("#mdl-materia-prima").modal("hide");
}

function cerrarExtras(){
    $("#mdl-extras").modal("hide");
  }

function limpiarMateriaPrimaProducto() {
    $('#select_prod_mp').val(1);
    $('#ipt_cantidad_req').val(0);
    $('#ipt_id_prod_mp').val(-1);
}

function agregarMateriaPrimaProducto() {
    let id_prod = $('#select_prod_mp').val();
    let cant = $('#ipt_cantidad_req').val();
    let id_mp_prod = $('#ipt_id_prod_mp').val();
    $.ajax({
        url: '/menu/productos/guardarMpProd',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id_prod_seleccionado: id_prod_seleccionado,
            id_mp_prod: id_mp_prod,
            id_prod: id_prod,
            cant: cant
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            console.log(respuesta.datos);
            showError(respuesta.mensaje);
            return;
        }


        showSuccess("Se agregó correctamente");
        cargarMateriaPrima();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function agregarExtraProducto() {
    let ipt_dsc_ext = $('#ipt_dsc_ext').val();
    let ipt_precio_ext = $('#ipt_precio_ext').val();
    let ipt_id_prod_ext = $('#ipt_id_prod_ext').val();
    let ipt_dsc_gru_ext = $('#ipt_dsc_gru_ext').val();
    let esRequerido = $("#requisito").is(':checked');
    let multiple = $("#multiple").is(':checked');
    $.ajax({
        url: '/menu/productos/guardarExtProd',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id: ipt_id_prod_ext,
            precio: ipt_precio_ext,
            dsc: ipt_dsc_ext,
            dsc_grupo: ipt_dsc_gru_ext,
            producto : id_prod_seleccionado,
            es_Requerido : esRequerido,
            multiple : multiple
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        showSuccess("Se agregó correctamente");
        cargarExtras();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}



function eliminarProdMp(id_prod_mp) {
    $.ajax({
        url: '/menu/productos/eliminarMpProd',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id_prod_mp: id_prod_mp
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        showSuccess("Se elimino correctamente");
        cargarMateriaPrima();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}

function eliminarExtra(id_prod_mp) {
    $.ajax({
        url: '/menu/productos/eliminarExtra',
        type: 'post',
        data: {
            _token: CSRF_TOKEN,
            id_prod: id_prod_mp
        }
    }).done(function (respuesta) {

        if (!respuesta.estado) {
            showError(respuesta.mensaje);
            return;
        }

        showSuccess("Se elimino correctamente");
        cargarExtras();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Ocurrió un error consultando el servidor");
    });
}
