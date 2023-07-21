<?php $__env->startSection('style'); ?>
  
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <form method="POST" action="<?php echo e(URL::to('productoExterno/producto/guardar')); ?>"   autocomplete="off">
        <?php echo e(csrf_field()); ?>

        <input type="hidden"  name="id" value="<?php echo e($data['producto']->id ??""); ?>" >

      <div class="card">
        <div class="card-header">
          <h4>Editar Producto Externo</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Código -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Código</label>
                 <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo e($data['producto']->codigo_barra ??""); ?>" required maxlength="15">
              </div>
            </div>
            <!-- descripción -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Nombre </label>
                <input type="text" class="form-control" id="nombre" name="nombre"  value="<?php echo e($data['producto']->nombre ??""); ?>" required maxlength="50">
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group mb-0">
                  <label>Descripción</label>
                  <textarea class="form-control" name="descripcion" id="detalle_movimiento_generado"
                      maxlength="400"><?php echo e($data['producto']->descripcion ?? ''); ?></textarea>
              </div>
          </div>
            
            <!-- categoria -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Categoría</label>
                <select class="form-control" id="categoria" name="categoria">
                 <?php $__currentLoopData = $data['categorias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($i->id); ?>" 
                    <?php if($i->id == ($data['producto']->categoria ?? -1)): ?>
                        selected
                    <?php endif; ?>
                    ><?php echo e($i->categoria); ?></option>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>
            <!-- precio -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Precio CRC</label>
                <input type="number" class="form-control" id="precio" name="precio" step="any" value="<?php echo e($data['producto']->precio ??""); ?>" required min="0">
              </div>
            </div>

            <!-- precio -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Precio Compra CRC</label>
                <input type="number" class="form-control" id="precio_compra" name="precio_compra" step="any" value="<?php echo e($data['producto']->precio_compra ??""); ?>" required min="0">
              </div>
            </div>


            <!-- impuesto -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Impuesto</label>
                <select class="form-control" id="impuesto" name="impuesto">
                  <?php $__currentLoopData = $data['impuestos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($i->id); ?>"
                    <?php if($i->id == ($data['producto']->impuesto ?? -1)): ?>
                        selected
                    <?php endif; ?>
                    ><?php echo e($i->descripcion); ?></option>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>

            <!-- impuesto -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Proveedor</label>
                <select class="form-control" id="proveedor" name="proveedor">
                  <?php $__currentLoopData = $data['proveedores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($i->id); ?>"
                    <?php if($i->id == ($data['producto']->proveedor ?? -1)): ?>
                        selected
                    <?php endif; ?>
                    ><?php echo e($i->nombre); ?></option>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>

          
            <!-- enviar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Guardar producto</label>
                <input type="submit" class="btn btn-primary form-control" value="Guardar">
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
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/productoExterno/editar.blade.php ENDPATH**/ ?>