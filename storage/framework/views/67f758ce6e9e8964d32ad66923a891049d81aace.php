<?php $__currentLoopData = $data['ordenes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="p-15 border-bottom">
        <div class="col-12">
            <div class="card">
                <div class="card-body card-type-3">
                    <div class="row">
                        <div class="col">

                            <span class="font-weight-bold mb-0">Mesa No.<?php echo e($o->numero_mesa ?? '##'); ?></span><br>
                            <span class="font-weight-bold mb-0">ORD-<?php echo e($o->numero_orden ?? '##'); ?></span><br>
                            <span class="text-nowrap"><?php echo e($o->nombre_cliente ?? 'Cliente'); ?></span><br>
                            <span class="text-nowrap">
                                <?php switch($o->estado):
                                    case ("LF"): ?>
                                    Listo para facturar
                                    <?php break; ?>
                                    <?php case ("EP"): ?>
                                    En preparación
                                    <?php break; ?>
                                    <?php case ("PT"): ?>
                                    En espera de entregar
                                    <?php break; ?>
                                    <?php default: ?>

                                <?php endswitch; ?></span><br>
                            <span class="font-weight-bold mb-0">CRC <?php echo e(number_format($o->total ?? 0, 2, '.', ',')); ?></span>
                        </div>
                    </div>
                        <p class="mt-3 mb-0 text-muted text-sm">
                            <a style="cursor: pointer" onclick='goFacturaOrden("<?php echo e($o->id); ?>")' class="btn btn-icon icon-left btn-restore-theme">
                                <i class="fas fa-payment"></i> Detalles..
                            </a>
                        </p>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<div class="p-15 border-bottom">
    <div class="col-12">
        <div class="card">
        </div>
    </div>
</div>
<?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/layout/layout/comandaBar.blade.php ENDPATH**/ ?>