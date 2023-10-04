<?php $__env->startSection('style'); ?>

    <link rel="stylesheet" href="<?php echo e(asset('assets/bundles/datatables/datatables.min.css')); ?>">
    <link rel="stylesheet"
        href="<?php echo e(asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css')); ?>">
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <section class="section">
            <div class="section-body">

                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Inventario productos externos</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="btn_buscar_pro" class="form-control"
                                    placeholder="Buscar producto">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon" style="cursor: pointer;"><i
                                            class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <form action="<?php echo e(URL::to('productoExterno/inventario/inventarios/filtro')); ?>" method="POST">
                            <?php echo e(csrf_field()); ?>

                            <div class="row" style="width: 100%">
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Sucursal</label>
                                        <select class="form-control" id="sucursal" name="sucursal" required>
                                            <option value="-1" selected>Seleccione una sucursal</option>
                                            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($i->id ?? ''); ?>" title="<?php echo e($i->descripcion ?? ''); ?>"
                                                    <?php if($i->id == $data['filtros']['sucursal']): ?> selected <?php endif; ?>><?php echo e($i->descripcion ?? ''); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Buscar</label>
                                        <button type="submit" class="btn btn-primary btn-icon form-control"
                                            style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Agregar</label>
                                        <button type="button" class="btn btn-secondary btn-icon form-control"
                                            style="cursor: pointer;" onclick="abrirAgregarProducto()"><i
                                                class="fas fa-plus"></i> Agregar producto</button>
                                    </div>
                                </div>


                            </div>
                        </form>
                        <div id="contenedor_productos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaInventarios">
                                    <thead>


                                        <tr>
                                            <th class="text-center">Código</th>

                                            <th class="text-center">Producto</th>
                                            <th class="text-center">
                                                Categoría
                                            </th>
                                            <th class="text-center">
                                                Cantidad
                                            </th>


                                        </tr>
                                    </thead>
                                    <tbody id="tbody_generico">
                                        <?php $__currentLoopData = $data['inventarios']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr style="cursor: pointer" onclick='editarProductoInventario("<?php echo e($i->pe_id); ?>","<?php echo e($i->id); ?>","<?php echo e($i->cantidad); ?>")'>
                                                <td class="text-center">
                                                    <?php echo e(strtoupper($i->codigo_barra ?? '')); ?>

                                                </td>
                                                <td class="text-center">
                                                    <?php echo e($i->nombre ?? ''); ?>

                                                </td>
                                                <td class="text-center">
                                                    <?php echo e($i->categoria ?? ''); ?>

                                                </td>
                                                <td class="text-center">
                                                    <?php echo e($i->cantidad ?? ''); ?>

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
        <form id="formEditarProducto" action="<?php echo e(URL::to('bodega/producto/editar')); ?>" style="display: none"
            method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idProductoEditar" id="idProductoEditar" value="-1">
        </form>
    </div>

    <!-- modal modal de agregar producto -->
    <div class="modal fade bs-example-modal-center" id='mdl_agregar_producto' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                    <input type="hidden" id="pe_id" name="pe_id" value="-1">
                    <input type="hidden" name="sucursal_agregar_id" id="sucursal_agregar_id" value="-1">
                    <div class="modal-header">

                        <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                        </div>
                        <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Producto menú</h5>
                        <button type="button" id='btnSalirFact' class="close" aria-hidden="true"
                            data-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-12 col-sm-8">
                                <div class="form-group">
                                    <label>Prodcuto</label>
                                    <select class="form-control" id="producto_externo" name="producto_externo" required>
                                        
                                        <?php $__currentLoopData = $data['productos_externos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($i->id ?? ''); ?>" title="<?php echo e($i->codigo_barra ?? ''); ?>">
                                                <?php echo e($i->nombre ?? ''); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-12 col-sm-4">
                                <div class="form-group">
                                    <label>Busqueda</label>
                                    <a class="btn btn-secondary" onclick="abrirProductosExternos()"
                                        style="cursor: pointer">Buscar producto</a>
                                </div>
                            </div>
                            <div class="col-xl-12 col-sm-12">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" class="form-control space_input_modal" id="cantidad_agregar"
                                            name="cantidad_agregar" required max="10000" min="1">

                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div id='footerContiner' class="modal-footer" >
                        <a href="#" class="btn btn-secondary" data-dismiss="modal">Volver</a>
                        <input type="button" class="btn btn-primary" onclick="guardarProductoSucursal()" value="Guardar" />

                    </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal de agregar producto-->
    <!--ayuda prodcutos-->
    <div class="modal fade bs-example-modal-center" id='mdl_ayuda_producto' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Producto menú</h5>
                    <button type="button" id='btnSalirFact' class="close" aria-hidden="true" data-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <input type="text" name="" id="btn_buscar_producto_ayuda" class="form-control"
                            placeholder="Buscar producto">
    
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaProductos">
                            <thead>
                                <tr>
                                    <th class="text-center">Código</th>

                                    <th class="text-center">Producto</th>
                                    <th class="text-center">
                                        Categoría
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody_productos">
                                <?php $__currentLoopData = $data['productos_externos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr style="cursor: pointer" onclick='seleccionarProductoAyuda("<?php echo e($i->id); ?>")'>
                                        <td class="text-center">
                                            <?php echo e(strtoupper($i->codigo_barra ?? '')); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->nombre ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->categoria ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -- fin modal ayuda prodcutos-->
    <script>
        window.addEventListener("load", initialice, false);

        function initialice() {
            var sucursal = $("#sucursal option[value='" + "<?php echo e($data['filtros']['sucursal']); ?>" + "']").html();

            var topMesage = 'Reporte de Inventario de productos externos de la sucursal ' + sucursal+'.' ;
            var bottomMesage = 'Reporte de Inventario de productos externos filtrado por';

            topMesage +=  ' Solicitud realizada por ' + "<?php echo e(session('usuario')['usuario']); ?>" + '.';

            if ("<?php echo e($data['filtros']['sucursal']); ?>" != '-1') {
                bottomMesage += ' sucursal [ ' + sucursal + ' ],';
            } else {
                bottomMesage += ' sucursal [ Todas ],';
            }


            bottomMesage += ' Desarrollado por Space Software CR. ';


            $('#tablaInventarios').DataTable({
                dom: 'Bfrtip',
                "searching": false,
                "paging": false,
                'fixedHeader': {
                    'header': true,
                    'footer': true
                },
                buttons: [{
                    extend: 'excel',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'inventario_' + sucursal + '_el_amanecer'
                }, {
                    extend: 'pdf',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'inventario_' + sucursal + '_el_amanecer'
                }, {
                    extend: 'print',
                    title: 'SPACE REST',
                    messageTop: topMesage,
                    messageBottom: bottomMesage,
                    filename: 'inventario_' + sucursal + '_el_amanecer'
                }]
            });

        }

    </script>


<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/productoExterno/inventario/inventarios.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/productoExterno/inventario/inventarios.blade.php ENDPATH**/ ?>