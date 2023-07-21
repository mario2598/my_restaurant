<?php $__env->startSection('style'); ?>
  
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <form method="POST" action="<?php echo e(URL::to('usuario/guardarusuario')); ?>"   autocomplete="off">
        <?php echo e(csrf_field()); ?>

      <div class="card">
        <div class="card-header">
          <h4>Nuevo Usuario</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- nombre -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Nombre</label>
              <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo e($data['datos']['nombre'] ??""); ?>" required maxlength="25">
              </div>
            </div>
            <!-- ape1 -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Primer Apellido </label>
                <input type="text" class="form-control" id="ape1" name="ape1"  value="<?php echo e($data['datos']['ape1'] ??""); ?>" required maxlength="25">
              </div>
            </div>
            <!-- ape2 -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Segundo Apellido (Opcional)</label>
                <input type="text" class="form-control" id="ape2" name="ape2" value="<?php echo e($data['datos']['ape2'] ??""); ?>" maxlength="25">
              </div>
            </div>
            <!-- cedula -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Cédula</label>
                <input type="text" class="form-control" id="cedula" name="cedula" value="<?php echo e($data['datos']['cedula'] ??""); ?>" required maxlength="15">
              </div>
            </div>
            <!-- nacimiento -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Fecha Nacimiento (Opcional)</label>
                <input type="date" id="nacimiento" name="nacimiento" value="<?php echo e($data['datos']['nacimiento'] ??""); ?>" class="form-control">
              </div>
              
            </div>

            <!-- telefono -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Teléfono (+506)</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="fas fa-phone"></i>
                    </div>
                  </div>
                  <input type="number" class="form-control phone-number" id="telefono" name="telefono" value="<?php echo e($data['datos']['telefono'] ??""); ?>" maxlength="8">
                </div>
              </div>

            </div>

            <!-- usuario -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Usuario</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="fas fa-user"></i>
                    </div>
                  </div>
                  <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo e($data['datos']['usuario'] ??""); ?>" onfocus="this.removeAttribute('readonly');" autocomplete="off" required maxlength="25">
                </div>
              </div>
            </div>

            <!-- contraseña -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>* Contraseña </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="fas fa-lock"></i>
                    </div>
                  </div>
                  <input type="password" class="form-control " id="contra" name="contra" value="<?php echo e($data['datos']['contra'] ??""); ?>" autocomplete="off" required maxlength="25" minlength="4">
                </div>
                
              </div>
            </div>

            <!-- correo -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Correo (Opcional)</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text">
                      <i class="fas fa-envelope"></i>
                    </div>
                  </div>
                  <input type="email" class="form-control " id="correo" name="correo" value="<?php echo e($data['datos']['correo'] ??""); ?>" maxlength="100">
                </div>
                
              </div>
            </div>

            <!-- rol -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Rol</label>
                <select class="form-control" id="rol" name="rol">
                  <?php $__currentLoopData = $data['roles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($i->id); ?>"
                    <?php if($i->id == ($data['datos']['rol'] ?? -1)): ?>
                        selected
                    <?php endif; ?>
                    ><?php echo e($i->rol); ?></option>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>

            <!-- sucursal -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Sucursal</label>
                <select class="form-control" id="sucursal" name="sucursal">
                 <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($i->id); ?>" 
                    <?php if($i->id == ($data['datos']['sucursal'] ?? -1)): ?>
                        selected
                    <?php endif; ?>
                    ><?php echo e($i->descripcion); ?></option>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>
          
            <!-- enviar -->
            <div class="col-sm-12 col-md-6 col-xl-4">
              <div class="form-group">
                <label>Guardar usuario</label>
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
  <script src="<?php echo e(asset("assets/js/mant_clientes.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/usuario/nuevoUsuario.blade.php ENDPATH**/ ?>