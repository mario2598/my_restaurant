window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    $("#buscar_pedido").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_generico tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});


function initialice() {

}


/** modales  */
/**
 * Abre el modal y carga los datos correspondientes
 * @param {id} id 
 * @param {nombre proveedor} id 
 * @param {descripcion  del proveedor} desc 
 */
function editarGenerico(id, banco, porcentaje) {
    $('#mdl_generico_ipt_nombre').val(banco);
    $('#mdl_generico_ipt_porcentaje').val(porcentaje);
    $('#mdl_generico_ipt_id').val(id);
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
    $('#mdl_generico_ipt_nombre').val("");
    $('#mdl_generico_ipt_porcentaje').val("0");
    $('#mdl_generico_ipt_id').val('-1');
    $('#mdl_generico').modal('show');
}

function eliminarSucursalInventarioPedido(id) {
    swal({
            title: 'Seguro de eliminar el pedido?',
            text: 'No podra deshacer esta acción!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                swal.close();
                $('#idPedido').val(id);
                $('#formEliminarPedido').submit();

            } else {
                swal.close();
            }
        });


}

function verSucursalInventarioPedido(id) {
  if(id != ""){
    $('#idPedidoEditar').val(id);
    $('#formGoEditarPedido').submit();
  }
}
