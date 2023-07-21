<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <section class="section">
            <form method="POST" action="<?php echo e(URL::to('materiaPrima/producto/guardar')); ?>" autocomplete="off">
                <?php echo e(csrf_field()); ?>

                <input type="hidden" name="id" value="-1">

                <div class="card">
                    <div class="card-header">
                        <h4>Ingresar Producto Materia Prima</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Nombre</label>
                                    <textarea class="form-control" name="nombre" id="nombre"
                                     maxlength="5000"><?php echo e($data['datos']['nombre'] ?? ''); ?></textarea>

                                </div>
                            </div>
                         

                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group mb-0">
                                    <label>* Unidad de medida</label>
                                    <input type="text" class="form-control" id="unidad_medida" name="unidad_medida"
                                    value="<?php echo e($data['datos']['unidad_medida'] ?? ''); ?>" required maxlength="100">
                                </div>
                            </div>

                            <!-- categoria -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control" id="proveedor" name="proveedor">
                                        <?php $__currentLoopData = $data['proveedores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->descripcion ?? ''); ?>" 
                                          <?php if($i->id == ($data['datos']['proveedor'] ?? 0)): ?>
                                                selected
                                            <?php endif; ?>
                                          ><?php echo e($i->nombre ?? ''); ?></option>
                                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <!-- precio -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Precio CRC</label>
                                    <input type="number" class="form-control" id="precio" name="precio" step="any"
                                        value="<?php echo e($data['datos']['precio'] ?? ''); ?>" required min="0">
                                </div>
                            </div>
                            <!-- enviar -->
                            <div class="col-sm-12 col-md-6 col-xl-4">
                                <div class="form-group">
                                    <label>Guardar producto</label>
                                    <input type="submit" class="btn btn-primary form-control" value="Guardar">
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            </form>

        </section>

    </div>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/jquery-ui/jquery-ui.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/bodega/productos.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/materiaPrima/producto/nuevoProducto.blade.php ENDPATH**/ ?>