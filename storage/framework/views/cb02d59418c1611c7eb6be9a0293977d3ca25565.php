<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <section class="section">
            <form method="POST" action="<?php echo e(URL::to('materiaPrima/producto/guardar')); ?>" autocomplete="off">
                <?php echo e(csrf_field()); ?>

                <input type="hidden" name="id" value="<?php echo e($data['producto']->id); ?>">
                <div class="card">
                    <div class="card-header">
                        <h4>Editar Producto Menú</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <!-- descripción -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Nombre </label>
                                    <textarea class="form-control" name="nombre" id="nombre" maxlength="2000"><?php echo e($data['producto']->nombre ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group mb-0">
                                    <label>* Unidad de medida</label>
                                    <input type="text" class="form-control" id="unidad_medida" name="unidad_medida"
                                        value="<?php echo e($data['producto']->unidad_medida); ?>" required maxlength="100">
                                </div>
                            </div>

                            <!-- categoria -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control" id="proveedor" name="proveedor">
                                        <?php $__currentLoopData = $data['proveedores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                                <?php if($i->id == ($data['producto']->proveedor ?? 0)): ?> selected <?php endif; ?>><?php echo e($i->nombre ?? ''); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <!-- precio -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>* Precio CRC</label>
                                    <input type="number" class="form-control" id="precio" name="precio" step=any
                                        value="<?php echo e($data['producto']->precio ?? ''); ?>" required min="0">
                                </div>
                            </div>

                            <!-- enviar -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Guardar producto</label>
                                    <input type="submit" class="btn btn-primary form-control" value="Guardar">
                                </div>
                            </div>
                            <!-- eliminar -->
                            <div class="col-sm-12 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label>Eliminar producto</label>
                                    <a class="btn btn-danger form-control"
                                        onclick='eliminarProducto("<?php echo e($data['producto']->id); ?>")'
                                        style="color: white;cursor: pointer;">Eliminar </a>
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            </form>

        </section>

    </div>

    <form id="formEliminarProducto" action="<?php echo e(URL::to('materiaPrima/producto/eliminar')); ?>" style="display: none" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idProductoEliminar" id="idProductoEliminar" value="-1">
    </form>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/jquery-ui/jquery-ui.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/materiaPrima/productos.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/materiaPrima/producto/editarProducto.blade.php ENDPATH**/ ?>