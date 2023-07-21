<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>El Amanecer</title>
    <meta name="keywords" content="Panadería y Cafetería">
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', config('app.name')); ?>">
    <meta name="author" content="<?php echo $__env->yieldContent('meta_author', config('app.name')); ?>">
    <!-- Favicon -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type='image/x-icon' href="<?php echo e(asset('assets/images/favicon.ico')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/app.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/bundles/izitoast/css/iziToast.min.css')); ?>">

    <!-- Template CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/components.css')); ?>">
    <!-- Custom style CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/custom.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/space.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/bundles/ionicons/css/ionicons.min.css')); ?>">
    <script type="module" src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldContent('styles'); ?>



</head>

<body>
    <input type="hidden" value="<?php echo e(url('/')); ?>" id="base_path">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->color_fondo ?? 1); ?>" id="cp_color_fondo">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->color_sidebar ?? 1); ?>" id="cp_color_sidebar">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->color_tema ?? 'white'); ?>" id="cp_color_tema">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->mini_sidebar ?? 1); ?>" id="cp_mini_sidebar">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->sticky_topbar ?? 1); ?>" id="cp_sticky_topbar">
    <!-- Begin page -->
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <?php echo $__env->make('layout.topbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->

            <?php echo $__env->yieldContent('content'); ?>

            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->
            <?php echo $__env->make('layout.comandaBar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->yieldContent('popup'); ?>
            <?php echo $__env->make('layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <form id="frm-facturar-orden" action="<?php echo e(URL::to('facturacion/dividirFactura')); ?>" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="ipt_id_orden" id="ipt_id_orden_fac">
    </form> 

    <form id="frm-go-orden" action="<?php echo e(URL::to('facturacion/factura')); ?>" style="display: none" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="ipt_id_orden_factura" id="ipt_id_orden_factura" value="-1">
    </form>


    <form id="frm-caja-rapida" action="<?php echo e(URL::to('facturacion/pagar')); ?>" method="POST">
        <?php echo e(csrf_field()); ?>

        <input type="hidden" name="ipt_id_orden" id="ipt_id_orden">
    </form>

    <?php echo $__env->make('layout.msjAlerta', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script src="<?php echo e(asset('assets/js/layout/comandaBar.js')); ?>"></script>
    <!-- General JS Scripts -->
    <script src="<?php echo e(asset('assets/bundles/sweetalert/sweetalert.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/app.min.js')); ?>"></script>
    <!-- JS Libraies -->
    <script src="<?php echo e(asset('assets/bundles/apexcharts/apexcharts.min.js')); ?>"></script>
    <!-- Page Specific JS File -->
    <script src="<?php echo e(asset('assets/js/page/index.js')); ?>"></script>
    <!-- Template JS File -->
    <script src="<?php echo e(asset('assets/js/scripts.js')); ?>"></script>
    <!-- Custom JS File -->
    <script src="<?php echo e(asset('assets/js/custom.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/bundles/izitoast/js/iziToast.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/space.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/ion-icons.js')); ?>"></script>
    <?php echo $__env->yieldContent('script'); ?>

</body>



</html>
<?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/layout/master-facturacion.blade.php ENDPATH**/ ?>