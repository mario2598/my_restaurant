<?php $__env->startSection('content'); ?>
    <section class="section">
        <div class="container mt-5">
            <div class="row">
                <div class="col-sm-12 col-md-6 col-xl-6" >
                    <div class="card-header" style="background-color: transparent;border-color: transparent;">

                        <div class="account-logo">
                            <a href="#"><img src="<?php echo e(asset('assets/images/default-image.png')); ?>"
                                    style="background-color: transparent;border-color: transparent;" class="img-thumbnail"
                                    title="Nombre de empresa" alt="Logo de Empresa"></a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-xl-6" style="margin-top: 60px;">
                    <div class="card-body " style="background-color: transparent;border-color: transparent;">
                        <h4>Inicio de Sesión</h4>
                        <form method="POST" action="<?php echo e(URL::to('ingresar')); ?>" class="needs-validation" autocomplete="off">
                            <?php echo e(csrf_field()); ?>

                            <div class="form-group">
                                <label for="email">Usuario</label>
                                <input type="text" class="form-control" name="user" tabindex="1" required autofocus
                                    maxlength="25">
                                <div class="invalid-feedback">
                                    * Ingresa un usuario valido
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="d-block">
                                    <label for="password" class="control-label">Contraseña</label>
                                    <!--<div class="float-right">
                                      <a href="auth-forgot-password.html" class="text-small">
                                        Forgot Password?
                                      </a>
                                    </div>-->
                                </div>
                                <input id="password" type="password" class="form-control" name="password" tabindex="2"
                                    required minlength="4" maxlength="25">
                                <div class="invalid-feedback">
                                    * Ingresa la contraseña
                                </div>
                            </div>

                            <div class="form-group">
                                <input type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4"
                                    value="Ingresar">

                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="mt-5 text-muted text-center">
                GYM BAR | Elevate your gym experience
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master-login', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/CoffeeToGo/resources/views/login.blade.php ENDPATH**/ ?>