<?php $__env->startSection('style'); ?>


<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="card card-warning">
          <div class="card-header">
            <h4>Gastos Pendientes de aprobar</h4>
            <form class="card-header-form">
              <div class="input-group">
                <input type="text" name=""  onkeyup="filtrarGastosPendientesAdmin(this.value)" id="btn_buscar_gasto" class="form-control" placeholder="Buscar gasto">
                <div class="input-group-btn">
                  <a class="btn btn-primary btn-icon" style="cursor: pointer;" onclick="filtrarGastosPendientesAdmin(btn_buscar_gasto.value)"><i class="fas fa-search"></i></a>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body">
            <div id="contenedor_gastos_sin_aprobar" class="row">
              <?php $__currentLoopData = $data['gastosSinAprobar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-12 col-md-6 col-lg-6">

                  <div class="card card-primary">
                    <div class="card-header">
                      <h4>CRC <?php echo e(number_format($g->monto,2,".",",")); ?>  <small>- <?php echo e($g->nombre ?? ''); ?> </small></h4>
                      <div class="card-header-action">
                        <?php if($g->caja_cerrada == 'N'): ?>
                           <small>* Caja sin cerrar</small>
                        <?php endif; ?>
                        <?php if($g->caja_cerrada == 'A'): ?>
                          <a  onclick='clickGasto("<?php echo e($g->id); ?>")' style="cursor: pointer; color:white;" class="btn btn-primary">Ver</a>
                          <a onclick='rechazarGastoUsuario("<?php echo e($g->id); ?>")' style="color:white" class="btn btn-primary">Rechazar</a>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="card-body">
                      <p><strong><?php echo e(strtoupper($g->nombreUsuario ?? '')); ?> - <?php echo e($g->fecha ?? ''); ?></strong> <br> 
                        <small><?php echo e(($g->descripcion ?? '')); ?> </small><br>
                        <?php if($g->observacion != null && $g->observacion != ""): ?>
                          <small><strong>Observación : </strong> <?php echo e($g->observacion ?? ''); ?> </small><br>
                        <?php endif; ?>
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

  <script src="<?php echo e(asset("assets/js/gastos_pendientes.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/gastos/gastosPendientesAdmin.blade.php ENDPATH**/ ?>