window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var tipos = [];
var promociones = [];
var nuevaPromo = false;

var promocionSeleccionada = {
    "id": 0,
    "descripcion": "",
    "precio": "",
    "categoria": "",
    "activo": "0"
};

var detalle = {
    "id": 0,
    "producto": "",
    "descripcion": "",
    "tipo": ""
};
$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody-promos tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $("#btn_buscar_producto_mnu_ayuda").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_productos_mnu tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $("#btn_buscar_producto_ext_ayuda").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_productos_ext tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    filtrar();
});


function limpiarPromocion() {
    promocionSeleccionada = {
        "id": 0,
        "descripcion": "",
        "precio": "",
        "estado": "",
        "categoria": "",
        detalles: []
    };
}



function filtrar() {
    $.ajax({
        url: `${base_path}/mant/grupoPromocion/filtro`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        promociones = response['datos'];
        generarHTMLPromos();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function generarHTMLPromos() {
    var texto = "";
    promociones.forEach(p => {
        texto = texto +
            `<tr style="border-bottom: 1px solid grey;" onclick="editarPromocion('${p.id}')">
                <td class="text-center" >
                    ${p.id}
                </td> 
                <td class="text-center">
                    ${p.descripcion}
            </td>
                <td class="text-center">
                    ${currencyCRFormat(p.precio)}
                </td>
                <td class="text-center">
                ${p.estado == 1 ? "Activa" : "Inactiva"}
            </td>
               </tr>`;
    });

    $('#tbody-promos').html(texto);
}

function editarPromocion(id) {
    nuevaPromo = false;
    const found = promociones.find(s => s.id == id);
    if (found != null && found != undefined) {
        promocionSeleccionada = found;
        cargarPromoModal();
        $('#mdlEditarPromo').modal("show");
    } else {
        showError("No se encontro la promoción");
    }
}

function cerrarMdlEditarPromo() {
    $('#mdlEditarPromo').modal("hide");
}

function cargarPromoModal() {
    $('#descripcion').val(promocionSeleccionada.descripcion);
    $('#precio').val(promocionSeleccionada.precio);
    $('#select_categoria').val(promocionSeleccionada.categoria);
    var activo = false;
    if (promocionSeleccionada.estado == "1") {
        activo = true;
    }
    $("#activo").prop("checked", activo);
    generarHTMLdetallePromos();
}

function cargarModalPromo() {
    promocionSeleccionada.descripcion = $('#descripcion').val();
    promocionSeleccionada.precio = $('#precio').val();
    promocionSeleccionada.categoria = $('#select_categoria').val();
    var checkbox = document.getElementById("activo");
    promocionSeleccionada.estado = checkbox.checked ? "1" : "0";
}


function generarHTMLdetallePromos() {
    var texto = "";
    promocionSeleccionada.detalles.forEach(d => {
        texto = texto +
            `<tr >
                <td class="text-center" >
                    ${d.prod.nombre}
                </td> 
                <td class="text-center">
                    ${d.cantidad}
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger px-2" onclick='eliminarDetallePromocion("${d.id}")' 
                    title="Eliminar producto">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                </button>
            </td>
               </tr>`;
    });

    $('#tbody-detalles').html(texto);
}

function abrirModalAddProdMenu() {
    detalle.tipo = "R"
    $('#mdl_addDetalle').modal("show");
}

function cerrarModalAddProdMenu() {
    $('#mdl_addDetalle').modal("hide");
}

function abrirModalAddProdExt() {
    detalle.tipo = "E"
    $('#mdl_addDetalleExterno').modal("show");
}

function cerrarModalAddProdExt() {
    $('#mdl_addDetalleExterno').modal("hide");
}

function abrirProductosExternosAyuda() {
    $('#mdl_ayuda_producto').modal('show');
}


function abrirProductosMenuAyuda() {
    $('#mdl_ayuda_producto_mnu').modal('show');
}


function seleccionarProductoAyuda($producto) {
    $('#producto_externo').val($producto);
    $('#mdl_ayuda_producto').modal('hide');
}

function seleccionarProductoAyudaMnu($producto) {
    $('#prodcuto_menu').val($producto);
    $('#mdl_ayuda_producto_mnu').modal('hide');
}

function guardarPromocion() {
    cargarModalPromo();
    var formData = new FormData();
    var file = $('#foto_producto')[0].files[0];
    formData.append('foto_producto', file);
    formData.append('_token', CSRF_TOKEN);
    formData.append('promocion', JSON.stringify(promocionSeleccionada));
    $.ajax({
        url: `${base_path}/mant/grupoPromocion/guardarPromocion`,
        type: 'post',
        dataType: "json",
        data: formData,
        contentType: false, // No establecer el tipo de contenido
        processData: false

    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        promocionSeleccionada = response['datos'];
        if (nuevaPromo) {
            filtrar();
        } else {
            generarHTMLPromos();
        }
        cerrarMdlEditarPromo();

        showSuccess("Se guardo la promoción")
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}

function eliminarDetallePromocion(id) {
    $.ajax({
        url: `${base_path}/mant/grupoPromocion/eliminarDetallePromocion`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            detallePromo: id
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        promocionSeleccionada = response['datos'];
        generarHTMLPromos();
        cargarPromoModal();
        cerrarModalAddProdMenu();
        showSuccess("Se guardo el detalle de promoción");
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}


function guardarDetallePromo() {
    cargarModalDetalle();

    $.ajax({
        url: `${base_path}/mant/grupoPromocion/guardarDetallePromocion`,
        type: 'post',
        dataType: "json",
        data: {
            _token: CSRF_TOKEN,
            promocion: promocionSeleccionada,
            detallePromo: detalle
        }
    }).done(function (response) {
        if (!response['estado']) {
            showError(response['mensaje']);
            return;
        }
        promocionSeleccionada = response['datos'];
        generarHTMLPromos();
        cargarPromoModal();
        cerrarModalAddProdMenu();
        cerrarModalAddProdExt();
        showSuccess("Se guardo el detalle de promoción")
    }).fail(function (jqXHR, textStatus, errorThrown) {
        showError("Algo salió mal");
    });
}


function cargarModalDetalle() {
    if (detalle.tipo == "R") {
        detalle.producto = $('#prodcuto_menu').val();
        detalle.cantidad = $('#cantidad_mnu').val();
    } else {
        detalle.producto = $('#producto_externo').val();
        detalle.cantidad = $('#cantidad_agregar').val();
    }
}

function mdlNuevaPromocion() {
    nuevaPromo = true;
    limpiarPromocion();
    cargarPromoModal();
    $('#mdlEditarPromo').modal("show");
}
