<?php $__env->startSection('style'); ?>


<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="card card-warning">
          <div class="card-header">
            <h4>Parámetros generales</h4>
           
          </div>
          <div class="card-body">
          <form action="<?php echo e(URL::to('mant/guardarparametrosgenerales')); ?>"  autocomplete="off" method="POST" >
            <?php echo e(csrf_field()); ?>

            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>% Cobro Banco</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <i class="fas fa-university"></i>
                      </div>
                    </div>
                    <input type="number" class="form-control " step="any" id="cobro" name="cobro" value="<?php echo e($data['parametros_generales']->porcentaje_banco ??""); ?>" required max="99" min="0">
                  </div>
                  
                </div>
              </div>
              <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>Tiempo refresco Monitor de Movimientos (Segundos)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <i class="fas fa-clock"></i>
                      </div>
                    </div>
                    <input type="number" class="form-control " id="tiempoMonitorMov" name="tiempoMonitorMov" value="<?php echo e($data['parametros_generales']->tiempo_refresco_monitor_movimientos ??""); ?>" required max="99" min="0">
                  </div>
                  
                </div>
              </div>
              <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>Saldo inicial mes cafetería (CRC)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <i class="fas fa-clock"></i>
                      </div>
                    </div>
                    <input type="number" step="any" class="form-control " id="inicio_mes_cafeteria" name="inicio_mes_cafeteria"  value="<?php echo e($data['parametros_generales']->inicio_mes_cafeteria ??0); ?>" required >
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>Saldo inicial mes panadería (CRC)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <i class="fas fa-clock"></i>
                      </div>
                    </div>
                    <input type="number" step="any" class="form-control " id="inicio_mes_panaderia" name="inicio_mes_panaderia"  value="<?php echo e($data['parametros_generales']->inicio_mes_panaderia ??0); ?>" required >
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="form-group">
                  <label>Guardar</label>
                  <input type="submit" class="btn btn-primary form-control" value="Guardar">
                </div>
              </div>
           
            </div>
          </form>
          </div>
        </div>
      
      </div>
    </section>
    
      
    
  </div>



<?php $__env->startSection('script'); ?>

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/mant/parametros_generales.blade.php ENDPATH**/ ?>