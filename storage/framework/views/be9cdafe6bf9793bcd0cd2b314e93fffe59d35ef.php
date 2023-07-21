<?php $__env->startSection('style'); ?>
  
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <form method="POST" action="<?php echo e(URL::to('menu/producto/guardar')); ?>"   autocomplete="off">
        <?php echo e(csrf_field()); ?>

        <input type="hidden"  name="id" value="<?php echo e($data['producto']->id); ?>" >
      <div class="card">
        <div class="card-header">
          <h4>Editar Producto Menú</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Código -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Código</label>
              <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo e($data['producto']->codigo ??""); ?>" required maxlength="15">
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
                    <?php if($i->id == ($data['producto']->categoria ??-1)): ?>
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
                <input type="number" class="form-control" id="precio" name="precio" step=any value="<?php echo e($data['producto']->precio ??""); ?>" required min="0">
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

            <!-- tipo comanda BE : BEBIDA , CO >COCINA -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Tipo comanda</label>
                <select class="form-control" id="tipo_comanda" name="tipo_comanda">
                
                  <option value="BE" 
                    <?php if("BE" == ($data['producto']->tipo_comanda)): ?>
                        selected
                    <?php endif; ?>
                  >GEN BEB</option>
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
            <!-- eliminar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Eliminar producto</label>
              <a  class="btn btn-danger form-control" onclick='eliminarProducto("<?php echo e($data["producto"]->id); ?>")' style="color: white;cursor: pointer;" >Eliminar </a>
              </div>
            </div>

          </div>
          
         
        </div>
      </div>
    </form>
        
    </section>
    
  </div>

  <form id="formEliminarProducto" action="<?php echo e(URL::to('menu/producto/eliminar')); ?>" style="display: none"  method="POST">
    <?php echo e(csrf_field()); ?>

    <input type="hidden" name="idProductoEliminar" id="idProductoEliminar" value="-1">
  </form>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
 
  <script src="<?php echo e(asset("assets/bundles/jquery-ui/jquery-ui.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/bodega/productos.js")); ?>"></script>

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/productosMenu/producto/editarProducto.blade.php ENDPATH**/ ?>