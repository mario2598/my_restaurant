<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

 <!-- Main Content -->
 <div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
              <form  action="<?php echo e(URL::to('gastos/guardar')); ?>"  method="POST" enctype="multipart/form-data">
                <?php echo e(csrf_field()); ?>

                <input type="hidden" name="id" value="-1">
                <div class="card-header">
                  <h4>Ingresar gasto</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Proveedor</label>
                                <select class="form-control" name="proveedor">
                                    <?php $__currentLoopData = $data['proveedores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                        <?php if($i->id == ($data['datos']['proveedor'] ?? -1)): ?>
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
                                    <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->tipo ?? ''); ?>" ><?php echo e($i->tipo ?? ''); ?></option>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Tipo de documento</label>
                                <select class="form-control" name="tipo_documento">
                                    <option value="F" >Factura</option>
                                    <option value="O" title="Debera definir en observación">Otro</option>
                                </select>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Número comprobante</label>
                                <input type="text" class="form-control" name="num_comprobante" value="<?php echo e($data['datos']['num_comprobante'] ??""); ?>" maxlength="50" >
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                <label>Total CRC</label>
                                <input type="number" step="any" class="form-control" name="total" value="<?php echo e($data['datos']['total'] ??""); ?>" placeholder="0.00" min="10" required>
                              </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label>Descripción del gasto</label>
                                <textarea class="form-control" name="descripcion" required><?php echo e($data['datos']['descripcion'] ??""); ?></textarea>
                              </div>
                        </div>
                       
                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group ">
                              <label>Foto comprobante</label>
                                <input type="file" class="form-control" id="foto_comprobante" name="foto_comprobante" accept="image/png, image/jpeg, image/jpg" onchange="fileValidation()" >
								                <input type="text" id="foto_comprobante_b64" style='display:none;' name="foto_comprobante_b64">

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
<?php $__env->startSection('script'); ?>

  <script src="<?php echo e(asset("assets/js/gastos/gasto.js")); ?>"></script>
   
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/gastos/registrarGastoUsuario.blade.php ENDPATH**/ ?>