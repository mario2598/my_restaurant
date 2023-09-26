<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

<style>


  .trIngreso :hover {
     
      font-weight: bold;
  }
</style>
    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Ingresos Pendientes de aprobar</h4>

                    </div>
                    <div class="card-body">
                        <div id="contenedor_ingresos_sin_aprobar" class="row">
                            <table class="table" id="tbl-ordenes" style="max-height: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" style="text-align: center">Tipo Ingreso</th>
                                        <th scope="col" style="text-align: center">Usuario</th>
                                        <th scope="col" style="text-align: center">Fecha</th>
                                        <th scope="col" style="text-align: center">Descripción</th>
                                        <th scope="col" style="text-align: center">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-ordenes" class="trIngreso">


                                    <?php $__currentLoopData = $data['ingresosSinAprobar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr onclick='clickIngreso("<?php echo e($g->id); ?>")' style="cursor: pointer" >
                                            <td>
                                                <?php echo e($g->tipoIngreso ?? ''); ?>

                                            </td>
                                            <td>
                                                <?php echo e($g->nombreUsuario ?? ''); ?>

                                            </td>
                                            <td>
                                                <?php echo e($g->fecha ?? ''); ?>

                                            </td>
                                            <td>
                                                <?php echo e($g->descripcion ?? ''); ?>

                                            </td>
                                            <td>
                                                CRC <?php echo e(number_format($g->total ?? '0.00', 2, '.', ',')); ?>

                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </section>

    </div>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/js/ingresos_pendientes.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/ingresos/ingresosPendientes.blade.php ENDPATH**/ ?>