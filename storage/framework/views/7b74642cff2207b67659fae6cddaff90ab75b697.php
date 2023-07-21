<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title>El Amanecer</title>
        <meta name="keywords" content="Sistema de Gestión de movimientos">
        <meta name="description" content="<?php echo $__env->yieldContent('meta_description', config('app.name')); ?>">
        <meta name="author" content="<?php echo $__env->yieldContent('meta_author', config('app.name')); ?>">
        <!-- Favicon -->
        
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type='image/x-icon' href="<?php echo e(asset("assets/images/favicon.ico")); ?>">

        <link rel="stylesheet" href="<?php echo e(asset("assets/css/app.min.css")); ?>">
        <!-- Template CSS -->
        <link rel="stylesheet" href="<?php echo e(asset("assets/css/style.css")); ?>">
        <link rel="stylesheet" href="<?php echo e(asset("assets/css/components.css")); ?>">
        <!-- Custom style CSS -->
        <link rel="stylesheet" href="<?php echo e(asset("assets/css/custom.css")); ?>">
        <link rel="stylesheet" href="<?php echo e(asset("assets/bundles/izitoast/css/iziToast.min.css")); ?>">
    </head>

    <body>
        <!-- Begin page -->
        <div class="loader"></div>
        <div id="app">
            <div class="main-wrapper main-wrapper-1">
                
                <!-- ============================================================== -->
                <!-- Start right Content here -->
                <!-- ============================================================== -->
        
                <?php echo $__env->yieldContent('content'); ?>

                <!-- ============================================================== -->
                <!-- End Right content here -->
                <!-- ============================================================== -->

            </div>
        </div>
        <?php echo $__env->make('layout.msjAlerta', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- General JS Scripts -->
        <script src="<?php echo e(asset("assets/js/app.min.js")); ?>"></script>
        <!-- JS Libraies -->
        <script src="<?php echo e(asset("assets/bundles/apexcharts/apexcharts.min.js")); ?>"></script>
        <!-- Page Specific JS File -->
        <script src="<?php echo e(asset("assets/js/page/index.js")); ?>"></script>
        <!-- Template JS File -->
        <script src="<?php echo e(asset("assets/js/scripts.js")); ?>"></script>
        <!-- Custom JS File -->
        <script src="<?php echo e(asset("assets/js/custom.js")); ?>"></script>
        <script src="<?php echo e(asset("assets/bundles/izitoast/js/iziToast.min.js")); ?>"></script>
    </body>
  

   
</html><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/layout/master-login.blade.php ENDPATH**/ ?>