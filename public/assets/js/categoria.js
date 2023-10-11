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
    var t = document.getElementById('mdl_generico_ipt_categoria');
    t.addEventListener('input', function () { // 
        if (this.value.length > 30)
            this.value = this.value.slice(0, 30);
    });

    // Restringir tamaño  de los inputs
    var t = document.getElementById('mdl_generico_ipt_codigo');
    t.addEventListener('input', function () { // 
        if (this.value.length > 9)
            this.value = this.value.slice(0, 9);
    });

}


/** modales  */
/**
 * Abre el modal y carga los datos correspondientes

 */
function editarGenerico(id, categoria, codigo, url) {

    $('#mdl_generico_ipt_id').val(id);
    $('#mdl_generico_ipt_categoria').val(categoria);
    $('#mdl_generico_ipt_codigo').val(codigo);
    var imagen = document.getElementById("img_cat");
    imagen.src = url;
    $('#mdl_generico').modal('show');
}

/**
 * Cierra el modal 
 */
function cerrarModalGenerico() {
    $('#mdl_generico').modal('hide');
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevoGenerico() {
    $('#mdl_generico_ipt_id').val('-1');
    $('#mdl_generico_ipt_categoria').val("");
    $('#mdl_generico_ipt_codigo').val("");
    $('#mdl_generico').modal('show');
}

function eliminarGenerico(id) {
    swal({
            title: 'Seguro de inactivar la categoría?',
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
