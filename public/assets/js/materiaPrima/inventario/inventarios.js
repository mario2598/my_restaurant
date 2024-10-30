window.addEventListener("load", initialice, false);
var productosAgregar = [];
var pe_idGestion;
var prod_mp_id;
function initialice() {
    $("#btn_buscar_pro").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_genericoMp tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    cargarMateriPrimaInvSucursal();
}


function cargarProdMpNotSucursal() {

    $.ajax({
        url: `${base_path}/materiaPrima/inventario/inventarios/cargarMateriPrimaNotSucursal`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idSucursal: $('#sucursal').val()
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        productosAgregar = response['datos'];
        generarHtmlMatPrim();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError('Algo salio mal..');
    });
}

function buscarInv() {
    cargarMateriPrimaInvSucursal();
    cargarProdMpNotSucursal();
}
function cargarMateriPrimaInvSucursal() {

    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/materiaPrima/inventario/inventarios/cargarMateriPrimaInvSucursal`,
        type: 'get',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            idSucursal: $('#sucursal').val()
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            $('#loader').fadeOut();
            return;
        }
        generarHTMLMp(response['datos']);
        $('#loader').fadeOut();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        $('#loader').fadeOut();
    });
    $('#loader').fadeOut();
}

function generarHTMLMp(inventario) {
    
    $('#tbody_genericoMp').html('');
    let htmlContent = '';

    inventario.forEach(item => {
        htmlContent += `
            <tr style="cursor: pointer" onclick='editarProductoInventario("${item.ms_id}", "${item.id}", "${item.cantidad}", "${item.nombre}")'>
                <td class="text-center">${item.nombre ?? ''}</td>
                <td class="text-center">${item.cantidad ?? ''}</td>
                <td class="text-center">${item.unidad_medida ?? ''}</td>
                <td class="text-center">${item.nombre_prov ?? ''}</td>
            </tr>
        `;
    });

    $('#tbody_genericoMp').html(htmlContent);
    initTabla();
}

function abrirAgregarProducto() {
    cargarProdMpNotSucursal();
    pe_idGestion = null;
    $("#contInfoProd0").hide();
    $("#contInfoProd1").show();
    $("#contInfoProd2").show();
    $("#cantidad_agregar").removeAttr("readonly");
    $("#btn_add_inventario").show();
    $("#btn_ajustar_inventario").hide();
    $('#producto_externo').val('-1');
    $('#cantidad_agregar').val(0);
    $('#mdl_agregar_producto').modal('show');
}

function generarHtmlMatPrim() {
    var selectProd = document.getElementById('producto_externo');

    // Limpiar el contenido actual del select
    selectProd.innerHTML = '';
    // Iterar sobre el array de comandas y crear opciones
    productosAgregar.forEach(function (p) {
        var option = document.createElement('option');
        option.value = p.id || '';
        option.text = p.nombre || '';
        option.title = p.nombre || '';
        selectProd.appendChild(option);
    });
}

function abrirProductosExternos() {
    $('#mdl_ayuda_producto').modal('show');
}

function seleccionarProductoAyuda($producto) {
    $('#producto_externo').val($producto);
    $('#mdl_ayuda_producto').modal('hide');
}

function editarProductoInventario(id_pe, id_prod, cantidad, nombre) {

    pe_idGestion = id_pe;
    prod_mp_id = id_prod;
    $('#txtNombreProducto').html(nombre);
    $("#contInfoProd0").show();
    $("#contInfoProd1").hide();
    $("#contInfoProd2").hide();
    $("#cantidad_agregar").attr("readonly", true);
    $("#btn_add_inventario").hide();
    $("#btn_ajustar_inventario").show();
    $('#producto_externo').val(id_prod);
    $('#cantidad_agregar').val(cantidad);
    $('#mdl_agregar_producto').modal('show');
}

function crearProductoSucursal() {
    var idSuc = $('#sucursal').val();
    var prodExt = $('#producto_externo').val();
    var cant = $('#cantidad_agregar').val();

    $('#loader').fadeIn();
    $.ajax({
        url: `${base_path}/materiaPrima/inventario/inventarios/crearProducto`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            sucursal_agregar_id: idSuc,
            producto_externo: prodExt,
            cantidad_agregar: cant
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            $('#loader').fadeOut();
            return;
        }
        showSuccess("Se agregó el producto al inventario");
        $('#loader').fadeOut();
        $('#mdl_agregar_producto').modal('hide');
        buscarInv();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");

        $('#loader').fadeOut();
    });
}


function mdlAjustarInventario(){
    $('#cantidad_ajustar').val(1);
    $('#mdl_ajustar_cant_producto').modal('show');
}

function cerrarMdlAjustarInventario(){
    $('#cantidad_ajustar').val(1);
    $('#mdl_ajustar_cant_producto').modal('hide');
}

function aumentarInventario() {
    var id = pe_idGestion;
    var cant = $('#cantidad_ajustar').val();
    var idSuc = $('#sucursal').val( );
    var prodExt = prod_mp_id;
    $.ajax({
        url: `${base_path}/materiaPrima/inventario/inventarios/aumentar`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            pe_id: id,
            sucursal_agregar_id:idSuc,
            prodExt:prodExt,
            cantidad_agregar:cant
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se aumento el producto al inventario");
        $('#loader').fadeOut();
        $('#mdl_ajustar_cant_producto').modal('hide');
        $('#mdl_agregar_producto').modal('hide');
        buscarInv();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function disminuirInventario() {
    var id = pe_idGestion;
    var cant = $('#cantidad_ajustar').val();
    var idSuc = $('#sucursal').val( );
    var prodExt = prod_mp_id;
    $.ajax({
        url: `${base_path}/materiaPrima/inventario/inventarios/disminuir`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            pe_id: id,
            sucursal_agregar_id:idSuc,
            prodExt:prodExt,
            cantidad_agregar:cant
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        showSuccess("Se disminuyo el producto al inventario");
        $('#loader').fadeOut();
        $('#mdl_ajustar_cant_producto').modal('hide');
        $('#mdl_agregar_producto').modal('hide');
        buscarInv();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}