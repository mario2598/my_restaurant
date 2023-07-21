<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script>
        var inventarioLotes = [];
        var traslado = [];

    </script>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-6">

                        <div class="col-12 col-sm-12 col-lg-12">

                            <h5 style="font-size: 14px;">Inventario Disponible</h5>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaDetalle">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center"># Lote</th>
                                            <th scope="col" class="text-center">Producto</th>
                                            <th scope="col" class="text-center">Cantidad</th>
                                            <th scope="col" class="text-center">Agregar</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_inventario">
                                        <?php $__currentLoopData = $data['inventario_por_lote'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <script>
                                                    inventarioLotes.push({
                                                        "id": "<?php echo e($i->id); ?>",
                                                        "codigo":"<?php echo e($i->codigo); ?>",
                                                        "nombre": "<?php echo e($i->nombre); ?>",
                                                        "cantidad": "<?php echo e($i->cantidad); ?>",
                                                        "sucursal": "<?php echo e($i->sucursal); ?>",
                                                    });

                                                </script>
                                                <td class="text-center">
                                                    <?php echo e($i->codigo); ?>

                                                </td>
                                                <td class="text-center">
                                                    <?php echo e($i->nombre); ?>

                                                </td>
                                                <td class="text-center">
                                                    <?php echo e($i->cantidad); ?>

                                                </td>

                                                <td class="text-center">
                                                <button class="btn btn-icon btn-success" onclick='agregarProductoTraslado("<?php echo e($i->id); ?>")'
                                                        style="color: blanchedalmond"><i class="fas fa-plus"></i></button>
                                                </td>

                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-12 col-md-12 col-lg-6">

                        <div class="col-12 col-sm-12 col-lg-12">

                            <h5 style="font-size: 14px;">Detalle Traslado</h5>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaDetalle">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center"># Lote</th>
                                            <th scope="col" class="text-center">Producto</th>
                                            <th scope="col" class="text-center">Cantidad</th>
                                            <th scope="col" class="text-center">Devolver</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_traslado">
                                        <?php $__currentLoopData = $data['inventario_por_lote'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <script>
                                                traslado.push({
                                                    "id": "<?php echo e($i->id); ?>",
                                                    "codigo":"<?php echo e($i->codigo); ?>",
                                                    "nombre": "<?php echo e($i->nombre); ?>",
                                                    "cantidad": 0,
                                                    "sucursal": "<?php echo e($i->sucursal); ?>",
                                                });

                                            </script>
                                            <td class="text-center">
                                                <?php echo e($i->codigo); ?>

                                            </td>
                                            <td class="text-center">
                                                <?php echo e($i->nombre); ?>

                                            </td>
                                            <td class="text-center">
                                               0
                                            </td>

                                            <td class="text-center">
                                                <button class="btn btn-icon btn-success" onclick='eliminarProductoTraslado("<?php echo e($i->id); ?>")'
                                                    style="color: blanchedalmond"><i class="fas fa-minus"></i></button>
                                            </td>

                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Destino</label>
                            <select class="form-control" id="sucursal" name="sucursal" required>
                                <option value="-1" selected>Seleccione una sucursal</option>
                                <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($i->id ?? ''); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                ><?php echo e($i->descripcion ?? ''); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <div class="form-group mb-0">
                            <label>Detalle de traslado</label>
                            <textarea class="form-control" id="detalle_traslado" name="observacion"
                                id="detalle_movimiento_generado"
                                maxlength="150"><?php echo e($data['movimiento']->detalle ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <div class="form-group mb-0">
                            <label>Trasladar</label><br>
                        <a onclick='aplicarTrasladoSucursal("<?php echo e($data["sucursalAuth"]); ?>")'
                            style="cursor: pointer; color:white;" class="btn btn-warning">Aplicar Traslado</a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>



<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/js/inventario/trasladoSucursal.js')); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/inventario/trasladoInventartioSucursal.blade.php ENDPATH**/ ?>