<?php $__env->startSection('styles'); ?>

<link rel="stylesheet" href="<?php echo e(asset("assets/bundles/pretty-checkbox/pretty-checkbox.min.css")); ?>">

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
  <section class="section">
    <div class="section-body">
      <div class="card card-warning">
        <div class="card-header">
          <h4>Sucursales</h4>
          <form class="card-header-form">
            <div class="input-group">
              <input type="text" name="" id="input_buscar_sucursal" class="form-control" placeholder="Buscar..">
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
                <a class="btn btn-primary" title="Agregar Sucursal" style="color:white;" onclick="nuevaSucursal()">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                <thead>
                  <tr>
                    
                    <th class="space-align-center">Código</th>
                    <th class="space-align-center">Sucursal</th>
                    <th class="space-align-center">Acciones</th>
                  
                  </tr>
                </thead>
                <tbody id="tbody_sucursal">
                  <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                  
                  <td class="space-align-center"><?php echo e($s->id); ?></td>
                  <td class="space-align-center">
                    <?php echo e($s->descripcion); ?>

                  </td>
                  
                  <td class="space-align-center" >
                    <a onclick='editarSucursal("<?php echo e($s->id); ?>","<?php echo e($s->descripcion); ?>")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
                    <a onclick="eliminarSucursal(<?php echo e($s->id); ?>)" title="Eliminar" class="btn btn-danger" style="color:white"> <i class="fa fa-trash"></i></a>
                  </td>
                </tr>
    
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              
              </tbody>
            </table>
          </div>
          </div>
         
        </div>
      </div>
      <form id="frmEliminarSucursal" action="<?php echo e(URL::to('eliminarsucursal')); ?>"  style="display: none" method="POST" >
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idSucursalEliminar" id="idSucursalEliminar" value="">
      </form>
    </div>
  </section>

  </div>

  
   <!-- modal modal de agregar sucursal -->
<div class="modal fade bs-example-modal-center" id='mdl_sucursal' tabindex="-1" role="dialog"
aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="<?php echo e(URL::to('guardarsucursal')); ?>"  autocomplete="off" method="POST" >
            <?php echo e(csrf_field()); ?>

            <div class="modal-header">
              
              <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Sucursal</h5>
                <button type="button" class="close" aria-hidden="true"  onclick="cerrarModalSucursal()">x</button>
            </div>
            <div class="modal-body">
              <div class="row">
                  <div class="col-xl-12 col-sm-12">
                      <div class="form-group form-float">
                          <div class="form-line">
                          <label class="form-label">Descripción (Lugar)</label>
                          <input type="text" class="form-control space_input_modal" required maxlength="50" id="mdl_sucursal_ipt_descripcion" name="mdl_sucursal_ipt_descripcion" >
                          <input type="hidden" id="mdl_sucursal_ipt_id" name="mdl_sucursal_ipt_id" value="-1">

                          </div>
                      </div>
                  </div>
                  
              </div>

            </div>
            <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
              <a href="#" class="btn btn-secondary" onclick="cerrarModalSucursal()">Volver</a>
              <input type="submit" class="btn btn-primary" value="Guardar"/>
                     
            </div>
          </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -- fin modal de agregar sucursal-->
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
  <script src="<?php echo e(asset("assets/bundles/sweetalert/sweetalert.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/datatables/datatables.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/jquery-ui/jquery-ui.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/page/datatables.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/mant_sucursales.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/mant/sucursales.blade.php ENDPATH**/ ?>