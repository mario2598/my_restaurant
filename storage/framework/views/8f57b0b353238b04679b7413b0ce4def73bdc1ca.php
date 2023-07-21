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
            <h4>Productos Externos</h4>
            <form class="card-header-form">
              <div class="input-group">
                <input type="text" name=""  id="btn_buscar_pro" class="form-control" placeholder="Buscar producto">
                <div class="input-group-btn">
                  <a class="btn btn-primary btn-icon" style="cursor: pointer;"><i class="fas fa-search"></i></a>
                </div>
              </div>
            </form>
          </div>
          
          <div class="card-body">
            <form action="<?php echo e(URL::to('productoExterno/productos/filtro')); ?>" method="POST">
            <?php echo e(csrf_field()); ?>

            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Categoría</label>
                  <select class="form-control" id="select_categoria" name="categoria">
                      <option  value="T" selected>Todos</option>
                      <?php $__currentLoopData = $data['categorias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($i->id ?? -1); ?>" title="<?php echo e($i->categoria ?? ''); ?>" 
                        <?php if($i->id == $data['filtros']['categoria'] ): ?>
                              selected
                          <?php endif; ?>
                        ><?php echo e($i->categoria ?? ''); ?></option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-3">
                <div class="form-group">
                  <label>Tipo Impuesto</label>
                  <select class="form-control" id="select_impuesto" name="impuesto">
                      <option value="T" selected>Todos</option>
                      <?php $__currentLoopData = $data['impuestos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($i->id); ?>" title="<?php echo e($i->descripcion ?? ''); ?>" 
                          <?php if($i->id == $data['filtros']['impuesto'] ): ?>
                              selected
                          <?php endif; ?>
                          ><?php echo e($i->descripcion ?? ''); ?></option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Proveedor</label>
                  <select class="form-control" id="proveedor" name="proveedor">
                      <option value="T" selected>Todos</option>
                      <?php $__currentLoopData = $data['proveedores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($i->id); ?>" title="<?php echo e($i->descripcion ?? ''); ?>" 
                          <?php if($i->id == $data['filtros']['proveedor'] ): ?>
                              selected
                          <?php endif; ?>
                          ><?php echo e($i->nombre ?? ''); ?></option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Buscar</label>
                  <button type="submit" class="btn btn-primary btn-icon form-control" style="cursor: pointer;"><i class="fas fa-search"></i></button>
                </div>
              </div>
              <div class="col-sm-12 col-md-2">
                <div class="form-group">
                  <label>Nuevo</label>
                <a href="<?php echo e(url('productoExterno/nuevo')); ?>" class="btn btn-success btn-icon form-control" style="cursor: pointer;color:white;"><i class="fas fa-plus"></i> Agregar</a>
                </div>

              </div>
              
            </div>
          </form>
            <div id="contenedor_productos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaProductos" >
                  <thead>
                    
                  
                    <tr>
                      <th class="text-center">Código</th>
                    
                      <th class="text-center">Producto</th>
                      <th class="text-center">
                        Categoría 
                      </th>
                      <th class="text-center">
                        Impuestos % 
                      </th>
                      <th class="text-center">Precio</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    
                  <?php $__currentLoopData = $data['productos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr class="space_row_table" style="cursor: pointer;" onclick='clickProducto("<?php echo e($g->id); ?>")'>
                      
                      <td class="text-center"><?php echo e($g->codigo_barra ?? ""); ?></td>
                      <td class="text-center">
                        <?php echo e($g->nombre); ?>  
                      </td>
                      <td class="text-center">
                        <?php echo e($g->nombre_categoria ?? ''); ?>

                      </td>
                      <td class="text-center">
                          <?php echo e($g->porcentaje_impuesto ?? '0'); ?> %
                      </td>
                      
                      <td class="text-center">
                        CRC <?php echo e(number_format($g->precio ?? '0.00',2,".",",")); ?>

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
    <form id="formEditarProducto" action="<?php echo e(URL::to('productoExterno/editar')); ?>" style="display: none"  method="POST">
      <?php echo e(csrf_field()); ?>

      <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
    </form>
  </div>
  
  <script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      var categoria= $("#select_categoria option[value='" +"<?php echo e($data['filtros']['categoria']); ?>"+ "']").html();
      var impuesto= $("#select_impuesto option[value='" +"<?php echo e($data['filtros']['impuesto']); ?>"+ "']").html();

      var topMesage = 'Reporte de Productos Externos';
      var bottomMesage = 'Reporte de productos Externos filtrado por';
    
      topMesage += '.'+' Solicitud realizada por '+"<?php echo e(session('usuario')['usuario']); ?>"+'.';

      if("<?php echo e($data['filtros']['categoria']); ?>" != 'T'){
        bottomMesage += ' categoria [ '+categoria+' ],';
      }else{
        bottomMesage += ' categoria [ Todas ],';
      }

      if("<?php echo e($data['filtros']['impuesto']); ?>" != 'T'){
        bottomMesage += ' tipo de impuesto [ '+impuesto+' ],';
      }else{
        bottomMesage += 'tipo de impuesto [ Todos ].';
      }

      bottomMesage += ' Desarrollado por Space Software CR. ';
     
     
      $('#tablaProductos').DataTable({
        dom: 'Bfrtip',
        "searching": false,
        "paging": false,
        'fixedHeader': {
    'header': true,
    'footer': true
  },
        buttons: [
          {
            extend: 'excel',
            title: 'COFFEE TO GO',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_go'
          }, {
            extend: 'pdf',
            title: 'COFFEE TO GO',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_go'
          }, {
            extend: 'print',
            title: 'COFFEE TO GO',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_go'
          }
        ]
      });

    }
    </script>


<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset("assets/bundles/datatables/datatables.min.js")); ?>"></script>
<script src="<?php echo e(asset("assets/js/page/datatables.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/productoExterno/productos.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/productoExterno/productos.blade.php ENDPATH**/ ?>