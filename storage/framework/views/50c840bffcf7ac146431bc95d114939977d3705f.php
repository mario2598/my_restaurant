<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title>GYM BAR</title>
        <meta name="keywords" content="Elevate your gym experience">
        <meta name="description" content="<?php echo $__env->yieldContent('meta_description', config('app.name')); ?>">
        <meta name="author" content="<?php echo $__env->yieldContent('meta_author', config('app.name')); ?>">
        <!-- Favicon -->
        
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type='image/x-icon' href="<?php echo e(asset("assets/images/coffeeMini.png")); ?>">

        <link rel="stylesheet" href="<?php echo e(asset("assets/css/app.min.css")); ?>">
        <link rel="stylesheet" href="<?php echo e(asset("assets/bundles/izitoast/css/iziToast.min.css")); ?>">

        <!-- Template CSS -->
        <link rel="stylesheet" href="<?php echo e(asset("assets/css/style.css")); ?>">
        <link rel="stylesheet" href="<?php echo e(asset("assets/css/components.css")); ?>">
        <!-- Custom style CSS -->
        <link rel="stylesheet" href="<?php echo e(asset("assets/css/custom.css")); ?>">
        <link rel="stylesheet" href="<?php echo e(asset("assets/css/space.css")); ?>">
        <link rel="stylesheet" href="<?php echo e(asset("assets/bundles/ionicons/css/ionicons.min.css")); ?>">
        <script type="module" src="<?php echo e(asset('js/app.js')); ?>"></script>
        <?php echo $__env->yieldContent('styles'); ?>

        
    
    </head>

    <body style="sidebar-gone">
    <input type="hidden" value="<?php echo e(url('/')); ?>" id="base_path">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->color_fondo ?? 1); ?>" id="cp_color_fondo">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->color_sidebar ?? 1); ?>" id="cp_color_sidebar">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->color_tema ?? 'white'); ?>" id="cp_color_tema">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->mini_sidebar ?? 1); ?>" id="cp_mini_sidebar">
    <input type="hidden" value="<?php echo e($data['panel_configuraciones']->sticky_topbar ?? 1); ?>" id="cp_sticky_topbar">
    <audio id="clicSound">
      <source src="clic.mp3" type="audio/mpeg">
      <source src="clic.ogg" type="audio/ogg">
      Tu navegador no admite la reproducción de sonidos.
    </audio>
        <!-- Begin page -->
        <div class="loader"></div>
            <div id="app">
                <div class="main-wrapper main-wrapper-1" >
                    <?php echo $__env->make('layout.topbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <!-- ============================================================== -->
                    <!-- Start right Content here -->
                    <!-- ============================================================== -->
            
                    <?php echo $__env->yieldContent('content'); ?>

                    <!-- ============================================================== -->
                    <!-- End Right content here -->
                    <!-- ============================================================== -->
                    <?php echo $__env->make('layout.configbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php echo $__env->yieldContent('popup'); ?>
                    <?php echo $__env->make('layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
        </div>
        
        <?php echo $__env->make('layout.msjAlerta', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- General JS Scripts -->
        <script src="<?php echo e(asset("assets/bundles/sweetalert/sweetalert.min.js")); ?>"></script>
        <script src="<?php echo e(asset("assets/js/app.min.js")); ?>"></script>
        <!-- Page Specific JS File -->
        <script src="<?php echo e(asset("assets/js/page/index.js")); ?>"></script>
        <!-- Template JS File -->
        <script src="<?php echo e(asset("assets/js/scripts.js")); ?>"></script>
        <!-- Custom JS File -->
        <script src="<?php echo e(asset("assets/js/custom.js")); ?>"></script>
        <script src="<?php echo e(asset("assets/bundles/izitoast/js/iziToast.min.js")); ?>"></script>
        <script src="<?php echo e(asset("assets/js/space.js")); ?>"></script>
        <script src="<?php echo e(asset("assets/js/page/ion-icons.js")); ?>"></script>
        <?php echo $__env->yieldContent('script'); ?>

        <form id="formGastoEditar" action="<?php echo e(URL::to('gastos/editar')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idGastoEditar" id="idGastoEditar" value="-1">
          </form>

          <form id="formCancelarMovimiento" action="<?php echo e(URL::to('bodega/movimiento/cancelar')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idMovimientoCancelar" id="idMovimientoCancelar" value="-1">
            <input type="hidden" name="detalleMovimientoCancelar" id="detalleMovimientoCancelar" value="-1">
          </form>

          <form id="formVerMovimiento" action="<?php echo e(URL::to('bodega/inventario/movimiento')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idMov" id="idMov" value="-1">
          </form>

          <form id="formGastoEliminar" action="<?php echo e(URL::to('gastos/sinaprobar/eliminar')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idGastoEliminar" id="idGastoEliminar" value="-1">
          </form>

          <form id="formGastoAdminEliminar" action="<?php echo e(URL::to('gastos/eliminar')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idGastoEliminar" id="idGastoAdminEliminar" value="-1">
          </form>
          
          <form id="formGastoRechazar" action="<?php echo e(URL::to('gastos/rechazar')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idGastoRechazar" id="idGastoRechazar" value="-1">
          </form>

          <form id="formGasto" action="<?php echo e(URL::to('gastos/gasto')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idGasto" id="idGasto" value="-1">
          </form>

          <form id="formIngreso" action="<?php echo e(URL::to('ingresos/ingreso')); ?>" style="display: none"  method="POST">
            <?php echo e(csrf_field()); ?>

            <input type="hidden" name="idIngreso" id="idIngreso" value="-1">
          </form>


    </body>
  

   
</html><?php /**PATH /var/www/CoffeeToGo/resources/views/layout/master.blade.php ENDPATH**/ ?>