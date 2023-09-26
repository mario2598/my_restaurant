<?php $__env->startSection('style'); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <section class="section">
            <div class="row" id="contenedor_comandas">
                <?php $__currentLoopData = $data['ordenes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(count($p->detalles) > 0): ?>
                    <div class="col-md-6 col-xs-12 col-sm-12 col-xl-4">
                        <div class="card">
                            <div class="card-header" style="padding: 5px !important;">
                                <h4>
                                    <?php echo e($p->numero_orden); ?> : <?php echo e($p->nombre_cliente); ?> 

                                </h4>
                                <div class="card-header-action">
                                    <a class="btn btn-icon btn-success" style="cursor: pointer"
                                        onclick='terminarPreparacion(<?php echo e("$p->id"); ?>)' title="Teminar preparación orden"><i
                                            class="fas fa-check"></i></a>
                                    <a data-collapse="#mycard-collapse<?php echo e($p->id); ?>" title="Esconder"
                                        class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                                </div>
                            </div>
                            <div class="collapse show" id="mycard-collapse<?php echo e($p->id); ?>">
                                <div class="card-body" style="padding: 5px !important;">
                                    <div class="row">
                                        <div class="col-12">
                                           
                                                <h6 style="cursor: pointer">
                                                    Estado : <?php echo e($p->descEstado ?? ''); ?> </h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer" style="padding: 5px !important;">
                                    <div class="table-responsive">
                                        <h4>Detalle de orden</h4>
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $p->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr style="border-top:1px solid black ">
                                                        <td><i class="fas fa-box text-secondary" aria-hidden="true"></i>
                                                            - <?php echo e($d->nombre_producto ?? ''); ?></td>
                                                        <td><?php echo e($d->cantidad ?? '0'); ?> </td>
                                                        <td><?php echo e($d->observacion ?? ''); ?></td>
                                                    </tr>
                                                    <?php if($d->tieneExtras): ?>
                                                    <tr>
                                                        <td>
                                                        <table class="table table-hover mb-0" >
                                                            <thead>
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Extras </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__currentLoopData = $d->extras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td><i class="fas fa-box text-secondary" aria-hidden="true"></i>
                                                                            - <?php echo e($e->descripcion_extra ?? ''); ?></td>
                                                                    </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </tbody>
                                                        </table></td>
                                                    </tr>

                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    </div>

    <!-- modal modal de agregar producto -->
    <div class="modal fade bs-example-modal-center" id='mdl_agregar_producto' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar producto-->
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/facturacion/ordenesPreparacion.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/facturacion/ordenesPreparacion.blade.php ENDPATH**/ ?>