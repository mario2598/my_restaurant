<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte consumo diario <?php echo e($data['fechaReporte']); ?> - Coffee To Go</title>
    <style>
        body {
            background: #fff;
            color: #333;
            font-family: Lato, sans-serif;
        }

        .container {
            display: block;

        }

        ul {
            margin: 0;
            padding: 0;
        }

        li * {
            float: left;
        }

        li,
        h3 {
            clear: both;
            list-style: none;
        }

        input,
        button {
            outline: none;
        }

        button {
            background: none;
            border: 0px;
            color: #888;
            font-size: 15px;
            width: 60px;
            margin: 10px 0 0;
            font-family: Lato, sans-serif;
            cursor: pointer;
        }

        button:hover {
            color: #333;
        }

        /* Heading */
        h3,
        label[for='new-task'] {
            color: #333;
            font-weight: 700;
            font-size: 15px;
            border-bottom: 2px solid #333;
            padding: 30px 0 10px;
            margin: 0;
            text-transform: uppercase;
        }

        input[type="text"] {
            margin: 0;
            font-size: 18px;
            line-height: 18px;
            height: 18px;
            padding: 10px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 6px;
            font-family: Lato, sans-serif;
            color: #888;
        }

        input[type="text"]:focus {
            color: #333;
        }

        /* New Task */
        label[for='new-task'] {
            display: block;
            margin: 0 0 20px;
        }

        input#new-task {
            float: left;
            width: 318px;
        }

        p>button:hover {
            color: #0FC57C;
        }

        /* Task list */
        li {
            overflow: hidden;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }

        li>input[type="checkbox"] {
            margin: 0 10px;
            position: relative;
            top: 15px;
        }

        li>label {
            font-size: 18px;
            line-height: 40px;
            width: 237px;
            padding: 0 0 0 11px;
        }

        li>input[type="text"] {
            width: 226px;
        }

        li>.delete:hover {
            color: #CF2323;
        }

        /* Completed */
        #completed-tasks label {
            text-decoration: line-through;
            color: #888;
        }

        /* Edit Task */
        ul li input[type=text] {
            display: none;
        }

        ul li.editMode input[type=text] {
            display: block;
        }

        ul li.editMode label {
            display: none;
        }


        @import  url(https://fonts.googleapis.com/css?family=Roboto:100,300,400,900,700,500,300,100);

        * {
            margin: 0;
            box-sizing: border-box;

        }

        body {
            background: #E0E0E0;
            font-family: 'Roboto', sans-serif;
            background-image: url('');
            background-repeat: repeat-y;
            background-size: 100%;
        }

        ::selection {
            background: #f31544;
            color: #FFF;
        }

        ::moz-selection {
            background: #f31544;
            color: #FFF;
        }

        h1 {
            font-size: 1.5em;
            color: #222;
        }

        h2 {
            font-size: .9em;
        }

        h3 {
            font-size: 1.2em;
            font-weight: 300;
            line-height: 2em;
        }

        p {
            font-size: .7em;
            color: #666;
            line-height: 1.2em;
        }

        #invoiceholder {
            width: 100%;
            hieght: 100%;
            padding-top: 50px;
        }

        #headerimage {
            z-index: -1;
            position: relative;
            top: -50px;
            height: 350px;
            background-image: url('http://michaeltruong.ca/images/invoicebg.jpg');

            -webkit-box-shadow: inset 0 2px 4px rgba(0, 0, 0, .15), inset 0 -2px 4px rgba(0, 0, 0, .15);
            -moz-box-shadow: inset 0 2px 4px rgba(0, 0, 0, .15), inset 0 -2px 4px rgba(0, 0, 0, .15);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, .15), inset 0 -2px 4px rgba(0, 0, 0, .15);
            overflow: hidden;
            background-attachment: fixed;
            background-size: 1920px 80%;
            background-position: 50% -90%;
        }

        #invoice {
            position: relative;
            top: -290px;
            margin: 0 auto;
            width: 700px;
            background: #FFF;
        }

        [id*='invoice-'] {
            /* Targets all id with 'col-' */
            border-bottom: 1px solid #EEE;
            padding: 30px;
        }

        #invoice-top {
            min-height: 120px;
        }

        #invoice-mid {
            min-height: 120px;
        }

        #invoice-bot {
            min-height: 250px;
            margin: 15px;
        }

        .logo {
            float: left;
            height: 60px;
            width: 60px;
            background-size: 60px 60px;
            border-radius: 50px;
        }

        .clientlogo {
            float: left;
            height: 60px;
            width: 60px;
            background: url(http://michaeltruong.ca/images/client.jpg) no-repeat;
            background-size: 60px 60px;
            border-radius: 50px;
        }

        .info {
            display: block;
            float: left;
            margin-left: 20px;
        }

        .title {
            float: right;
        }

        .title p {
            text-align: right;
        }

        #project {
            float: right;
            ;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px 0 5px 15px;
            border: 1px solid #EEE
        }

        .tabletitle {
            padding: 5px;
            background: #EEE;
        }

        .service {
            border: 1px solid #EEE;
        }

        .item {
            width: 50%;
        }

        .itemtext {
            font-size: .9em;
        }

        #legalcopy {
            margin-top: 30px;
        }

        form {
            float: right;
            margin-top: 30px;
            text-align: right;
        }


        .effect2 {
            position: relative;
        }

        .effect2:before,
        .effect2:after {
            z-index: -1;
            position: absolute;
            content: "";
            bottom: 15px;
            left: 10px;
            width: 50%;
            top: 80%;
            max-width: 300px;
            background: #777;
            -webkit-box-shadow: 0 15px 10px #777;
            -moz-box-shadow: 0 15px 10px #777;
            box-shadow: 0 15px 10px #777;
            -webkit-transform: rotate(-3deg);
            -moz-transform: rotate(-3deg);
            -o-transform: rotate(-3deg);
            -ms-transform: rotate(-3deg);
            transform: rotate(-3deg);
        }

        .effect2:after {
            -webkit-transform: rotate(3deg);
            -moz-transform: rotate(3deg);
            -o-transform: rotate(3deg);
            -ms-transform: rotate(3deg);
            transform: rotate(3deg);
            right: 10px;
            left: auto;
        }



        .legal {
            width: 70%;
        }

        .flex-item-left {
            flex: 50%;
        }

        .flex-item-right {
            flex: 50%;
        }

        @media (max-width: 800px) {

            .flex-item-right,
            .flex-item-left {
                flex: 100%;
            }
        }

        .grid-container {
            display: grid;
            grid-template-columns: auto auto auto auto;
        }

        .item1 {
            grid-column: 1 / 5;
            margin-bottom: 20px;
        }
    </style>


