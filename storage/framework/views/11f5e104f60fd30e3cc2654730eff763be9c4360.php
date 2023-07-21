<?php $__env->startSection('style'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/bundles/datatables/datatables.min.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css')); ?>">
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script>
        var productos = [];
        var id_prod_seleccionado = -1;
    </script>

    <div class="main-content">
        <section class="section">
            <div class="section-body">

                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Productos Menú</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="btn_buscar_pro" class="form-control"
                                    placeholder="Buscar producto">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <form action="<?php echo e(URL::to('menu/productos/filtro')); ?>" method="POST">
                            <?php echo e(csrf_field()); ?>

                            <div class="row" style="width: 100%">
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Categoría</label>
                                        <select class="form-control" id="select_categoria" name="categoria">
                                            <option value="T" selected>Todos</option>
                                            <?php $__currentLoopData = $data['categorias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->categoria ?? ''); ?>"
                                                    <?php if($i->id == $data['filtros']['categoria']): ?> selected <?php endif; ?>>
                                                    <?php echo e($i->categoria ?? ''); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Tipo Impuesto</label>
                                        <select class="form-control" id="select_impuesto" name="impuesto">
                                            <option value="T" selected>Todos</option>
                                            <?php $__currentLoopData = $data['impuestos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($i->id); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                                    <?php if($i->id == $data['filtros']['impuesto']): ?> selected <?php endif; ?>>
                                                    <?php echo e($i->descripcion ?? ''); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-2">
                                    <div class="form-group">
                                        <label>Buscar</label>
                                        <button type="submit" class="btn btn-primary btn-icon form-control"
                                            style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-2">
                                    <div class="form-group">
                                        <label>Nuevo</label>
                                        <a href="<?php echo e(url('menu/producto/nuevo')); ?>"
                                            class="btn btn-success btn-icon form-control"
                                            style="cursor: pointer;color:white;"><i class="fas fa-plus"></i> Agregar</a>
                                    </div>

                                </div>

                            </div>
                        </form>
                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaProductos">
                                    <thead>


                                        <tr>
                                            <th class="text-center">Código</th>

                                            <th class="text-center">Producto</th>
                                            <th class="text-center">
                                                Categoría
                                            </th>
                                            <th class="text-center">
                                                Impuestos %
                                            </th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Materia Prima</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">

                                        <?php $__currentLoopData = $data['productos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="space_row_table" style="cursor: pointer;">

                                                <td class="text-center" onclick='clickProducto("<?php echo e($g->id); ?>")'>
                                                    <?php echo e($g->codigo ?? ''); ?></td>
                                                <td class="text-center" onclick='clickProducto("<?php echo e($g->id); ?>")'>
                                                    <?php echo e($g->nombre); ?>

                                                </td>
                                                <td class="text-center" onclick='clickProducto("<?php echo e($g->id); ?>")'>
                                                    <?php echo e($g->nombre_categoria ?? ''); ?>

                                                </td>
                                                <td class="text-center" onclick='clickProducto("<?php echo e($g->id); ?>")'>
                                                    <?php echo e($g->porcentaje_impuesto ?? '0'); ?> %
                                                </td>

                                                <td class="text-center" onclick='clickProducto("<?php echo e($g->id); ?>")'>
                                                    CRC <?php echo e(number_format($g->precio ?? '0.00', 2, '.', ',')); ?>

                                                </td>

                                                <td class="text-center">
                                                    <a class="btn btn-primary btn-icon" title="Composición del producto"
                                                        onclick='clickMateriaPrima("<?php echo e($g->id); ?>")'
                                                        style="cursor: pointer;"><i class="fas fa-list"></i></a>

                                                </td>
                                            </tr>
                                            <script>
                                                var inv = [];
                                            </script>
                                            <?php $__currentLoopData = $g->materia_prima; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <script>
                                                    inv.push({
                                                        "id": "<?php echo e($mp->id_mp_x_prod); ?>",
                                                        "nombre": "<?php echo e($mp->nombre); ?>",
                                                        "cantidad": "<?php echo e($mp->cantidad); ?>",
                                                        "unidad_medida": "<?php echo e($mp->unidad_medida); ?>"
                                                    });
                                                </script>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            <script>
                                                productos.push({
                                                    "id_producto": "<?php echo e($g->id); ?>",
                                                    "materia_prima": inv
                                                });
                                            </script>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>
        <form id="formEditarProducto" action="<?php echo e(URL::to('menu/producto/editar')); ?>" style="display: none" method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
        </form>
    </div>


    <div class="modal fade bs-example-modal-center" id='mdl-materia-prima' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <div class="row" style="width: 100%">
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <h5 class="modal-title">Composición Materia Prima</h5>

                        </div>
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <div class="form-group">
                                <label>Materia Prima</label>
                                <select class="form-control" id="select_prod_mp" style="width: 100%" name="select_prod_mp">
                                    <?php $__currentLoopData = $data['materia_prima']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->unidad_medida ?? ''); ?>">
                                            <?php echo e($i->nombre ?? ''); ?> - <?php echo e($i->unidad_medida ?? ''); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-xl-12">
                            <div class="form-group">
                                <label>Cantidad requerida</label>
                                <input type="number" class="form-control" id="ipt_cantidad_req" name="ipt_cantidad_req"
                                    value="" required step="0.01">
                                    <input  type="hidden" id="ipt_id_prod_mp" name="ipt_id_prod_mp"
                                    value="-1">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-xl-12">
                          <div class="form-group">
                            <a class="btn btn-primary" title="Guardar Composición" 
                            onclick="agregarMateriaPrimaProducto()"
                            style="color:white;cursor:pointer;" 
                            >Guardar Composición</a>
                            <a class="btn btn-secondary btn-icon" title="Cerrar"
                            onclick='cerrarMateriaPrima()'
                            style="cursor: pointer;">Cerrar</a>
                          </div>
                      </div>
                    </div>

                </div>
                <div class="modal-body">
                    <table class="table" id="tbl-inv" style="max-height: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col" style="text-align: center">Cantidad</th>
                                <th scope="col" style="text-align: center">Unidad Medida</th>
                                <th scope="col" style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-inv">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            var categoria = $("#select_categoria option[value='" + "<?php echo e($data['filtros']['categoria']); ?>" + "']").html();
            var impuesto = $("#select_impuesto option[value='" + "<?php echo e($data['filtros']['impuesto']); ?>" + "']").html();

            var topMesage = 'Reporte de Productos del Menú';
            var bottomMesage = 'Reporte de productos del Menú filtrado por';

            topMesage += '.' + ' Solicitud realizada por ' + "<?php echo e(session('usuario')['usuario']); ?>" + '.';

            if ("<?php echo e($data['filtros']['categoria']); ?>" != 'T') {
                bottomMesage += ' categoria [ ' + categoria + ' ],';
            } else {
                bottomMesage += ' categoria [ Todas ],';
            }

            if ("<?php echo e($data['filtros']['impuesto']); ?>" != 'T') {
                bottomMesage += ' tipo de impuesto [ ' + impuesto + ' ],';
            } else {
                bottomMesage += 'tipo de impuesto [ Todos ].';
            }

            bottomMesage += ' Coffee To Go CR. ';


            $('#tablaProductos').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                'fixedHeader': {
                    'header': true,
                    'footer': true
                },
                buttons: [{
                    extend: 'excel',
                    title: 'Coffee To Go',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_productos_coffee_to_coffee'
                }, {
                    extend: 'pdf',
                    title: 'Coffee To Go',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_productos_coffee_to_coffee'
                }, {
                    extend: 'print',
                    title: 'Coffee To Go',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'reporte_productos_coffee_to_coffee'
                }]
            });

        }
    </script>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/restaurante/productos.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/productosMenu/productos.blade.php ENDPATH**/ ?>