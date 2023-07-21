<?php $__env->startSection('style'); ?>


<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

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
              <?php $__currentLoopData = $data['ingresosSinAprobar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-12 col-md-6 col-lg-6" onclick='clickIngreso("<?php echo e($g->id); ?>")' style="cursor: pointer">

                  <div class="card card-primary">
                    <div class="card-header">
                      <h4><?php echo e(($g->tipoIngreso ?? '')); ?></h4>
                      <div class="card-header-action">
                        <small><?php echo e(($g->nombreUsuario ?? '')); ?></small>
                      </div>
                    </div>
                    <div class="card-body">
                      <p><small><?php echo e($g->fecha ?? ''); ?></small> <br> 
                        <div class="card-footer pt-3 d-flex justify-content-center">
                          <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                              <div class="budget-price justify-content-center">
                                <div class="budget-price-label" style="margin-right: 5px;">Sub Total</div>
                                <div class="budget-price-square bg-primary" data-width="20" style="width: 20px;"></div>
                                <div class="budget-price-label">CRC <?php echo e(number_format($g->subTotal ?? '0.00',2,".",",")); ?></div>
                              </div>
                            </div>
                            <div class="col-12 col-sm-12 col-lg-12">
                              <div class="budget-price justify-content-center">
                                <div class="budget-price-label" style="margin-right: 5px;">Gastos</div>
                                <div class="budget-price-square bg-danger" data-width="20" style="width: 20px;"></div>
                                <div class="budget-price-label">CRC <?php echo e(number_format($g->totalGastos ?? '0.00',2,".",",")); ?></div>
                              </div>
                            </div>
                            <div class="col-12 col-sm-12 col-lg-12">
                              <div class="budget-price justify-content-center">
                                <div class="budget-price-label" style="margin-right: 5px;">Total</div>
                                <div class="budget-price-square bg-success" data-width="20" style="width: 20px;"></div>
                                <div class="budget-price-label">CRC <?php echo e(number_format($g->total ?? '0.00',2,".",",")); ?></div>
                              </div>
                            </div>
                          </div>
                         
                        </div>
                        <small><strong>Descripción : </strong> <?php echo e($g->descripcion ?? ''); ?> </small><br>
                       
                      </p> 
                      
                    </div>
                    
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
           
          </div>
        </div>
      
      </div>
    </section>
    
  </div>


<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>

  <script src="<?php echo e(asset("assets/js/ingresos_pendientes.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/ingresos/ingresosPendientes.blade.php ENDPATH**/ ?>