</head>

<body>
    <div id="invoiceholder">
        <h2>Reportes de consumo</h2>
        <h2>Fecha : <?php echo e($data['fechaReporte']); ?></h2>
        <br>
        <div id="invoiceholder">
            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(count($s->reporteConsumoMp) > 0): ?>
                    <h3>Reportes de consumo de materia prima por sucursal</h3>
                <?php else: ?>
                    <h3>No se Reporta consumo de materia prima para la sucursal</h3>
                <?php endif; ?>
                <?php if(count($s->reporteConsumoMp) > 0): ?>
                    <div id="invoice-bot">
                        <h4><?php echo e($s->descripcion); ?></h4>
                        <table class="table " id="tablaIngresos">
                            <thead>
                                <tr>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">Consumo</th>
                                    <th class="text-center">Unidad Medida</th>
                                    <th class="text-center">Precio Unidad</th>
                                    <th class="text-center">Costo</th>
                                    <th class="text-center">Total en inventario</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_generico">
                                <?php $__currentLoopData = $s->reporteConsumoMp; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="space_row_table" style="cursor: pointer;">
                                        <td class="text-center">
                                            <?php echo e($g->nombreProducto ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($g->suma ?? 0); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($g->unidad_medida ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            CRC <?php echo e(number_format($g->precio_unidad ?? '0.00', 2, '.', ',')); ?>

                                        </td>
                                        <td class="text-center">
                                            CRC <?php echo e(number_format($g->costo ?? '0.00', 2, '.', ',')); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($g->cantTotalMp ?? ''); ?> <?php echo e($g->unidad_medida ?? ''); ?>

                                        </td>

                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </tbody>
                            <tfoot>
                                <tr class="space_row_table">
                                    <td class="text-center" style="background: rgb(226, 196, 196);"><strong>Total
                                        </strong></td>
                                    <td class="text-center" style="background: rgb(226, 196, 196);">
                                        ***
                                    </td>
                                    <td class="text-center" style="background: rgb(226, 196, 196);">
                                        ***
                                    </td>
                                    <td class="text-center" style="background: rgb(226, 196, 196);">
                                        <strong> ***</strong>
                                    </td>

                                    <td class="text-center" style="background: rgb(226, 196, 196);">

                                        <strong>CRC
                                            <?php echo e(number_format($s->costoTotalReporteConsumoMp ?? '0.00', 2, '.', ',')); ?></strong>
                                    </td>
                                    <td class="text-center" style="background: rgb(226, 196, 196);">
                                        ***
                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <br>
    <div id="invoiceholder">
        <?php if(count($s->reporteMateriaPrimaBaja) > 0): ?>
            <h3>Materia prima por debajo de su minimo deseable</h3>
        <?php else: ?>
            <h3>Sin materia prima abajo del minimo deseable</h3>
        <?php endif; ?>

        <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(count($s->reporteMateriaPrimaBaja) > 0): ?>
                <div id="invoice-bot">
                    <h4><?php echo e($s->descripcion); ?></h4>
                    <table class="table " id="tablaIngresos">
                        <thead>
                            <tr>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Minimo deseable</th>
                                <th class="text-center">Cantidad en el inventario</th>
                                <th class="text-center">Precio Unidad</th>
                                <th class="text-center">Proveedor</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_generico">
                            <?php $__currentLoopData = $s->reporteMateriaPrimaBaja; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="space_row_table" style="cursor: pointer;">
                                    <td class="text-center">
                                        <?php echo e($g->nombre ?? ''); ?>

                                    </td>
                                    <td class="text-center">
                                        <?php echo e($g->cant_min_deseada ?? 0); ?> <?php echo e($g->unidad_medida ?? ''); ?>

                                    </td>
                                    <td class="text-center">
                                        <?php echo e($g->cant_inventario ?? ''); ?> <?php echo e($g->unidad_medida ?? ''); ?>

                                    </td>
                                    <td class="text-center">
                                        CRC <?php echo e(number_format($g->precio ?? '0.00', 2, '.', ',')); ?>

                                    </td>
                                    <td class="text-center">
                                        <?php echo e($g->nombreProveedor ?? ''); ?>

                                    </td>

                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </tbody>

                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    </div>
    <br>
    <div id="invoiceholder">
        <div>
            <?php if(count($s->reporteMovIngresos) > 0): ?>
                <h3>Reportes de ingresos de productos externos por sucursal</h3>
            <?php else: ?>
                <h3>No se reportan ingresos de productos externos por sucursal</h3>
            <?php endif; ?>

            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(count($s->reporteMovIngresos) > 0): ?>
                    <div id="invoice-bot">
                        <h4><?php echo e($s->descripcion); ?></h4>
                        <table class="table " id="tablaIngresos">
                            <thead>
                                <tr>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">Cantidad ingresada</th>
                                    <th class="text-center">Inventario actual</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_generico">
                                <?php $__currentLoopData = $s->reporteMovIngresos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="space_row_table" style="cursor: pointer;">
                                        <td class="text-center">
                                            <?php echo e($i->nombreProducto ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->ingreso ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->cantInventarioActual ?? 0); ?>

                                        </td>

                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div><!-- End Invoice Holder-->
    </div>
    <br>
    <div id="invoiceholder">
        <div>

            <?php if(count($s->reporteMovSalidas) > 0): ?>
                <h3>Reportes de salidas de productos externos por sucursal</h3>
            <?php else: ?>
                <h3>No se reportan salidas de productos externos por sucursal</h3>
            <?php endif; ?>
            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(count($s->reporteMovSalidas) > 0): ?>
                    <div id="invoice-bot">
                        <h4><?php echo e($s->descripcion); ?></h4>
                        <table class="table " id="tablaIngresos">
                            <thead>
                                <tr>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">Cantidad de salida</th>
                                    <th class="text-center">Inventario actual</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_generico">
                                <?php $__currentLoopData = $s->reporteMovSalidas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="space_row_table" style="cursor: pointer;">
                                        <td class="text-center">
                                            <?php echo e($i->nombreProducto ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->salida ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->cantInventarioActual ?? 0); ?>

                                        </td>

                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <br>
    <div id="invoiceholder">
        <div>
            <?php if(count($s->reporteMovDesechos) > 0): ?>
                <h3>Reportes de salidas por desecho de productos externos por sucursal</h3>
            <?php else: ?>
                <h3>No se reportan salidas por desecho de productos externos por sucursal</h3>
            <?php endif; ?>
            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(count($s->reporteMovDesechos) > 0): ?>
                    <div id="invoice-bot">
                        <h4><?php echo e($s->descripcion); ?></h4>
                        <table class="table " id="tablaIngresos">
                            <thead>
                                <tr>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">Cantidad de desecho</th>
                                    <th class="text-center">Inventario actual</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_generico">
                                <?php $__currentLoopData = $s->reporteMovDesechos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="space_row_table" style="cursor: pointer;">
                                        <td class="text-center">
                                            <?php echo e($i->nombreProducto ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->desecho ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->cantInventarioActual ?? 0); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div><!-- End Invoice Holder-->
    </div>
    <br>
    <div id="invoiceholder">
        <div>
            <?php if(count($s->reporteVentasProdExt) > 0): ?>
                <h3>Reportes de salidas por venta de productos externos por sucursal</h3>
            <?php else: ?>
                <h3>No se reportan salidas por ventas de productos externos por sucursal</h3>
            <?php endif; ?>
            <?php $__currentLoopData = $data['sucursales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(count($s->reporteVentasProdExt) > 0): ?>
                    <div id="invoice-bot">
                        <h4><?php echo e($s->descripcion); ?></h4>
                        <table class="table " id="tablaIngresos">
                            <thead>
                                <tr>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">Cantidad salida</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_generico">
                                <?php $__currentLoopData = $s->reporteVentasProdExt; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="space_row_table" style="cursor: pointer;">
                                        <td class="text-center">
                                            <?php echo e($i->nombre_producto ?? ''); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($i->cantidad ?? ''); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div><!-- End Invoice Holder-->
    </div>
</body>

</html>
<?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/emails/reportes/reporteDiarioConsumoGen.blade.php ENDPATH**/ ?>