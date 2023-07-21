<label>Permisos</label>
<?php $__currentLoopData = $data['vistas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="card-body">
<div class="section-title"><?php echo e($i->titulo ?? ''); ?></div>
    <?php $__currentLoopData = $i->submenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="pretty p-default p-curve p-thick">
        <input type="checkbox" name="menus[]" value="<?php echo e($m->id); ?>"
        <?php $__currentLoopData = $data['permisos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($p->vista == $m->id): ?>
            checked
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        />
            <div class="state p-warning">
            <label><?php echo e($m->titulo ?? ''); ?></label>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/mant/layout/permisosRoles.blade.php ENDPATH**/ ?>