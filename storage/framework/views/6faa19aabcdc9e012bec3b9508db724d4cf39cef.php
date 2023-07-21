<div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar sticky space-navbar" style="">
        <div class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn"> <i data-feather="align-justify"></i></a></li>
            <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                <i data-feather="maximize"></i>
              </a></li>
        
          </ul>
        </div>
        <ul class="navbar-nav navbar-right">
          
          <li class="dropdown"><a href="#" data-toggle="dropdown"
            class="nav-link notification-toggle nav-link-lg"><i  class="fas fa-user-cog" style="color:#555556"></i>
          </a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
              <div class="dropdown-title">Bienvenido <?php echo e(session('usuario')['nombre'] ?? "Usuario"); ?></div>
               
              <div class="dropdown-divider"></div>
              <a href="<?php echo e(url('login')); ?>" class="dropdown-item has-icon text-danger"> <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
              </a>
            </div>
          </li>
        </ul>
      </nav><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/layout/topbar.blade.php ENDPATH**/ ?>