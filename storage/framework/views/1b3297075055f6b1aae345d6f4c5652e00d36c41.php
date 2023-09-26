<?php $__env->startSection('style'); ?>


<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="card card-warning">
          <div class="card-header">
            <h4>Categorías</h4>
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
                  <a class="btn btn-primary" title="Agregar Categoría" style="color:white;" onclick="nuevoGenerico()">+ Agregar</a>
                </div>
              </div>
              
           
            </div>
            <div id="contenedor_gastos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="">
                  <thead>
                    <tr>
                      
                      <th class="space-align-center">#Id</th>
                      <th class="space-align-center">Código</th>
                      <th class="space-align-center">Categoría</th>
                      <th class="space-align-center">Acciones</th>
                     
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    <?php $__currentLoopData = $data['categorias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    
                    <td class="space-align-center">
                      <?php echo e($g->id ?? "###"); ?>

                    </td>

                    <td class="space-align-center">
                      <?php echo e($g->codigo ?? "###"); ?>

                    </td>

                    <td class="space-align-center">
                      <?php echo e($g->categoria ?? ""); ?>

                    </td>
                    
                    <td class="space-align-center" >
                      <a onclick='editarGenerico("<?php echo e($g->id); ?>","<?php echo e($g->categoria ?? ""); ?>","<?php echo e($g->codigo ?? ""); ?>")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
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
      
      </div>
    </section>
    <form id="frmEliminarGenerico" action="<?php echo e(URL::to('eliminarcategoria')); ?>"  style="display: none" method="POST" >
      <?php echo e(csrf_field()); ?>

      <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
    </form>
    
  </div>

 <!-- modal modal de agregar proveedor -->
 <div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
 aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
           <form action="<?php echo e(URL::to('guardarcategoria')); ?>"  autocomplete="off" method="POST" >
             <?php echo e(csrf_field()); ?>

             <input type="hidden" id="mdl_generico_ipt_id" name="mdl_generico_ipt_id" value="-1">
             <div class="modal-header">
               
               <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                 <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Categoría</h5>
                 <button type="button" id='btnSalirFact' class="close" aria-hidden="true"  onclick="cerrarModalGenerico()">x</button>
             </div>
             <div class="modal-body">
               <div class="row">
                  <div class="col-xl-12 col-sm-12">
                    <div class="form-group form-float">
                      <div class="form-line">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control space_input_modal" id="mdl_generico_ipt_codigo" name="mdl_generico_ipt_codigo" required maxlength="9">
                    
                      </div>
                  </div>
                  </div>
                   <div class="col-xl-12 col-sm-12">
                     <div class="form-group form-float">
                       <div class="form-line">
                         <label class="form-label">Categoría</label>
                         <input type="text" class="form-control space_input_modal" id="mdl_generico_ipt_categoria" name="mdl_generico_ipt_categoria" required maxlength="30">
                      
                       </div>
                   </div>
                   </div>

               </div>
 
             </div>
             <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
               <a href="#" class="btn btn-secondary" onclick="cerrarModalGenerico()">Volver</a>
               <input type="submit" class="btn btn-primary" value="Guardar"/>
                      
             </div>
           </form>
         </div><!-- /.modal-content -->
     </div><!-- /.modal-dialog -->
 </div><!-- /.modal -- fin modal de agregar sucursal-->
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>

  <script src="<?php echo e(asset("assets/js/categoria.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/CoffeeToGo/resources/views/mant/categoria.blade.php ENDPATH**/ ?>