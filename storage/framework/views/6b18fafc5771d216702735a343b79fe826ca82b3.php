<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Coffee To Go</title>
    <meta name="keywords" content="The coffee experience">
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', config('app.name')); ?>">
    <meta name="author" content="<?php echo $__env->yieldContent('meta_author', config('app.name')); ?>">
    <!-- Favicon -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type='image/x-icon' href="<?php echo e(asset('assets/images/coffeeMini.png')); ?>">

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
    <link id="ligthStyle" href="<?php echo e(asset('assets/bundles/lightgallery/dist/css/lightgallery.css')); ?>" rel="stylesheet">
    <?php echo $__env->yieldContent('styles'); ?>
</head>

<body style="sidebar-gone">
    <input type="hidden" value="<?php echo e(url('/')); ?>" id="base_path">
    <!-- Begin page -->
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <?php echo $__env->make('layout.topbarUsuarioE', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->yieldContent('content'); ?>
            <?php echo $__env->yieldContent('popup'); ?>
            <?php echo $__env->make('layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="<?php echo e(asset('assets/bundles/sweetalert/sweetalert.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/app.min.js')); ?>"></script>
    <!-- Page Specific JS File -->
    <script src="<?php echo e(asset('assets/js/page/index.js')); ?>"></script>
    <!-- Template JS File -->
    <script src="<?php echo e(asset('assets/js/scripts.js')); ?>"></script>
    <!-- Custom JS File -->
    <script src="<?php echo e(asset('assets/js/custom.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/bundles/izitoast/js/iziToast.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/space.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/page/ion-icons.js')); ?>"></script>
    <script id="script1" src="<?php echo e(asset('assets/bundles/lightgallery/dist/js/lightgallery-all.js')); ?>"></script>
    <!-- Page Specific JS File -->
    <script id="script2" src="<?php echo e(asset('assets/js/page/light-gallery.js')); ?>"></script>
    <?php echo $__env->yieldContent('script'); ?>



</body>



</html>
<?php /**PATH D:\Proyectos\2023\Laravel\CoffeeToGo\resources\views/layout/master-usuarioMobile.blade.php ENDPATH**/ ?>