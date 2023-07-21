<?php $__currentLoopData = $data['pedidos_pendientes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(count($p->detalles) > 0): ?>
        <div class="col-md-6 col-xs-12 col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4>
                        <?php if($p->tipo == 'CA' || $p->tipo == 'M'): ?>
                            Mesa No.<?php echo e($p->numero_mesa); ?>

                        <?php else: ?>
                            Orden No.<?php echo e($p->numero_orden); ?>

                        <?php endif; ?>
                    </h4>
                    <div class="card-header-action">
                        <a class="btn btn-icon btn-success" style="cursor: pointer"
                        onclick='terminarOrdenComida("<?php echo e($p->id); ?>","<?php echo e($p->fecha_creacion_detalle); ?>")' title="Terminar orden"><i
                                class="fas fa-check"></i></a>
                        <a data-collapse="#mycard-collapse<?php echo e($p->id); ?>" title="Esconder"
                            class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                    </div>
                </div>
                <div class="collapse show" id="mycard-collapse<?php echo e($p->id); ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h6>Hora : <?php echo e($p->fecha_inicio_hora_tiempo); ?></h6><br>

                            </div>
                            <?php if($p->tipo == 'CA' || $p->tipo == 'M'): ?>
                                <div class="col-12">
                                    <h6 style="cursor: pointer">
                                        Orden No.<?php echo e($p->numero_orden); ?></h6><br>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
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
                                        <?php if($d->cantidad - $d->cantidad_preparada > 0): ?>
                                            <tr>
                                                <td><i class="<?php echo e($d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary'); ?>"
                                                        aria-hidden="true"
                                                        style="<?php echo e($d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;'); ?>"></i>
                                                    - <?php echo e($d->nombre_producto ?? ''); ?></td>
                                                <td><?php echo e($d->cantidad - $d->cantidad_preparada); ?> </td>
                                                <td><?php echo e($d->observacion ?? ''); ?></td>
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
<?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/cocina/cocina/layout/contenedor_comandas.blade.php ENDPATH**/ ?>