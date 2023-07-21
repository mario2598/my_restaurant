<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

 <!-- Main Content -->
 <div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
              <form  action="<?php echo e(URL::to('gastos/guardar')); ?>"  method="POST">
                <?php echo e(csrf_field()); ?>

              <input type="hidden" name="id" value="<?php echo e($data['gasto']->id); ?>">
                <div class="card-header">
                <h4>Información del gasto - <?php echo e($data['gasto']->nombreUsuario); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                            <label>Fecha</label>
                           <input type="text" class="form-control"  readonly value="<?php echo e($data['gasto']->fecha); ?>">
                          </div>
                    </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Proveedor</label>
                                <select class="form-control" name="proveedor">
                                    <?php $__currentLoopData = $data['proveedores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                        <?php if($i->id == ($data['gasto']->proveedor ?? -1)): ?>
                                            selected
                                        <?php endif; ?>
                                        ><?php echo e($i->nombre ?? ''); ?></option>
                                   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4"> 
                          <div class="form-group">
                            <label>Tipo de pago</label>
                            <select class="form-control" name="tipo_pago">
                                <?php $__currentLoopData = $data['tipos_pago']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->tipo ?? ''); ?>" 
                                    <?php if($i->id == ($data['gasto']->tipo_pago)): ?>
                                    selected
                                    <?php endif; ?>
                                    ><?php echo e($i->tipo ?? ''); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                            <label>Tipo de gasto</label>
                            <select class="form-control" name="tipo_gasto">
                              <?php $__currentLoopData = $data['tipos_gasto']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->tipo ?? ''); ?>" 
                                  <?php if($i->id == ($data['gasto']->tipo_gasto)): ?>
                                  selected
                                  <?php endif; ?>
                                  ><?php echo e($i->tipo ?? ''); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                            </select>
                          </div>

                      </div>

                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                          <label>Estado</label>
                          <select class="form-control space_disabled" name="aprobado">
                            <option value="S" <?php if ($data['gasto']->aprobado == 'S'){ echo 'selected';} ?>>Aprobado</option>
                            <option value="N" <?php if ($data['gasto']->aprobado == 'N'){ echo 'selected';} ?>>Sin Aprobar</option>
                            <option value="R" <?php if ($data['gasto']->aprobado == 'R'){ echo 'selected';} ?>>Rechazado</option>
                            <option value="E" <?php if ($data['gasto']->aprobado == 'E'){ echo 'selected';} ?>>Eliminado</option>
                          </select>
                        </div>
                    </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                              <label>Tipo de documento</label>
                              <select class="form-control" name="tipo_documento">
                                  <option value="F" 
                                  <?php if($data['gasto']->tipo_documento == "F"): ?>
                                      selected
                                  <?php endif; ?>
                                  >Factura</option>
                                  <option value="O" title="Debera definir en observación" 
                                  <?php if($data['gasto']->tipo_documento == "O"): ?>
                                    selected
                                  <?php endif; ?>
                                  >Otro</option>
                              </select>
                            </div>
                      </div>
                      
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Número comprobante</label>
                                <input type="text" class="form-control" name="num_comprobante" value="<?php echo e($data['gasto']->num_factura ??""); ?>" maxlength="50" >
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Total CRC</label>
                                <input type="number" step="any" class="form-control" name="total" value="<?php echo e($data['gasto']->monto ??""); ?>" placeholder="0.00" min="10" required>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label>Descripción del gasto</label>
                                <textarea class="form-control" name="descripcion" required><?php echo e($data['gasto']->descripcion ??""); ?></textarea>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label>Observación</label>
                                <textarea class="form-control" name="observacion"><?php echo e($data['gasto']->observacion ??""); ?></textarea>
                              </div>
                        </div>
                       
                       <?php if($data['gasto']->url_factura != null): ?>
                          <div class="col-sm-12 col-md-6 col-lg-4">
                            <div class="form-group">
                              <label>Foto comprobante</label>
                              <a class="btn btn-primary btn-icon form-control" style="cursor: pointer;color:white;" onclick='verFotoComprobanteGasto("<?php echo e($data["gasto"]->id); ?>")'><i class="fas fa-receipt"></i></a>
                            </div>
                          </div>
                        <?php endif; ?>
                    </div>   
                </div>      
                 
               
                <div class="card-footer text-right">
                  <?php if($data['gasto']->aprobado == 'N'): ?>
                  <a onclick='rechazarGastoUsuario("<?php echo e($data["gasto"]->id); ?>")' style="cursor: pointer; color:white;" class="btn btn-info" >Rechazar</a>
                  <a onclick='confirmarGasto("<?php echo e($data["gasto"]->id); ?>",this,"<?php echo e(number_format($data["gasto"]->monto,2,".",",")); ?>")' style="cursor: pointer; color:white;" class="btn btn-success" >Confirmar</a>
                <?php endif; ?>
                <?php if($data['gasto']->aprobado != 'E'): ?>
                  <input type="button" onclick='eliminarGastoAdmin("<?php echo e($data["gasto"]->id); ?>")' class="btn btn-warning" value="Eliminar"/>
                  <input type="submit" class="btn btn-primary" value="Guardar"/>
                <?php endif; ?>
                </div>
              </form>
            </div>
            </div>

    </div>
</div>
</section>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/gastos/gasto.blade.php ENDPATH**/ ?>