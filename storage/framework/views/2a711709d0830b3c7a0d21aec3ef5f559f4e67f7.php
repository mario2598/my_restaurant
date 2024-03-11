<?php $__env->startSection('style'); ?>

<link rel="stylesheet" href="<?php echo e(asset("assets/bundles/datatables/datatables.min.css")); ?>">
  <link rel="stylesheet" href="<?php echo e(asset("assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css")); ?>">
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>  

<?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="main-content">
    <section class="section">
      <div class="section-body">
        <div class="card card-warning">
          <div class="card-header">
            <h4>Ingresos</h4>
            <form class="card-header-form">
              <div class="input-group">
                <input type="text" name="" onkeyup="filtrarGastosAdmin(this.value)" id="btn_buscar_gasto" class="form-control" placeholder="Buscar ingreso">
                <div class="input-group-btn">
                  <a class="btn btn-primary btn-icon" style="cursor: pointer;" onclick="filtrarGastosAdmin(btn_buscar_gasto.value)"><i class="fas fa-search"></i></a>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body">
            <form action="<?php echo e(URL::to('ingresos/administracion/filtro')); ?>" method="POST">
            <?php echo e(csrf_field()); ?>

            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Cliente</label>
                  <select class="form-control" id="select_cliente" name="cliente">
                      <option value="0" selected>Todos</option>
                      <?php $__currentLoopData = $data['clientes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->nombre ?? ''); ?>" 
                        <?php if($i->id == $data['filtros']['cliente'] ): ?>
                              selected
                          <?php endif; ?>
                        ><?php echo e($i->nombre ?? ''); ?></option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Sucursal</label>
                  <select class="form-control" id="select_sucursal" name="sucursal">
                      <option value="T" selected>Todos</option>
                      <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($i->id ?? ''); ?>" title="<?php echo e($i->descripcion ?? ''); ?>" 
                          <?php if($i->descripcion == $data['filtros']['sucursal'] ): ?>
                              selected
                          <?php endif; ?>
                          ><?php echo e($i->descripcion ?? ''); ?></option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>

              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Tipo Ingreso</label>
                  <select class="form-control" id="select_tipo_ingreso" name="tipo_ingreso">
                      <option value="T" selected>Todos</option>
                      <?php $__currentLoopData = $data['tipos_ingreso']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($i->id); ?>" title="<?php echo e($i->tipo ?? ''); ?>" 
                          <?php if($i->id == $data['filtros']['tipo_ingreso'] ): ?>
                              selected
                          <?php endif; ?>
                          ><?php echo e($i->tipo ?? ''); ?></option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Estado</label>
                  <select class="form-control" name="aprobado">
                    <option value="T" <?php if ($data['filtros']['aprobado'] == 'T'){ echo 'selected';} ?>>Todos</option>
                    <option value="S" <?php if ($data['filtros']['aprobado'] == 'S'){ echo 'selected';} ?>>Aprobados</option>
                    <option value="R" <?php if ($data['filtros']['aprobado'] == 'R'){ echo 'selected';} ?>>Rechazado</option>
                    <option value="N" <?php if ($data['filtros']['aprobado'] == 'N'){ echo 'selected';} ?>>Sin Aprobar</option>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Desde</label>
                  <input type="date" class="form-control" name="desde" value="<?php echo e($data['filtros']['desde']  ?? ''); ?>"/>
                   
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Hasta</label>
                  <input type="date" class="form-control" name="hasta"  value="<?php echo e($data['filtros']['hasta']  ?? ''); ?>"/>
                </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Buscar</label>
                  <button type="submit" class="btn btn-primary btn-icon form-control" style="cursor: pointer;"><i class="fas fa-search"></i></button>
                </div>
              </div>
           
            </div>
          </form>
            <div id="contenedor_gastos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaIngresos" >
                  <thead>
                  
                    <tr>
                    
                      <th class="text-center">Tipo Ingreso</th>
                      <th class="text-center">
                        Monto  
                      </th>
                      <th class="text-center">
                        Usuario
                      </th>
                      <th class="text-center">Sucursal</th>
                      <th class="text-center">Fecha</th>
                      <th class="text-center">Estado</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    <?php $__currentLoopData = $data['ingresos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr class="space_row_table" style="cursor: pointer;" onclick='clickIngreso("<?php echo e($g->id); ?>")'>
                      
                      <td class="text-center"><?php echo e($g->nombre_tipo_ingreso ?? ""); ?></td>
                      <td class="text-center">
                        CRC <?php echo e(number_format($g->total ?? '0.00',2,".",",")); ?>

                      </td>
                      <td class="text-center">
                        <?php echo e($g->nombreUsuario ?? ''); ?>

                      </td>
                      <td class="text-center">
                        <?php echo e($g->nombreSucursal); ?>  
                      </td>
                      
                     
                      <td class="text-center"><?php echo e($g->fecha ?? ""); ?></td>
                      <td class="text-center">
                    
                      <?php if($g->aprobado == "N"): ?>
                        <div class="badge badge-warning badge-shadow">
                          Sin Aprobar</div></td>
                      <?php endif; ?> 
                      <?php if($g->aprobado == "R"): ?>
                        <div class="badge badge-danger badge-shadow">
                          Rechazado</div></td>
                      <?php endif; ?> 
                      <?php if($g->aprobado == "E"): ?>
                        <div class="badge badge-dark badge-shadow">
                          Eliminado</div></td>
                      <?php endif; ?> 
                      <?php if($g->aprobado == "S"): ?>
                        <div class="badge badge-success badge-shadow">
                          Aprobado</div></td>
                      <?php endif; ?> 
                     
                    </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                  </tbody>
                    <tfoot>
                      <?php if(count($data['ingresos']) > 0): ?>
                    
                      <tr class="space_row_table" >
                        
                        <td class="text-center" style="background: rgb(226, 196, 196);"><strong>Total General</strong></td>
                        <td class="text-center" style="background: rgb(226, 196, 196);">
                          <strong>  CRC <?php echo e(number_format($data['totalIngresos'] ?? '0.00',2,".",",")); ?></strong>
                        </td>
                        <td class="text-center" style="background: rgb(226, 196, 196);">
                          ***
                        </td>
                        <td class="text-center" style="background: rgb(226, 196, 196);">
                          <strong> ***</strong>
                        </td>
                        
                        <td class="text-center" style="background: rgb(226, 196, 196);">
                        
                        <strong>***</strong>
                        </td>
                        <td class="text-center" style="background: rgb(226, 196, 196);">***</td>

                      
                      </tr>

                  
                    <?php endif; ?>
                  </tfoot>
                </table>
              </div> 
            </div>
           
          </div>
        </div>
      
      </div>
    </section>
    
  </div>

  <script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      var tipo_ingreso= $("#select_tipo_ingreso option[value='" +"<?php echo e($data['filtros']['tipo_ingreso']); ?>"+ "']").html();
      var cliente= $("#select_cliente option[value='" +"<?php echo e($data['filtros']['cliente']); ?>"+ "']").html();
      var sucursal= $("#select_sucursal option[value='" +"<?php echo e($data['filtros']['sucursal']); ?>"+ "']").html();
  
      var topMesage = 'Reporte de Ingresos';
      var bottomMesage = 'Reporte general de ingresos filtrado por';
      if("<?php echo e($data['filtros']['aprobado']); ?>" == 'S'){
        topMesage += ' APROBADOS';
      }else if("<?php echo e($data['filtros']['aprobado']); ?>" == 'R'){
        topMesage += ' RECHAZADOS';
      }
      else if("<?php echo e($data['filtros']['aprobado']); ?>" == 'N'){
        topMesage += ' SIN APROBAR';
      }
      if("<?php echo e($data['filtros']['desde']); ?>" != ''){
        topMesage += ' desde el '+"<?php echo e($data['filtros']['desde']); ?>";
      }
      if("<?php echo e($data['filtros']['hasta']); ?>" != ''){
        topMesage += ' hasta el '+"<?php echo e($data['filtros']['hasta']); ?>";
      }
      topMesage += '.'+' Solicitud realizada por '+"<?php echo e(session('usuario')['usuario']); ?>"+'.';
  
      if("<?php echo e($data['filtros']['sucursal']); ?>" != 'T'){
        bottomMesage += ' sucursal [ '+sucursal+' ],';
      }else{
        bottomMesage += ' sucursal [ Todas ],';
      }
  
      if("<?php echo e($data['filtros']['tipo_ingreso']); ?>" != ''){
        bottomMesage += ' tipo de ingreso [ '+tipo_ingreso+' ],';
      }else{
        bottomMesage += ' tipo de ingreso [ Todas ],';
      }
  
      if("<?php echo e($data['filtros']['cliente']); ?>" != 0){
        bottomMesage += ' cliente [ '+cliente+' ].';
      }else{
        bottomMesage += 'cliente [ Todos ]. ';
      }
      bottomMesage += ' Desarrollado por Space Software CR. ';
     
     
      $('#tablaIngresos').DataTable({
        dom: 'Bfrtip',
        "searching": false,
        "paging": false,
        buttons: [
          {
            extend: 'excel',
            title: 'GYM BAR',
            messageTop:topMesage,
            footer: true,
            messageBottom:bottomMesage,
            filename: 'reporte_ingresos_coffee_to_go'
          }, {
            extend: 'pdf',
            title: 'GYM BAR',
            footer: true,
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_ingresos_coffee_to_go'
          }, {
            extend: 'print',
            title: 'GYM BAR',
            footer: true,
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_ingresos_coffee_to_go'
          }
        ]
      });
  
    }
    </script>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset("assets/bundles/datatables/datatables.min.js")); ?>"></script>
<script src="<?php echo e(asset("assets/js/page/datatables.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/gastos_admin.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/CoffeeToGo/resources/views/ingresos/ingresosAdmin.blade.php ENDPATH**/ ?>