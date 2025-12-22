window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var productoGestion = null;
var idProductoGestion = 0;
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
        if (this.value.length > 50)
            this.value = this.value.slice(0, 50);
    });

    t = document.getElementById('codigo');
    t.addEventListener('input', function () { // 
        if (this.value.length > 15)
            this.value = this.value.slice(0, 15);
    });
    idProductoGestion = $("#idProducto").val();

    if (idProductoGestion == null || idProductoGestion == undefined) {
        idProductoGestion = 0;
    }

    if (idProductoGestion > 0) {
        cargarProducto();
    }

    // Preview de imagen cuando se selecciona un archivo nuevo
    $('#foto_producto').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imgProd').attr('src', e.target.result);
                $('#imgProd').css({
                    'border': '2px solid #28a745',
                    'box-shadow': '0 0 5px rgba(40, 167, 69, 0.5)'
                });
            };
            reader.readAsDataURL(file);
        }
    });

}


function cargarProducto() {
    var idAux = $('#idProducto').val();
    $.ajax({
        url: `${base_path}/productoMenu/producto/cargarProducto`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idProducto: idProductoGestion
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(res['mensaje']);
            return;
        }
        productoGestion = response['datos'];
        cargarProductoPantalla();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function guardarProducto() {
    var formData = new FormData(document.getElementById('formularioGen'));
    $.ajax({
        type: "POST",
        url: `${base_path}/menu/producto/guardar`,
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json"
    }).done(function (response) {
      
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
       
        showSuccess("Se guardo el producto");
        productoGestion = response['datos'];
        $("#idProducto").val(productoGestion);
        cargarProducto();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function cargarProductoPantalla() {
    $("#codigo").val(productoGestion.codigo);
    $("#nombre").val(productoGestion.nombre);
    $("#descripcion").html(productoGestion.descripcion);
    $("#categoria").val(productoGestion.categoria);
    $("#precio").val(productoGestion.precio);
    $("#impuesto").val(productoGestion.impuesto);
    $("#receta").val(productoGestion.receta);
    $("#posicion_menu").val(productoGestion.posicion_menu);
    var imagen = document.getElementById("imgProd");
    imagen.src = productoGestion.url_imagen;
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
