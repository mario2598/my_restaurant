<?php $__currentLoopData = $data['inventario']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr style="cursor: pointer;" onclick="seleccionarProductoInventario('<?php echo e($i->codigo_barra); ?>','<?php echo e($i->nombre); ?>','<?php echo e($i->cantidad); ?>')">
    <td scope="row" class="text-center">
        <?php echo e(strtoupper($i->codigo_barra ?? '')); ?>

    </td>
    <td class="text-center">
        <?php echo e($i->nombre ?? ''); ?>

    </td>
    <td class="text-center">
        <?php echo e($i->cantidad ?? ''); ?>

    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/bodega/inventario/layout/tablaInventario.blade.php ENDPATH**/ ?>