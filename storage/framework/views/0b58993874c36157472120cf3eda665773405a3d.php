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
          <h4>Roles</h4>
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
                <a class="btn btn-primary" title="Agregar Rol" style="color:white;" onclick="nuevoGenerico()">+ Agregar</a>
              </div>
            </div>
            
         
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="">
                <thead>
                  <tr>
                    
                    <th class="space-align-center">Código</th>
                    <th class="space-align-center">Rol</th>
                    <th class="space-align-center">Tipo Gasto</th>
                    <th class="space-align-center">Tipo Ingreso</th>
                    <th class="space-align-center">Acciones</th>
                   
                  </tr>
                </thead>
                <tbody id="tbody_generico">
                  <?php $__currentLoopData = $data['roles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                  
                  <td class="space-align-center">
                    <?php echo e($g->codigo ?? ""); ?>

                  </td>
                  <td class="space-align-center">
                    <?php echo e($g->rol ?? ""); ?> 
                  </td>
                  <td class="space-align-center">
                    <?php echo e($g->tipo_gasto ?? ""); ?> 
                  </td>
                  <td class="space-align-center">
                    <?php echo e($g->tipo_ingreso ?? ""); ?> 
                  </td>
                  <td class="space-align-center" >
                    <a onclick='editarGenerico("<?php echo e($g->id); ?>","<?php echo e($g->codigo ?? ""); ?>","<?php echo e($g->rol ?? ""); ?>","<?php echo e($g->tipo_gasto_id ?? ""); ?>","<?php echo e($g->tipo_ingreso_id ?? ""); ?>","<?php echo e($g->administrador ?? "N"); ?>","<?php echo e($g->cierra_caja ?? "N"); ?>")'  title="Editar" class="btn btn-primary" style="color:white"><i class="fas fa-cog"></i></a> 
                    <a onclick="eliminarGenerico(<?php echo e($g->id); ?>)" title="Eliminar" class="btn btn-danger" style="color:white"> <i class="fa fa-trash"></i></a>
                  </td>
                </tr>
    
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              
              </tbody>
            </table>
          </div>
          </div>
          <form id="frmEliminarGenerico" action="<?php echo e(URL::to('eliminarrol')); ?>"  style="display: none" method="POST" >
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idGenericoEliminar" id="idGenericoEliminar" value="">
          </form>
        </div>
      </div>
    
    </div>
  </section>

 
    
  </div>

  
   <!-- modal modal de agregar proveedor -->
<div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form id="formRoles" action="<?php echo e(URL::to('guardarrol')); ?>"  autocomplete="off" method="POST" >
            <?php echo e(csrf_field()); ?>

            <input type="hidden" id="mdl_generico_ipt_id" name="mdl_generico_ipt_id" value="-1">
            <div class="modal-header">
              
              <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
                <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Rol</h5>
                <button type="button" id='btnSalirFact' class="close" aria-hidden="true"  onclick="cerrarModalGenerico()">x</button>
            </div>
            <div class="modal-body">
              <div class="row">
                  <div class="col-xl-12 col-sm-12">
                    <div class="form-group form-float">
                      <div class="form-line">
                        <label class="form-label">Código de Rol</label>
                        <input type="text" class="form-control space_input_modal" id="mdl_generico_ipt_codigo" name="mdl_generico_ipt_codigo" required maxlength="50">
                      </div>
                  </div>
                  </div>
                  <div class="col-xl-12 col-sm-12">
                      <div class="form-group form-float">
                          <div class="form-line">
                          <label class="form-label">Rol</label>
                          <input type="text" class="form-control space_input_modal"  id="mdl_generico_ipt_rol" name="mdl_generico_ipt_rol" required maxlength="15">
                          </div>
                      </div>
                  </div>
                  <div class="col-12 col-md-6 col-lg-12">
                    <div class="form-group">
                        <label>Tipo de gasto</label>
                        <select id="mdl_generico_slc_tipo_gasto" class="form-control" name="tipo_gasto">
                          <?php $__currentLoopData = $data['tipos_gasto']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->tipo ?? ''); ?>" ><?php echo e($i->tipo ?? ''); ?></option>
                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                        </select>
                      </div>
                </div>

                <div class="col-12 col-md-6 col-lg-12">
                  <div class="form-group">
                      <label>Tipo de ingreso</label>
                      <select id="mdl_generico_slc_tipo_ingreso" class="form-control" name="tipos_ingreso">
                        <?php $__currentLoopData = $data['tipos_ingreso']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->tipo ?? ''); ?>" ><?php echo e($i->tipo ?? ''); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                          
                      </select>
                    </div>
              </div>

              <div class="col-12 col-md-6 col-lg-12">
                <div class="form-group">
                  <div class="section-title">Privilegios Administrador</div>
                  <div class="pretty p-default p-curve p-thick">
                    <input type="checkbox" name="administrador" id="administrador"  value="S"/>
                      <div class="state p-warning">
                        <label>Sí</label>
                      </div>
                    </div>
                </div>
              </div>

              <div class="col-12 col-md-12 col-lg-12">
                <div class="form-group">
                  <div class="section-title">Cierra Caja</div>
                  <div class="pretty p-default p-curve p-thick">
                    <input type="checkbox" name="cierra_caja" id="cierra_caja"  value="S"/>
                      <div class="state p-warning">
                        <label>Sí</label>
                      </div>
                    </div>
                    <br>
                    <small>Los gastos podran ser procesados únicamente cuando la caja este cerrada.</small>

                </div>
                
              </div>
   

              <div id="cont_permisos_roles" class="col-12 col-md-6 col-lg-12">
                
                  <label>Permisos</label>
                  <?php $__currentLoopData = $data['vistas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <div class="card-body">
                    <div class="section-title"><?php echo e($i->titulo ?? ''); ?></div>
                      <?php $__currentLoopData = $i->submenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="pretty p-default p-curve p-thick">
                        <input type="checkbox" name="menus[]" value="<?php echo e($m->id); ?>"/>
                          <div class="state p-warning">
                            <label><?php echo e($m->titulo ?? ''); ?></label>
                          </div>
                        </div>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
  <script src="<?php echo e(asset("assets/bundles/sweetalert/sweetalert.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/datatables/datatables.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/bundles/jquery-ui/jquery-ui.min.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/page/datatables.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/mant_roles.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/mant/roles.blade.php ENDPATH**/ ?>