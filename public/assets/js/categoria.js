window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    $("#input_buscar_generico").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tbody_generico tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Manejar clic en botón editar usando data attributes
    $(document).on('click', '.editar-categoria', function() {
        var id = $(this).data('id');
        var categoria = $(this).data('categoria');
        var codigo = $(this).data('codigo');
        var url = $(this).data('url');
        var posicion = $(this).data('posicion');
        
        // Limpiar URL si tiene duplicaciones (por si acaso)
        if (url && typeof url === 'string') {
            // Si la URL contiene el patrón duplicado, limpiarla
            var baseUrlPattern = base_path + '/storage/' + base_path;
            if (url.includes(baseUrlPattern)) {
                // Extraer la parte correcta de la URL
                var match = url.match(/\/storage\/categorias\/[^\/]+$/);
                if (match) {
                    url = base_path + match[0];
                } else {
                    // Si no se puede extraer, usar imagen por defecto
                    url = base_path + '/assets/images/default-logo.png';
                }
            }
        }
        
        editarGenerico(id, categoria, codigo, url, posicion);
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

    // Preview de imagen cuando se selecciona un archivo nuevo
    $('#foto_producto').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#img_cat').attr('src', e.target.result);
                $('#img_cat').css({
                    'border': '2px solid #28a745',
                    'box-shadow': '0 0 5px rgba(40, 167, 69, 0.5)'
                });
            };
            reader.readAsDataURL(file);
        }
    });

    // Manejar error de carga de imagen
    $('#img_cat').on('error', function() {
        var defaultImage = base_path + '/assets/images/default-logo.png';
        $(this).attr('src', defaultImage);
        $(this).off('error'); // Prevenir loop infinito
    });

}


/** modales  */
/**
 * Abre el modal y carga los datos correspondientes

 */
function editarGenerico(id, categoria, codigo, url, posicion_menu) {
    $('#mdl_generico_ipt_id').val(id);
    $('#mdl_generico_ipt_categoria').val(categoria);
    $('#mdl_generico_ipt_codigo').val(codigo);
    $('#posicion_menu').val(posicion_menu);
    
    // Validar y establecer la imagen
    var imagen = document.getElementById("img_cat");
    var defaultImage = base_path + '/assets/images/default-logo.png';
    
    // Validar que la URL no esté vacía, no sea undefined, y no contenga duplicaciones
    if (url && url.trim() !== '' && url !== 'undefined' && 
        url !== base_path + '/storage' && 
        !url.includes(base_path + '/storage/' + base_path)) {
        // Si la URL ya es completa (empieza con http), usarla directamente
        // Si no, podría ser relativa y necesitar procesamiento
        if (url.startsWith('http://') || url.startsWith('https://')) {
            imagen.src = url;
        } else if (url.startsWith('/')) {
            imagen.src = base_path + url;
        } else {
            imagen.src = url;
        }
    } else {
        imagen.src = defaultImage;
    }
    
    // Resetear el input de archivo
    $('#foto_producto').val('');
    
    // Remover estilos de preview si existen
    $('#img_cat').css({
        'border': '1px solid #ddd',
        'box-shadow': 'none'
    });
    
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
    $('#posicion_menu').val(0);
    
    // Establecer imagen por defecto
    var imagen = document.getElementById("img_cat");
    var defaultImage = base_path + '/assets/images/default-logo.png';
    imagen.src = defaultImage;
    
    // Resetear el input de archivo
    $('#foto_producto').val('');
    
    // Remover estilos de preview si existen
    $('#img_cat').css({
        'border': '1px solid #ddd',
        'box-shadow': 'none'
    });
    
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
