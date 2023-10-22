<?php $__env->startSection('content'); ?>
   
    <?php echo $__env->make('layout.sidebarMenuMobile', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <style>
        .card-mario {
            border: 1px solid #ccc;
            /* Borde delgado de color gris */
            border-radius: 8px;
            /* Bordes poco redondeados */
            background-color: #fff;
            /* Color de fondo blanco */
            box-shadow: none;
            margin-right: 5px;
            /* Sin sombra */
            transition: transform 0.2s;
            /* Efecto de transformación suave al hacer hover */

            /* Estilo del texto */
            color: #333;
            font-size: 16px;
        }



        .imagen-cuadrada {
            width: 200px;
            /* Establece el ancho fijo para la imagen */
            height: 200px;
            /* Establece la altura fija para la imagen (mismo valor que el ancho) */
            object-fit: cover;
            /* Ajusta la imagen para que cubra completamente el cuadrado sin distorsión */
        }

        .lg-outer .lg-thumb-item {
            border: 0px solid #FFF !important;
        }


        .categories ul li a:hover {
            color: white;
            background: black;
        }

       

        .categories ul {
            text-align: left;
        }

        .col-sm-12 {
            padding-right: 0px !important;
            padding-left: 0px !important;
        }

        .card .card-body {
            padding: 5px !important;
            padding-left: 10px !important;
        }
    </style>
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">

                        <div class="col-md-12">
                            <div class="section-body" style="margin-top: 5px;">
                                <div class="row clearfix">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">
                                        
                                                <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                                    <?php $__currentLoopData = $data['categorias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"
                                                            onclick="seleccionarTipo(<?php echo e($index); ?>)"
                                                            style="padding: 10px;">
                                                            <div class="card-mario" style="padding: 10px;">
                                                                <img class="img-responsive thumbnail imagen-cuadrada"
                                                                    src="<?php echo e($cat->url_imagen); ?>"
                                                                    alt="<?php echo e($cat->categoria); ?>">

                                                                <p style="text-align: center;">
                                                                    <small><?php echo e($cat->categoria); ?></small>
                                                                    <br>

                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                          
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(asset('assets/js/usuarioExterno/menuMobile.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master-usuarioMobile', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/usuarioExterno/menuMobile.blade.php ENDPATH**/ ?>