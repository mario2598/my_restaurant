<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-8">
                        <div class="card">
                            <form action="<?php echo e(URL::to('ingresos/guardar')); ?>" method="POST">
                                <?php echo e(csrf_field()); ?>

                                <input type="hidden" name="id" value="<?php echo e($data['ingreso']->id); ?>">
                                <div class="card-header">
                                    <h4>Ingreso - <?php echo e($data['ingreso']->nombreUsuario); ?></h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php echo e($data['ingreso']->fecha); ?>">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Sucursal</label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php echo e($data['ingreso']->nombreSucursal); ?>">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Monto Efectivo (CRC)
                                                     <?php if($data['tieneReporteCajero']): ?>
                                                      <strong> 
                                                        - Reportado <?php echo e(number_format($data['reporte_cajero']->monto_efectivo ?? '0.00', 2, '.', ',')); ?>

                                                      </strong>
                                                    <?php endif; ?>
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_efectivo"
                                                    name="monto_efectivo"
                                                    value="<?php echo e($data['ingreso']->monto_efectivo ?? ''); ?>"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Monto Tarjeta (CRC)
                                                  <?php if($data['tieneReporteCajero']): ?>
                                                    <strong> 
                                                      - Reportado <?php echo e(number_format($data['reporte_cajero']->monto_tarjeta ?? '0.00', 2, '.', ',')); ?>

                                                    </strong>
                                                  <?php endif; ?>

                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_tarjeta"
                                                    name="monto_tarjeta"
                                                    value="<?php echo e($data['ingreso']->monto_tarjeta ?? ''); ?>" placeholder="0.00"
                                                    min="0">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Monto SINPE (CRC) 
                                                  <?php if($data['tieneReporteCajero']): ?>
                                                    <strong> 
                                                      - Reportado <?php echo e(number_format($data['reporte_cajero']->monto_sinpe ?? '0.00', 2, '.', ',')); ?>

                                                    </strong>
                                                  <?php endif; ?>
                                                </label>
                                                <input type="number" class="form-control" step=any id="monto_sinpe"
                                                    name="monto_sinpe" value="<?php echo e($data['ingreso']->monto_sinpe ?? ''); ?>"
                                                    placeholder="0.00" min="0">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-6">
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
                                            <div class="col-12 col-md-4 col-lg-6">
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

                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group mb-0">
                                                <label>Descripción del ingreso</label>
                                                <textarea class="form-control" required maxlength="300" readonly
                                                    name="descripcion"><?php echo e($data['ingreso']->descripcion ?? ''); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-6">
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
                                        <a onclick='rechazarIngreso("<?php echo e($data['ingreso']->id); ?>")'
                                            style="cursor: pointer; color:white;" class="btn btn-info">Rechazar</a>
                                        <a onclick='confirmarIngreso("<?php echo e($data['ingreso']->id); ?>")'
                                            style="cursor: pointer; color:white;" class="btn btn-success">Confirmar</a>
                                    <?php endif; ?>
                                    <?php if($data['ingreso']->aprobado != 'E'): ?>
                                        <a onclick='eliminarIngresoAdmin("<?php echo e($data['ingreso']->id); ?>")'
                                            style="cursor: pointer; color:white;" class="btn btn-warning">Eliminar</a>
                                        <input type="submit" class="btn btn-primary" value="Guardar" />
                                    <?php endif; ?>

                                </div>


                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-4">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card l-bg-orange">
                                <div class="card-statistic-3">
                                    <div class="card-icon card-icon-large"><i class="fa fa-money-bill-alt"></i></div>
                                    <div class="card-content">
                                        <h4 class="card-title">Ingreso -
                                            <?php if($data['ingreso']->aprobado == 'N'): ?>

                                                Sin Aprobar
                                            <?php endif; ?>
                                            <?php if($data['ingreso']->aprobado == 'S'): ?>

                                                Aprobado
                                            <?php endif; ?>
                                            <?php if($data['ingreso']->aprobado == 'R'): ?>

                                                Rechazado
                                            <?php endif; ?>
                                            <?php if($data['ingreso']->aprobado == 'E'): ?>

                                                Eliminado
                                            <?php endif; ?>

                                        </h4>
                                        <span>CRC
                                            <?php echo e(number_format($data['ingreso']->subtotal ?? '0.00', 2, '.', ',')); ?></span>
                                        <div class="progress mt-1 mb-1" data-height="8">
                                            <?php if($data['ingreso']->subtotal >= $data['estadisticas']['promedio']): ?>
                                                <div class="progress-bar l-bg-green" role="progressbar" data-width="75%"
                                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                            <?php endif; ?>
                                            <?php if($data['ingreso']->subtotal < $data['estadisticas']['promedio']): ?>
                                                <div class="progress-bar l-bg-red" role="progressbar" data-width="25%"
                                                    aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                            <?php endif; ?>


                                        </div>
                                    </div>
                                    <p class="mb-0 text-sm">

                                        <?php if($data['ingreso']->subtotal >= $data['estadisticas']['promedio']): ?>
                                            <span class="mr-2" style="color: green">
                                                <i style="color: green" class="fa fa-arrow-up"></i> Sobre el promedio
                                                mensual
                                        <?php endif; ?>
                                        <?php if($data['ingreso']->subtotal < $data['estadisticas']['promedio']): ?>
                                            <span class="mr-2" style="color: red">
                                                <i style="color: red" class="fa fa-arrow-down"></i> Bajo el promedio mensual
                                        <?php endif; ?>
                                        </span>
                                        <span class="text-nowrap">Promedio Mensual CRC
                                            <strong><?php echo e(number_format($data['estadisticas']['promedio'] ?? '0.00', 2, '.', ',')); ?></strong></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if($data['tieneVentas']): ?>
                        <div class="col-12 col-sm-12 col-lg-12">
                            <div>
                                <h4>Ventas</h4>
                            </div>
                            <div id="accordion">
                                <?php $__currentLoopData = $data['ventasParciales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="card">
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0">
                                                <button class="btn btn-primary" style="width: 100%" onclick='ticketeParcial("<?php echo e($i->ordenObj->id); ?>")'>IMPRIMIR
                                                </button>
                                                <button class="btn btn-link" data-toggle="collapse"
                                                    data-target="#collapse<?php echo e($i->ordenObj->id); ?>" aria-expanded="false"
                                                    aria-controls="collapseOne">
                                                    <?php echo e($i->ordenObj->nombre_cliente ?? '*'); ?> | PAGO PARCIAL | ORD-<?php echo e($i->ordenObj->numero_orden); ?> - CRC
                                                    <?php echo e(number_format($i->cancelado  ?? '0.00', 2, '.', ',')); ?> -
                                                    <?php echo e($i->ordenObj->fecha_inicio); ?>

                                                </button>
                                            </h5>
                                        </div>

                                        <div id="collapse<?php echo e($i->ordenObj->id); ?>" class="collapse hide"
                                            aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">
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
                                                            <?php $__currentLoopData = $i->ordenObj->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><i class="<?php echo e($d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary'); ?>"
                                                                            aria-hidden="true"
                                                                            style="<?php echo e($d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;'); ?>"></i>
                                                                        - <?php echo e($d->nombre_producto ?? ''); ?>

                                                                    </td>
                                                                    <td><?php echo e($d->cantidad ?? '0'); ?> </td>
                                                                    <td><?php echo e($d->observacion ?? ''); ?></td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php $__currentLoopData = $data['ventas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="card">
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0">
                                                <button class="btn btn-primary" style="width: 100%" onclick='tickete("<?php echo e($i->id); ?>")'>IMPRIMIR
                                                </button>
                                                <button class="btn btn-link" data-toggle="collapse"
                                                    data-target="#collapse<?php echo e($i->id); ?>" aria-expanded="false"
                                                    aria-controls="collapseOne">
                                                    <?php echo e($i->nombre_cliente ?? '*'); ?> | ORD-<?php echo e($i->numero_orden); ?> - CRC
                                                    <?php echo e(number_format($i->total ?? '0.00', 2, '.', ',')); ?> -
                                                    <?php echo e($i->fecha_inicio); ?>

                                                </button>
                                            </h5>
                                        </div>

                                        <div id="collapse<?php echo e($i->id); ?>" class="collapse hide"
                                            aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">
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
                                                            <?php $__currentLoopData = $i->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><i class="<?php echo e($d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary'); ?>"
                                                                            aria-hidden="true"
                                                                            style="<?php echo e($d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;'); ?>"></i>
                                                                        - <?php echo e($d->nombre_producto ?? ''); ?>

                                                                    </td>
                                                                    <td><?php echo e($d->cantidad ?? '0'); ?> </td>
                                                                    <td><?php echo e($d->observacion ?? ''); ?></td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    </div>
    </section>
    </div>
    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    

    <form id="formEliminarIngreso" action="<?php echo e(URL::to('ingresos/eliminar')); ?>" style="display: none" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idIngresoEliminar" id="idIngresoEliminar" value="-1">

    </form>


    <form id="formAprobarIngreso" action="<?php echo e(URL::to('ingresos/aprobar')); ?>" style="display: none" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idIngresoAprobar" id="idIngresoAprobar" value="-1">
    </form>

    <form id="formRechazarIngreso" action="<?php echo e(URL::to('ingresos/rechazar')); ?>" style="display: none" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idIngresoRechazar" id="idIngresoRechazar" value="-1">
    </form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

    <script src="<?php echo e(asset('assets/js/ingresos.js')); ?>"></script>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/ingresos/ingreso/ingresoSinGastos.blade.php ENDPATH**/ ?>