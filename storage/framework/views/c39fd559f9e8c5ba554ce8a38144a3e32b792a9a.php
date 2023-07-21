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
                        <h4>Menú</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="btn_buscar_pro" class="form-control"
                                    placeholder="Buscar restaurante">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <form action="<?php echo e(URL::to('restaurante/menus/filtro')); ?>" method="POST">
                            <?php echo e(csrf_field()); ?>

                            <div class="row" style="width: 100%">
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Sucursal</label>
                                        <select class="form-control" id="sucursal" name="sucursal" required>
                                            <option value="-1" selected>Seleccione una sucursal</option>
                                            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($i->id ?? ''); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                                    <?php if($i->id == $data['filtros']['sucursal']): ?>
                                                    selected
                                            <?php endif; ?>
                                            ><?php echo e($i->descripcion ?? ''); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Buscar</label>
                                        <button type="submit" class="btn btn-primary btn-icon form-control"
                                            style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>


                            </div>
                        </form>
                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaInventarios">
                                    <thead>


                                        <tr>
                                            <th class="text-center">Código</th>
                                           


                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">
                                        <?php $__currentLoopData = $data['restaurantes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr style="cursor: pointer" onclick='goEditarMenu(<?php echo e("$i->id"); ?>)'>
                                                <td class="text-center">
                                                    REST-<?php echo e(strtoupper($i->id ?? '')); ?>

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
        <form id="formRestauranteMenu" action="<?php echo e(URL::to('restaurante/menus/editar')); ?>" style="display: none" method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idRestauranteMenu" id="idRestauranteMenu" value="-1">
        </form>
    </div>

   
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/restaurante/menus.js')); ?>"></script>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/restaurante/menus.blade.php ENDPATH**/ ?>