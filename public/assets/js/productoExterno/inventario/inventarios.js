window.addEventListener("load", initialice, false);
function initialice() {
    $("#btn_buscar_producto_ayuda").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_productos tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    cargarComandas();
}



function abrirAgregarProducto() {
    $('#pe_id').val('-1');
    cargarProductosExternosSucursal();
    $('#producto_externo').val('-1');
    $('#cantidad_agregar').val(0);
    $('#cantidad_agregar').prop('disabled', false);
    $('#contBusdcarPe').fadeIn();
    $('#btn_ajustar_inventario').fadeOut();
    $('#producto_externo').prop('disabled', false);
    $('#comanda_select').val(-1);
    $('#mdl_agregar_producto').modal('show');

}


function cambiarSucursal(form) {
    form.submit();
}

function cargarComandas() {
    if ($('#sucursal').val() != '-1') {
        $('#loader').fadeIn();
        comandasGeneral = {};
        $.ajax({
            url: `${base_path}/productoExterno/inventario/inventarios/cargarComandas`,
            type: 'get',
            dataType: "json",
            data: {
                _token: CSRF_TOKEN,
                idSucursal: $('#sucursal').val()
            }
        }).done(function (response) {
            if (!response['estado']) {
                return;
            }

            generarHTMLComandas(response['datos']);
            $('#loader').fadeOut();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#loader').fadeOut();
        });
        $('#loader').fadeOut();

    }
}


function generarHTMLComandas(comandas) {
    // Obtener el elemento select
    var selectComandas = document.getElementById('comanda_select');

    // Limpiar el contenido actual del select
    selectComandas.innerHTML = '';

    // Añadir la opción de "Comanda General"
    var optionGeneral = document.createElement('option');
    optionGeneral.value = '-1';
    optionGeneral.text = 'Comanda General';
    optionGeneral.selected = true;
    selectComandas.appendChild(optionGeneral);

    // Iterar sobre el array de comandas y crear opciones
    comandas.forEach(function (comanda) {
        var option = document.createElement('option');
        option.value = comanda.id || '';
        option.text = comanda.nombre || '';
        option.title = comanda.nombre || '';
        selectComandas.appendChild(option);
    });
}

function cargarProductosExternosSucursal() {
    $.ajax({
        url: `${base_path}/productoExterno/inventario/inventarios/cargarPeSucursal`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idSucursal: $('#sucursal').val()
        }
    }).done(function (response) {
        if (!response['estado']) {
            return;
        }

        generarHTMLProductos(response['datos']);
        $('#loader').fadeOut();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        $('#loader').fadeOut();
    });
    $('#loader').fadeOut();
}

function generarHTMLProductos(productos) {
    // Obtener el elemento select
    var selectPE = document.getElementById('producto_externo');

    // Limpiar el contenido actual del select
    selectPE.innerHTML = '';


    // Iterar sobre el array de comandas y crear opciones
    productos.forEach(function (producto) {
        var option = document.createElement('option');
        option.value = producto.id || '';
        option.text = producto.nombre || '';
        option.title = producto.codigo_barra || '';
        selectPE.appendChild(option);
    });
}



function abrirProductosExternos() {
    $('#mdl_ayuda_producto').modal('show');
}

function seleccionarProductoAyuda($producto) {
    $('#producto_externo').val($producto);
    $('#mdl_ayuda_producto').modal('hide');
}

function editarProductoInventario(id_pe, id_prod, cantidad, comanda, nombreProducto) {
    $('#pe_id').val(id_pe);

    $('#producto_externo').val(id_prod);
    $('#cantidad_agregar').val(cantidad);
    $('#cantidad_agregar').prop('disabled', true);
    $('#contBusdcarPe').fadeOut();
    $('#btn_ajustar_inventario').fadeIn();
    $('#producto_externo').prop('disabled', true);
    $('#comanda_select').val(comanda == '' ? -1 : comanda);
    var selectPE = document.getElementById('producto_externo');
    selectPE.innerHTML = '';
    var option = document.createElement('option');
    option.value = id_prod || '';
    option.text = nombreProducto || '';
    option.title = nombreProducto || '';
    selectPE.appendChild(option);

    $('#mdl_agregar_producto').modal('show');
}


function guardarProductoSucursal() {
    var id = $('#pe_id').val();
    var idSuc = $('#sucursal').val();
    var prodExt = $('#producto_externo').val();
    var cant = $('#cantidad_agregar').val();
    var desecho = $('#es_desecho').prop('checked');
    $.ajax({
        url: `${base_path}/productoExterno/inventario/inventarios/guardar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            pe_id: id,
            sucursal_agregar_id: idSuc,
            producto_externo: prodExt,
            cantidad_agregar: cant, comanda_select: $('#comanda_select').val()
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        $('#form_cargar_menu').submit();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function mdlAjustarInventario() {
    $('#cantidad_ajutar').val(1);
    $('#mdl_ajustar_cant_producto').modal('show');
}

function cerrarMdlAjustarInventario() {
    $('#cantidad_ajutar').val(1);
    $('#mdl_ajustar_cant_producto').modal('hide');
}

function aumentarInventario() {
    var id = $('#pe_id').val();
    var cant = $('#cantidad_ajustar').val();
    var idSuc = $('#sucursal').val();
    var prodExt = $('#producto_externo').val();
    $.ajax({
        url: `${base_path}/productoExterno/inventario/inventarios/aumentar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            pe_id: id,
            sucursal_agregar_id: idSuc,
            producto_externo: prodExt,
            cantidad_agregar: cant
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        cerrarMdlAjustarInventario();
        $('#mdl_agregar_producto').modal('hide');
        $('#form_cargar_menu').submit();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function disminuirInventario(desecho) {
    var id = $('#pe_id').val();
    var cant = $('#cantidad_ajutar').val();
    var idSuc = $('#sucursal').val();
    var prodExt = $('#producto_externo').val();
    $.ajax({
        url: `${base_path}/productoExterno/inventario/inventarios/disminuir`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            pe_id: id,
            sucursal_agregar_id: idSuc,
            producto_externo: prodExt,
            cantidad_agregar: cant,
            es_desecho: desecho
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        cerrarMdlAjustarInventario();
        $('#mdl_agregar_producto').modal('hide');
        $('#form_cargar_menu').submit();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function desecharInventario() {
    disminuirInventario("S");
}