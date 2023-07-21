<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Listas de productos -->
    <script>
        var tipos = []; // Se crea la lista  de tipos de productos
        var productosGeneral = [];
        var detalles = [];
        var salonSeleccionado = 0;
        var mobiliarioSeleccionado = 0;
        var clienteSeleccionado = 0;
        var contadorAux = 0;
        var tipoOrden = "<?php echo e($data['orden']->tipo ?? 'LL'); ?>";
        var idOrden = "<?php echo e($data['orden']->id ?? -1); ?>";

    </script>


    <?php $__currentLoopData = $data['tipos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <script>
            var categorias = [];
            var auxProducto;

        </script>

        <?php $__currentLoopData = $tipo['categorias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <script>
                var productos = [];

            </script>

            <?php $__currentLoopData = $categoria->productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <script>
                    auxProducto = {
                        "id": "<?php echo e($producto->id); ?>",
                        "nombre": "<?php echo e($producto->nombre ?? ''); ?>",
                        "impuesto": "<?php echo e($producto->impuesto ?? 0); ?>",
                        "precio": "<?php echo e($producto->precio ?? 0); ?>",
                        "codigo": "<?php echo e($producto->codigo ?? ''); ?>",
                        "tipoComanda": "<?php echo e($producto->tipo_comanda ?? ''); ?>",
                        "cantidad": "<?php echo e($producto->cantidad ?? -1); ?>",
                        "cantidad_original": "<?php echo e($producto->cantidad ?? -1); ?>",
                        "tipoProducto": "<?php echo e($producto->tipoProducto ?? -1); ?>"
                    };
                    productos.push(auxProducto);
                    productosGeneral.push(auxProducto);

                </script>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <script>
                categorias.push({
                    "id": "<?php echo e($categoria->id); ?>",
                    "categoria": "<?php echo e($categoria->categoria); ?>",
                    "productos": productos
                });

            </script>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <script>
            tipos.push({
                "nombre": "<?php echo e($tipo['nombre']); ?>",
                "codigo": "<?php echo e($tipo['codigo']); ?>",
                "color": "<?php echo e($tipo['color']); ?>",
                "categorias": categorias
            });

        </script>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <script>
        var salones = []; // Se crea la lista  de salones
        let productoEncontrado;
        let totalAux = 0;

    </script>

    <?php $__currentLoopData = $data['orden']->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <script>
            productosGeneral.forEach(producto => {
                if (producto.codigo == "<?php echo e($d->codigo_producto); ?>") {
                    productoEncontrado = producto;
                }
            });

            totalAux = parseFloat("<?php echo e($d->precio_unidad); ?>" * "<?php echo e($d->cantidad); ?>").toFixed(2);

            if ("<?php echo e($d->servicio_mesa); ?>" == 'S') {
                let impuestoMesa = 0;
                impuestoMesa =  totalAux -(totalAux / 1.10); 
                totalAux = parseInt(totalAux) + parseInt(impuestoMesa);
            }
            detalles.push({
                "indice": contadorAux,
                "cantidad": "<?php echo e($d->cantidad); ?>",
                "impuestoServicio": "<?php echo e($d->servicio_mesa); ?>",
                "impuesto": "<?php echo e($d->porcentaje_impuesto); ?>",
                "precio_unidad": "<?php echo e($d->precio_unidad); ?>",
                "total": totalAux,
                "observacion": "<?php echo e($d->observacion); ?>",
                //"tipo": tipos[tipoSeleccionado].codigo,
                "tipo": "<?php echo e($d->tipo_producto); ?>",
                "fechaCreacion": "<?php echo e($d->fecha_creacion); ?>",
                "tipoComanda": "<?php echo e($d->tipo_comanda); ?>",
                "cantidadPreparada": "<?php echo e($d->cantidad_preparada); ?>",
                "producto": productoEncontrado
            });
            contadorAux++;

        </script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <!-- #endregion -->

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="row">
                            <!-- <div class="d-flex flex-row">
                                                                     Panel seleccionar productos -->
                            <div class="col-lg-5 col-md-12 pr-25">
                                <!-- Productos -->
                                <ul class="nav nav-pills" id="nv-tipos">
                                    <!-- Lista dinámica de tipos -->
                                </ul>
                                <div class="card">
                                    <!-- Categorías -->
                                    <div class="card-header col-12 mt-1" style="max-height: 450px;">
                                        <ul id="scrl-categorias"
                                            class="nav nav-pills d-flex flex-row justify-content-space-between draggable-scroller"
                                            style="overflow-x: auto; cursor: grab; white-space: nowrap; flex-wrap: nowrap;">
                                            <!-- Lista dinámica categorías -->
                                        </ul>
                                    </div>
                                    <!-- Productos -->
                                    <div id="scrl-productos"
                                        class="col-12 d-flex flex-column justify-content-space-between card-body draggable-scroller"
                                        style="max-height: 450px; overflow-y: auto; cursor:grab;">
                                        <table class="table table-borderless" style="background-color: white">
                                            <thead>
                                                <th>Producto</th>
                                                <th class="text-center">Precio</th>
                                                <th class="text-center">Acciones</th>
                                            </thead>
                                            <tbody id="tbody-productos">
                                                <!-- Lista dinámica de productos -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel orden -->
                            <div class="col-lg-7 col-md-12 pl-0">
                                <!-- Acciones -->
                                <div style="padding: 0 5% 1.3% 0">
                                    <ul class="nav nav-pills d-flex flex-row justify-content-end" id="nv-acciones">
                                        <a class="btn btn-info px-2 mr-1" href="<?php echo e(url('facturacion/facturar')); ?>"
                                            style=" color: white;cursor: pointer;"> Nueva Factura <i class="fas fa-broom"
                                                aria-hidden="true"></i></a>

                                        <a class="btn btn-info px-2 mr-1"
                                            onclick='goDividirFactura("<?php echo e($data['orden']->id); ?>")'
                                            style=" color: white;cursor: pointer;"> Dividir Factura <i class="fas fa-half"
                                                aria-hidden="true"></i></a>
                                        <a class="btn btn-info px-2 mr-1"
                                            onclick='preTickete("<?php echo e($data["orden"]->id); ?>")'
                                               style="color: white;cursor: pointer;"> Pre tiquete <i class="fas fa-print"
                                                aria-hidden="true"></i></a>
                                        <!-- <button type="button" class="btn btn-info px-2 mr-1"
                                                                                    onclick="confirmarOrden()">Caja Rápida <i class="fas fa-money-bill"
                                                                                        aria-hidden="true"></i></button>-->
                                    </ul>
                                </div>
                                <!-- Orden -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-block">
                                            <h5 class="card-title">Orden ORD-<?php echo e($data['orden']->numero_orden ?? '###'); ?>

                                            </h5>
                                            <div class="d-flex flex-row mt-10">
                                                <div class="col-4 d-flex flex-row mt-3 mb-2">
                                                    <h3 class="text-muted">Total: </h3>
                                                    <h3 id="txt-total-pagar" class="text-muted" style="margin-left: 3%">
                                                        0,00</h3>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="d-flex flex-row mt-10">
                                                        <div class="form-group col-12 mb-0">
                                                            <div class="input-group">
                                                                <h6>Estimado :
                                                                    <?php echo e($data['orden']->nombre_cliente ?? 'CLIENTE'); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <?php if($data['mesa'] != null): ?>
                                                        <div class="form-group col-12 mt-2 mb-0">
                                                            <h6>Mesa No.<?php echo e($data['mesa']->numero_mesa); ?></h6>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="form-group col-6 mt-2 mb-0" style="text-align: center">
                                                    <button type="button" class="btn btn-info "
                                                        onclick='guardarFactura(true)'>Facturar <i
                                                            class="fas fa-file-invoice-dollar"
                                                            aria-hidden="true"></i></button>
                                                </div>
                                                <div class="form-group col-6 mt-2 mb-0" style="text-align: center">
                                                    <button type="button" class="btn btn-info "
                                                        onclick='guardarFactura(false)'>Guardar orden <i
                                                            class="fas fa-file-save" aria-hidden="true"></i></button>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="card-body">
                                            <table class="table" id="scrl-orden" class="draggable-scroller"
                                                style="max-height: 100%;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col">Producto</th>
                                                        <th scope="col" style="text-align: center">Cantidad</th>
                                                        <th scope="col" style="text-align: center">Precio</th>
                                                        <th scope="col" style="text-align: center">Total</th>
                                                        <th scope="col" style="text-align: center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-orden">

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    
    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>

    <input type="text" style="width: 3px;" id="scanner" placeholder="scanner">
    <form id="frm-dividirFactura" action="<?php echo e(URL::to('facturacion/dividirFactura')); ?>" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="ipt_id_orden_dividir" id="ipt_id_orden_dividir">
    </form>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/js/facturacion/factura.js')); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master-facturacion', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/facturacion/factura.blade.php ENDPATH**/ ?>