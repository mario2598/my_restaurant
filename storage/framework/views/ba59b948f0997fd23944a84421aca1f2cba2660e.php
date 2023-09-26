<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="<?php echo e(URL::to('ingresos/guardar')); ?>" method="POST">
                                <?php echo e(csrf_field()); ?>

                                <input type="hidden" name="id" value="<?php echo e($data['ingreso']->id); ?>">
                                <div class="card-header">
                                    <h4>Ingreso   
                                        <?php if($data['ingreso']->aprobado == 'N'): ?>
                                                Sin Aprobar
                                            <?php endif; ?>
                                            <?php if($data['ingreso']->aprobado == 'S'): ?>
                                                Aprobado
                                            <?php endif; ?>
                                            <?php if($data['ingreso']->aprobado == 'R'): ?>
                                                Rechazado
                                            <?php endif; ?>
                                             - <?php echo e($data['ingreso']->nombreUsuario); ?>  - CRC
                                             <?php echo e(number_format($data['ingreso']->subtotal ?? '0.00', 2, '.', ',')); ?>

                                            </h4>

                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php echo e($data['ingreso']->fecha); ?>">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Sucursal</label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php echo e($data['ingreso']->nombreSucursal); ?>">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto Efectivo (CRC)
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_efectivo"
                                                    name="monto_efectivo"
                                                    value="<?php echo e($data['ingreso']->monto_efectivo ?? ''); ?>"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto Tarjeta (CRC)
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_tarjeta"
                                                    name="monto_tarjeta"
                                                    value="<?php echo e($data['ingreso']->monto_tarjeta ?? ''); ?>" placeholder="0.00"
                                                    min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Monto SINPE (CRC) 
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_sinpe"
                                                    name="monto_sinpe" value="<?php echo e($data['ingreso']->monto_sinpe ?? ''); ?>"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Tipo ingreso</label>
                                                <select class="form-control space_disabled" name="tipo_ingreso">
                                                    <?php $__currentLoopData = $data['tipos_ingreso']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($i->id); ?>" title="<?php echo e($i->tipo ?? ''); ?>"
                                                            <?php if($i->id == ($data['ingreso']->tipo ?? -1)): ?> selected <?php endif; ?>><?php echo e($i->tipo); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php if($data['ingreso']->cliente != null): ?>
                                            <div class="col-12 col-md-4 col-lg-4">
                                                <div class="form-group">
                                                    <label>Cliente</label>
                                                    <select class="form-control" name="cliente">
                                                        <?php $__currentLoopData = $data['clientes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($i->id); ?>"
                                                                title="<?php echo e($i->nombre ?? ''); ?>" <?php if($i->id == ($data['ingreso']->cliente ?? -1)): ?> selected <?php endif; ?>><?php echo e($i->nombre); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label>Descripción del ingreso</label>
                                                <textarea class="form-control" required maxlength="300" readonly
                                                    name="descripcion"><?php echo e($data['ingreso']->descripcion ?? ''); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label>Observación</label>
                                                <textarea class="form-control" name="observacion"
                                                    maxlength="150"><?php echo e($data['ingreso']->observacion ?? ''); ?></textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <?php if($data['ingreso']->aprobado == 'N'): ?>
                                        <a onclick='confirmarIngreso("<?php echo e($data['ingreso']->id); ?>")'
                                            style="cursor: pointer; color:white;" class="btn btn-success">Confirmar</a>
                                    <?php endif; ?>
                                </div>


                            </form>
                        </div>
                    </div>
          
                    <?php if($data['tieneVentas']): ?>
                        <div class="col-12 col-sm-12 col-lg-12">
                            <div>
                                <h4>Ventas Relacionadas</h4>
                            </div>
                            <div class="card">
                                <form class="card-header-form">
                                    <div class="input-group">
                                        <input type="text" name="" id="input_buscar_generico" class="form-control"
                                            placeholder="Buscar..">
                                    </div>
                                </form>
                                <table class="table" id="tbl-detallesAnular" style="max-height: 100%;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" style="text-align: center">No.Factura</th>
                                            <th scope="col" style="text-align: center">Fecha</th>
                                            <th scope="col" style="text-align: center">Total pagado</th>
                                            <th scope="col" style="text-align: center">Cliente</th>
                                            <th scope="col" style="text-align: center">Imprimir</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-ventasRel">
                                        <?php $__currentLoopData = $data['ventas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td style="text-align: center"><?php echo e($i->numero_orden); ?></td>
                                                <td style="text-align: center"><?php echo e($i->fecha_inicio); ?></td>
                                                <td style="text-align: center"><?php echo e(number_format($i->total ?? '0.00', 2, '.', ',')); ?></td>
                                                <td style="text-align: center"><?php echo e($i->nombre_cliente ?? '*'); ?></td>
                                                <td style="text-align: center"><button class="btn btn-primary" style="width: 100%" 
                                                         onclick='tickete("<?php echo e($i->id); ?>")'>IMPRIMIR
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    </div>
    </section>
    </div>
    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

    <script src="<?php echo e(asset('assets/js/ingresos.js')); ?>"></script>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/ingresos/ingreso/ingreso.blade.php ENDPATH**/ ?>