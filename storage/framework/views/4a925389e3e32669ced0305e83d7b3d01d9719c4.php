<?php $__env->startSection('style'); ?>
  
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <form method="POST" action="<?php echo e(URL::to('bodega/lote/guardar')); ?>" autocomplete="off">
        <?php echo e(csrf_field()); ?>

        <input type="hidden"  name="id" value="-1" >

      <div class="card">
        <div class="card-header">
          <h4>Ingresar producto a bodega</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- codigo lote -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Código de lote (Autogenerado)</label>
                 <input type="text" readonly class="form-control" id="codigo_lote" name="codigo_lote" placeholder="###" value="<?php echo e($data['datos']['codigo_lote'] ??""); ?>"  >
              </div>
            </div>
             <!-- Producto -->
             <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Producto</label>
                <select class="form-control" id="producto" name="producto">
                 <?php $__currentLoopData = $data['productos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($i->id); ?>" 
                   
                    ><?php echo e($i->nombre); ?></option>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>
            <!-- cantidad -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Cantidad</label>
                 <input type="number" class="form-control" id="cantidad" name="cantidad" value="<?php echo e($data['datos']['cantidad'] ??""); ?>" required  min="1">
              </div>
            </div>
            <!-- Fecha Vencimiento  -->
           
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Fecha Vencimiento </label>
              <input type="date" id="vencimiento" name="vencimiento" min='<?php echo e(date('Y-m-d')); ?>' class="form-control" required>
              </div>
            </div>
             <!-- bodega -->
             <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Bodega</label>
                <select class="form-control" id="bodega" name="bodega">
                 <?php $__currentLoopData = $data['bodegas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($i->id); ?>" 
                   
                    ><?php echo e($i->descripcion); ?></option>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
              <div class="form-group mb-0">
                  <label>Detalle</label>
                  <textarea class="form-control" name="detalle" maxlength="300"><?php echo e($data['datos']['detalle'] ??""); ?></textarea>
                </div>
            </div>
            <!-- generar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Ingresar inventario</label>
                <input type="submit" class="btn btn-primary form-control" value="Ingresar">
              </div>
            </div>

          </div>
          
         
        </div>
      </div>
    </form>
        
    </section>
    
  </div>

  
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
 
  <script src="<?php echo e(asset("assets/bundles/jquery-ui/jquery-ui.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/bodega/productos.js")); ?>"></script>

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/bodega/lote/nuevoLote.blade.php ENDPATH**/ ?>