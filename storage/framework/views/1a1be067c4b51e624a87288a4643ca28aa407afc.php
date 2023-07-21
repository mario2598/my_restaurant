<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

 <!-- Main Content -->
 <div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 col-md-6 col-lg-12">
            <div class="card">
              <form  action="<?php echo e(URL::to('ingresos/guardar')); ?>"  method="POST">
                <?php echo e(csrf_field()); ?>

                <input type="hidden" name="id" value="-1">
                <div class="card-header">
                  <h4>Registrar ingreso</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                      <div class="col-sm-12 col-md-6 col-xl-4">
                        <div class="form-group">
                          <label>Fecha Ingreso </label>
                        <input type="date" id="fecha" name="fecha" max='<?php echo e(date('Y-m-d')); ?>' class="form-control" >
                        </div>
                      </div>
                        
                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                              <label>Monto Efectivo (CRC)</label>
                              <input type="number" class="form-control" step=any id="monto_efectivo" name="monto_efectivo" value="<?php echo e($data['datos']['monto_efectivo'] ??""); ?>" placeholder="0.00" min="0">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                              <label>Monto Tarjeta (CRC)</label>
                              <input type="number" class="form-control" step=any id="monto_tarjeta" name="monto_tarjeta" value="<?php echo e($data['datos']['monto_tarjeta'] ??""); ?>" placeholder="0.00" min="0">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                              <label>Monto SINPE (CRC)</label>
                          <input type="number" class="form-control" step=any id="monto_sinpe" name="monto_sinpe" value="<?php echo e($data['datos']['monto_sinpe'] ??""); ?>" placeholder="0.00"  min="0">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                        <div class="form-group">
                              <label>Tipo ingreso</label>
                              <select class="form-control" name="tipo_ingreso">
                                  <?php $__currentLoopData = $data['tipos_ingreso']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <option value="<?php echo e($i->id); ?>" title="<?php echo e($i->tipo ?? ''); ?>" 
                                    <?php if($i->id == ($data['datos']['tipo_ingreso'] ?? -1)): ?>
                                        selected
                                    <?php endif; ?>
                                    ><?php echo e($i->tipo); ?></option>
                                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                              </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                              <label>Cliente</label>
                              <select class="form-control" name="cliente">
                                <option value="null" > Sin Cliente </option>
                                <?php $__currentLoopData = $data['clientes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->nombre ?? ''); ?>" 
                                    <?php if($i->id == ($data['datos']['cliente'] ?? -1)): ?>
                                        selected
                                    <?php endif; ?>
                                    ><?php echo e($i->nombre ?? ''); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                  
                              </select>
                            </div>
                      </div>
                      
                      <div class="col-12 col-md-6 col-lg-6">
                        <div class="form-group mb-0">
                            <label>Descripción del ingreso</label>
                            <textarea class="form-control" required maxlength="300" name="descripcion"><?php echo e($data['datos']['descripcion'] ??""); ?></textarea>
                          </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-group mb-0">
                            <label>Observación</label>
                            <textarea class="form-control" name="observacion" maxlength="150"><?php echo e($data['datos']['observacion'] ??""); ?></textarea>
                          </div>
                      </div>
                        
                          
                    </div>   
                </div>      
                 
               
                <div class="card-footer text-right">
                  <input type="submit" class="btn btn-primary" value="Registrar"/>
                </div>
              </form>
            </div>
            </div>
       
    </div>
</div>
</section>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/ingresos/registrarIngresoAdmin.blade.php ENDPATH**/ ?>