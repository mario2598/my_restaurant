<?php $__env->startSection('style'); ?>


<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Pedidos Pendientes</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="buscar_pedido" class="form-control"
                                    placeholder="Buscar pedido">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon"><i class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="tablaPedidos">
                                <thead>
                                    <tr>

                                        <th class="text-center"># Pedido</th>
                                        <th class="text-center">Solicitante</th>
                                        <th class="text-center">Sucursal</th>
                                        <th class="text-center">Fecha</th>

                                    </tr>
                                </thead>
                                <tbody id="tbody_generico">
                                    <?php $__currentLoopData = $data['pedidos_pendientes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="space_row_table" style="cursor: pointer;"
                                            onclick='verSucursalBodegaPedido("<?php echo e($g->id); ?>")'>

                                            <td class="text-center">
                                                <?php echo e($g->id); ?>

                                            </td>
                                            <td class="text-center">
                                                <?php echo e($g->emisorNombre ?? ''); ?>

                                            </td>
                                            <td class="text-center">
                                                <?php echo e($g->sucursalNombre ?? ''); ?>

                                            </td>
                                            <td class="text-center">
                                                <?php echo e($g->fecha ?? ''); ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>

    <form id="formGoPedidoBodega" action="<?php echo e(URL::to('bodega/inventario/pedido')); ?>" style="display: none" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="idPedidoBodega" id="idPedidoBodega" value="-1">
    </form>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>

    <script>
        function verSucursalBodegaPedido(id) {
            if (id != "") {
                $('#idPedidoBodega').val(id);
                $('#formGoPedidoBodega').submit();
            }
        }

    </script>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/bodega/inventario/pedidosPendientes.blade.php ENDPATH**/ ?>