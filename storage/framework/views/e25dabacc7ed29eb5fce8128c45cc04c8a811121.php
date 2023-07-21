<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layout.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script>
        var orden = {
            "id": "<?php echo e($data['orden']->id); ?>",
            "numero_orden": "<?php echo e($data['orden']->numero_orden); ?>",
            "total": "<?php echo e($data['orden']->total); ?>",
            "total_cancelado": "<?php echo e($data['orden']->total_cancelado); ?>"
        };

        //console.log(orden);

        var detalles = [];
        let producto;

    </script>

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-xs-12 col-md-12 col-lg-12">
                        <div class="col-12 col-xs-12 card" style="padding:10px;">
                            <div class="row" style="padding-top:8px; padding-left:8px;">
                                <div class="col-sm-12 col-md-4 col-lg-3">
                                    <h6 class="text-muted">Cajero: </h6>
                                    <h6 class="text-muted">
                                        <?php echo e($data['orden']->nombre_cajero ?? 'Usuario'); ?>

                                    </h6>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-3">
                                    <h6 class="text-muted">Cliente: </h6>
                                    <h6 class="text-muted">
                                        <?php echo e($data['orden']->nombre_cliente ?? 'Estimado cliente'); ?></h6>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-2">
                                    <h6 class="text-muted">Fecha: </h6>
                                    <h6 class="text-muted"> <?php echo e(date('d-m-Y')); ?></h6>
                                </div>

                                <div class="col-xs-12 col-md-12 col-lg-4">
                                    <ul class="nav nav-pills d-flex flex-row justify-content-end" id="nv-acciones"
                                        style="float: right">
                                        <button type="button" class="btn btn-info px-2 mr-1" style="margin-top: 5px;"
                                            onclick="confirmarPagarOrden()">Pagar orden <i class="fas fa-money-bill"
                                                aria-hidden="true"></i></button>

                                        <button type="button" class="btn btn-info px-2 mr-1" style="margin-top: 5px;"
                                            onclick="confirmarPagarOrden(true)">Adelantar orden <i class="fas fa-clock"
                                                aria-hidden="true"></i></button>
                                        
                                        <button type="button" class="btn btn-info px-2 mr-1" style="margin-top: 5px;"
                                            onclick='goFacturaOrden("<?php echo e($data["orden"]->id); ?>")'>Editar orden <i class="fas fa-cog"
                                                aria-hidden="true"></i></button>

                                    </ul>
                                </div>

                            </div>
                        </div>
                        <!-- Órdenes -->
                        <div class="row">
                            <!-- Panel formulario -->
                            <div class="col-xs-12 col-md-12 col-lg-7 pl-0">

                                <!-- Orden -->
                                <div class="col-12">
                                    <div class="card">

                                        <div class="card-header d-block">
                                            <h5 class="card-title">Facturar</h5>
                                            <div class="d-flex flex-row px-3">
                                                <div class="col-4 d-flex flex-row justify-content-start mt-3 mb-2">
                                                    <h6 class="text-muted" >Orden: </h6>
                                                    <h6 class="text-muted" style="margin-left: 3%" id="lbl-total-general">
                                                       ORD-<?php echo e($data['orden']->numero_orden); ?></h6>
                                                </div>
                                                <div class="col-4 d-flex flex-row justify-content-center mt-3 mb-2">
                                                    <h6 class="text-muted">Prepago: </h6>
                                                    <h6 class="text-muted" style="margin-left: 3%">
                                                        <?php echo e($data['orden']->total_cancelado); ?></h6>
                                                </div>
                                                <div class="col-4 d-flex flex-row justify-content-end mt-3 mb-2">
                                                    <h6 class="text-muted">Descuento: </h6>
                                                    <h6 id="lbl-descuento" class="text-muted" style="margin-left: 3%">
                                                        0,00</h6>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row px-3">
                                                <div class="col-4 d-flex flex-row justify-content-start mt-3 mb-2">
                                                    <h6 class="text-muted">Pago: </h6>
                                                    <h6 id="lbl-total" class="text-muted" style="margin-left: 3%">
                                                        0,00</h6>
                                                </div>
                                                <div class="col-4 d-flex flex-row justify-content-center mt-3 mb-2">
                                                    <h6 class="text-muted">Pendiente: </h6>
                                                    <h6 id="lbl-total-pendiente" class="text-muted" style="margin-left: 3%">
                                                        0,00</h6>
                                                </div>
                                                <div class="col-4 d-flex flex-row justify-content-end mt-3 mb-2">
                                                    <h6 class="text-muted">Vuelto: </h6>
                                                    <h6 id="lbl-vuelto" class="text-muted" style="margin-left: 3%">
                                                        0,00</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body col-12">
                                            <div class="container-fluid d-flex flex-row">
                                                <div class="form-group col-6">
                                                    <label for="txt-efectivo">Efectivo</label>
                                                    <input type="number" class="form-control" id="txt-efectivo"
                                                        placeholder="0.00" onchange="cambioValor('efectivo')">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="txt-tarjeta">Tarjeta</label>
                                                    <input type="number" class="form-control" id="txt-tarjeta"
                                                        placeholder="0.00" onchange="cambioValor('tarjeta')">
                                                </div>
                                            </div>
                                            <div class="container-fluid d-flex flex-row">
                                                <div class="form-group col-6">
                                                    <label for="txt-sinpe">SINPE Móvil</label>
                                                    <input type="number" class="form-control" id="txt-sinpe"
                                                        placeholder="0.00" onchange="cambioValor('sinpe')">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer d-flex flex-column">
                                            <div class="col-12">
                                                <div class="form-group col-12 mb-0 px-3">
                                                    <h6 class="card-subtitle ml-2 text-muted">Cliente</h6>
                                                    <div class="input-group">
                                                        <input type="hidden" name="txt-id-cliente"
                                                            value="<?php echo e($data['orden']->cliente ?? ''); ?>" id="txt-id-cliente"
                                                            value="-1">
                                                        <input type="text" class="form-control h-75" name="txt-cliente"
                                                            id="txt-cliente"
                                                            value="<?php echo e($data['orden']->nombre_cliente ?? ''); ?>"
                                                            onchange="$('#txt-id-cliente').val('-1')">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 d-flex flex-row mt-5">
                                                <div class="form-group col-4">
                                                    <h6 class="ml-2 text-muted">% Descuento</h6>
                                                    <input type="number" class="form-control" id="txt-descuento" max="100"
                                                        min="0" placeholder="0" onchange="asignarDescuento()">
                                                </div>
                                                <div class="form-group col-4">
                                                    <h6 class="ml-2 text-muted">Imprime tiquete</h6>
                                                    <select class="custom-select" id="sel-tiquete"
                                                        onchange="cambiarImprimeTiquete()">
                                                        <option value="N">No</option>
                                                        <option value="S">Sí</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-4">
                                                    <h6 class="ml-2 text-muted">Factura electrónica</h6>
                                                    <select class="custom-select" id="sel-factura"
                                                        onchange="cambiarGeneraFactura()">
                                                        <option value="N">No</option>
                                                        <option value="S">Sí</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Panel ayuda calculadora -->
                            <div class="col-xs-12 col-md-12 col-lg-4">
                                <div class="card" style="width: 100%;">
                                    <div class="calculator card">

                                        <input type="text" id="txt-teclado" class="calculator-screen z-depth-1" value="0"
                                            disabled />

                                        <div class="calculator-keys">

                                            <button type="button" class="operator btn btn-info" value="E"
                                                onclick="asignarMontoTeclado(event)"><i
                                                    class="fas fa-money-bill"></i></button>
                                            <button type="button" class="operator btn btn-info" value="T"
                                                onclick="asignarMontoTeclado(event)"><i
                                                    class="fas fa-credit-card"></i></button>
                                            <button type="button" class="operator btn btn-info" value="S"
                                                onclick="asignarMontoTeclado(event)"><i class="fas fa-mobile"></i></button>


                                            <button type="button" value="7" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">7</button>
                                            <button type="button" value="8" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">8</button>
                                            <button type="button" value="9" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">9</button>

                                            <button type="button" value="4" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">4</button>
                                            <button type="button" value="5" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">5</button>
                                            <button type="button" value="6" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">6</button>


                                            <button type="button" value="1" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">1</button>
                                            <button type="button" value="2" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">2</button>
                                            <button type="button" value="3" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">3</button>


                                            <button type="button" value="0" class="btn btn-light waves-effect"
                                                onclick="agregarNumeroTeclado(event)">0</button>
                                            <button type="button" class="decimal function btn btn-secondary"
                                                onclick="agregarNumeroTeclado(event)" value=".">.</button>
                                            <button type="button" class="all-clear function btn btn-danger btn-sm"
                                                onclick="limpiarTeclado()" value="all-clear"><i
                                                    class="fas fa-broom"></i></button>

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

    <form id="frm-factrar-orden" action="<?php echo e(URL::to('facturacion/pagar')); ?>" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="ipt_id_orden" id="ipt_id_orden">
    </form>

    <form id="frm-crear-orden" action="<?php echo e(URL::to('facturacion/facturar')); ?>" method="GET">
        <?php echo e(csrf_field()); ?>

    </form>

    <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/bundles/datatables/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/datatables.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/js/facturacion/pagar.js')); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master-facturacion', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/facturacion/pagar.blade.php ENDPATH**/ ?>