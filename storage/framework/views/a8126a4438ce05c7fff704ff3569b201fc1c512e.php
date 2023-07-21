<?php $__env->startSection('style'); ?>
  <link rel="stylesheet" href="<?php echo e(asset("assets/bundles/datatables/datatables.min.css")); ?>">
  <link rel="stylesheet" href="<?php echo e(asset("assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css")); ?>">
  <link rel="stylesheet" href="<?php echo e(asset("assets/bundles/izitoast/css/iziToast.min.css")); ?>">

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
  <section class="section">
    <div class="section-body">
      <div class="card card-warning">
        <div class="card-header">
          <h4>Usuarios</h4>
          <form class="card-header-form">
            <div class="input-group">
              <input type="text" name="" id="input_buscar_generico" class="form-control" placeholder="Buscar..">
              <div class="input-group-btn">
                <a class="btn btn-primary btn-icon" style="cursor: pointer;" onclick="$('#input_buscar_generico').trigger('change');"><i class="fas fa-search"></i></a>
              </div>
            </div>
          </form>
        </div>
        <div class="card-body">
        
          <div class="row" style="width: 100%">
            <div class="col-sm-12 col-md-2">
              <div class="form-group">
                <a class="btn btn-primary" title="Agregar Tipo Gasto" style="color:white;cursor:pointer;" href="<?php echo e(url('usuario/nuevo')); ?>">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                <thead>
                
                  <tr>
                  
                    <th>Usuario</th>
                    <th class="text-center">
                      Nombre 
                    </th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Sucursal</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tbody_generico">
                    <?php $__currentLoopData = $data['usuarios']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    
                    <td><?php echo e($g->usuario ?? ""); ?></td>
                    <td>
                      <?php echo e($g->nombre); ?> <?php echo e($g->ape1 ?? ""); ?> <?php echo e($g->ape2 ?? ""); ?>  
                    </td>
                    <td class="align-middle">
                        <?php echo e($g->correo ?? ''); ?>

                    </td>
                    <td>
                      +506  <?php echo e($g->telefono ?? ""); ?>

                    </td>
                    <td><?php echo e($g->sucursal_nombre ?? ""); ?></td>
                    <td>
                  
                    <?php if($g->rol == "1"): ?>
                      <div class="badge badge-info badge-shadow">
                    <?php endif; ?> 
                    <?php if($g->rol == "2"): ?>
                      <div class="badge badge-warning badge-shadow">
                    <?php endif; ?> 
                    <?php if($g->rol == "3"): ?>
                      <div class="badge badge-success badge-shadow">
                    <?php endif; ?> 
                    <?php if($g->rol == "4"): ?>
                      <div class="badge badge-secondary badge-shadow">
                    <?php endif; ?> 
                    <?php if($g->rol == "5"): ?>
                      <div class="badge badge-danger badge-shadow">
                    <?php endif; ?> 
                    <?php if($g->rol > "5"): ?>
                      <div class="badge badge-primary badge-shadow">
                    <?php endif; ?> 
                    <?php echo e($g->rol_nombre); ?></div></td>
                    <td class="space-align-center" >
                    <a onclick="editarUsuario(<?php echo e($g->id); ?>)" title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
                      <a onclick="eliminarGenerico(<?php echo e($g->id); ?>)" title="Eliminar" class="btn btn-danger" style="color:white"> <i class="fa fa-trash"></i></a>
                    </td>
                  </tr>

                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                </tbody>
              </table>
          </div>
          </div>
         
        </div>
      </div>
      <form id="frmEditarUsuario" action="<?php echo e(URL::to('usuario/editar')); ?>"  style="display: none" method="POST" >
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idUsuarioEditar" id="idUsuarioEditar" value="">
      </form>
    
      <form id="frmEliminarGenerico" action="<?php echo e(URL::to('eliminarusuario')); ?>"  style="display: none" method="POST" >
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
      </form>
    </div>
  </section>

  </div>


<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
  <script src="<?php echo e(asset("assets/bundles/sweetalert/sweetalert.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/datatables/datatables.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/jquery-ui/jquery-ui.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/page/datatables.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/mant_usuarios.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/mant/usuarios.blade.php ENDPATH**/ ?>