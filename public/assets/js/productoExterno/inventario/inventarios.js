window.addEventListener("load", initialice, false);
function initialice() {
    $("#btn_buscar_producto_ayuda").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_productos tr").filter(function () {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
}


function abrirAgregarProducto(){
    $('#pe_id').val('-1');
    $('#sucursal_agregar_id').val( $('#sucursal').val());
    $('#producto_externo').val('-1');
    $('#cantidad_agregar').val(0);
    $('#mdl_agregar_producto').modal('show');
}

function abrirProductosExternos(){
    $('#mdl_ayuda_producto').modal('show');
}

function seleccionarProductoAyuda($producto){
    $('#producto_externo').val($producto);
    $('#mdl_ayuda_producto').modal('hide');
}

function editarProductoInventario(id_pe,id_prod,cantidad){
    $('#pe_id').val(id_pe);
    $('#sucursal_agregar_id').val( $('#sucursal').val());
    $('#producto_externo').val(id_prod);
    $('#cantidad_agregar').val(cantidad);
    $('#mdl_agregar_producto').modal('show');
}


function guardarProductoSucursal() {
    var id = $('#pe_id').val();
    var idSuc = $('#sucursal_agregar_id').val( );
    var prodExt = $('#producto_externo').val();
    var cant = $('#cantidad_agregar').val();
    
    $.ajax({
        url: `${base_path}/productoExterno/inventario/inventarios/guardar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            pe_id: id,
            sucursal_agregar_id:idSuc,
            producto_externo:prodExt,
            cantidad_agregar:cant
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }

        location.reload();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}
