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
            <h4>Productos Materia Prima</h4>
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
           
            <div class="row" style="width: 100%">
              <div class="col-sm-12 col-md-4">
                <div class="form-group">
                  <label>Nuevo</label>
                <a href="<?php echo e(url('materiaPrima/productos/nuevo')); ?>" class="btn btn-success btn-icon form-control" style="cursor: pointer;color:white;"><i class="fas fa-plus"></i> Agregar</a>
                </div>
              </div>
              
            </div>
         
            <div id="contenedor_productos" class="row">
              <div class="table-responsive">
                <table class="table table-striped" id="tablaProductos" >
                  <thead>
                    
                  
                    <tr>
                     
                      <th class="text-center">Producto</th>
                      <th class="text-center">
                        Unidad Medida 
                      </th>
                      <th class="text-center">Precio</th>
                      
                    </tr>
                  </thead>
                  <tbody id="tbody_generico">
                    
                  <?php $__currentLoopData = $data['productos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr class="space_row_table" style="cursor: pointer;" onclick='clickProducto("<?php echo e($g->id); ?>")'>
                      
                      <td class="text-center">
                        <?php echo e($g->nombre); ?>  
                      </td>
                      <td class="text-center">
                        <?php echo e($g->unidad_medida ?? ''); ?>

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
    <form id="formEditarProducto" action="<?php echo e(URL::to('materiaPrima/producto/editar')); ?>" style="display: none"  method="POST">
      <?php echo e(csrf_field()); ?>

      <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
    </form>
  </div>
  
  <script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      var categoria= $("#select_categoria option[value='" +"<?php echo e($data['filtros']['categoria']); ?>"+ "']").html();
      var impuesto= $("#select_impuesto option[value='" +"<?php echo e($data['filtros']['impuesto']); ?>"+ "']").html();

      var topMesage = 'Reporte de Productos del Menú';
      var bottomMesage = 'Reporte de productos del Menú filtrado por';
    
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

      bottomMesage += ' Coffee To Go CR. ';
     
     
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
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_coffee'
          }, {
            extend: 'pdf',
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_coffee'
          }, {
            extend: 'print',
            title: 'Coffee To Go',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_productos_coffee_to_coffee'
          }
        ]
      });

    }
    </script>


<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset("assets/bundles/datatables/datatables.min.js")); ?>"></script>
<script src="<?php echo e(asset("assets/js/page/datatables.js")); ?>"></script>
  <script src="<?php echo e(asset("assets/js/materiaPrima/productos.js")); ?>"></script>
  

     
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/materiaPrima/productos.blade.php ENDPATH**/ ?>