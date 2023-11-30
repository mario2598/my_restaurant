<?php $__env->startSection('style'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/bundles/datatables/datatables.min.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css')); ?>">
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Consumo Inventario Materia Prima</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" onkeyup="filtrarGastosAdmin(this.value)"
                                    id="btn_buscar_gasto" class="form-control" placeholder="Filtro rápido">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"
                                        onclick="filtrarGastosAdmin(btn_buscar_gasto.value)"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo e(URL::to('informes/conMateriaPrima/filtro')); ?>" method="POST">
                            <?php echo e(csrf_field()); ?>

                            <div class="row" style="width: 100%">
                             
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Sucursal</label>
                                        <select class="form-control" id="select_sucursal" name="sucursal">
                                            <option value="T" selected>Todos</option>
                                            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($i->id ?? ''); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                                    <?php if($i->id == $data['filtros']['sucursal']): ?> selected <?php endif; ?>>
                                                    <?php echo e($i->descripcion ?? ''); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Desde</label>
                                        <input type="date" class="form-control" name="desde"
                                        required
                                            value="<?php echo e($data['filtros']['desde'] ?? ''); ?>" />

                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Hasta</label>
                                        <input type="date" class="form-control" name="hasta"
                                            value="<?php echo e($data['filtros']['hasta'] ?? ''); ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label>Descripción producto</label>
                                        <input type="text" name="descProd" onkeyup="filtrarGastosAdmin(this.value)"
                                            value="<?php echo e($data['filtros']['descProd'] ?? ''); ?>" class="form-control"
                                            placeholder="Descripción del producto">

                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-2">
                                    <div class="form-group">
                                        <label>Buscar</label>
                                        <button type="submit" class="btn btn-primary btn-icon form-control"
                                            style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>

                            </div>
                        </form>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table " id="tablaIngresos">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Sucursal</th>
                                            <th class="text-center">Producto MP</th>
                                            <th class="text-center">Consumo</th>
                                            <th class="text-center">Unidad Medida</th>
                                            <th class="text-center">Precio Unidad</th>
                                            <th class="text-center">Costo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">
                                        <?php $__currentLoopData = $data['datosReporte']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="space_row_table" style="cursor: pointer;">
                                                <td class="text-center"><?php echo e($g->nombreSucursal ?? ''); ?></td>
                                                <td class="text-center">
                                                    <?php echo e($g->nombreProducto ?? ''); ?>

                                                </td>
                                                <td class="text-center">
                                                    <?php echo e($g->suma ?? 0); ?> 
                                                </td>
                                                <td class="text-center">
                                                    <?php echo e($g->unidad_medida ?? ''); ?>

                                                </td>
                                                <td class="text-center">
                                                    CRC <?php echo e(number_format($g->precio_unidad  ?? '0.00',2,".",",")); ?>

                                                </td>
                                                <td class="text-center">
                                                    CRC <?php echo e(number_format($g->costo  ?? '0.00',2,".",",")); ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>

    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {

            var sucursal = $("#select_sucursal option[value='" + "<?php echo e($data['filtros']['sucursal']); ?>" + "']").html();

            var topMesage = 'Reporte de Consumo de Materia Prima \n';
            var bottomMesage = 'Reporte de Consumo de Materia Prima filtrado por : \n';

            if ("<?php echo e($data['filtros']['desde']); ?>" != '') {
                topMesage += ' Desde el ' + "<?php echo e($data['filtros']['desde']); ?>";
            }
           
            if ("<?php echo e($data['filtros']['hasta']); ?>" != '') {
                topMesage += ' Hasta el ' + "<?php echo e($data['filtros']['hasta']); ?>";
            }
           
            topMesage += '.' + '\nSolicitud realizada por ' + "<?php echo e(session('usuario')['usuario']); ?>" + '.';

            if ("<?php echo e($data['filtros']['sucursal']); ?>" != 'T') {
                bottomMesage += ' Sucursal [ ' + sucursal + ' ],';
            } else {
                bottomMesage += ' Sucursal [ Todas ],';
            }

            if ("<?php echo e($data['filtros']['descProd']); ?>" != '') {
                bottomMesage += ' Descripción Producto [ ' + "<?php echo e($data['filtros']['descProd']); ?>" + ' ].';
            } else {
                bottomMesage += '.';
            }
            bottomMesage += '\n\n Desarrollado por Space Software CR. ';


            $('#tablaIngresos').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                buttons: [{
                    extend: 'excel',
                    title: 'COFFEE TO GO',
                    messageTop: topMesage,
                    footer: true,
                    messageBottom: bottomMesage,
                    filename: 'consumo_mp_COFFETOGO'
                }, {
                    extend: 'pdf',
                    title: 'COFFEE TO GO',
                    footer: true,
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'consumo_mp_COFFETOGO'
                }, {
                    extend: 'print',
                    title: 'COFFEE TO GO',
                    footer: true,
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'consumo_mp_COFFETOGO'
                }]
            });

        }
    </script>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/gastos_admin.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/informes/conMateriaPrima.blade.php ENDPATH**/ ?>