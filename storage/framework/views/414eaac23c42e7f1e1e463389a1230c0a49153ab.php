<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
        <a href="<?php echo e(url('/')); ?>"> <img title="Nombre empresa" alt="Nombre empresa" src="<?php echo e(asset("assets/images/default-image_small.png")); ?>"
           style="background-color: transparent;border-color: transparent;" class="img-thumbnail"/> <span
            class="logo-name" style="color: transparent;" >Admin</span>
        </a>
      </div>
      <ul class="sidebar-menu">
        <li class="menu-header">Menú</li>
     
        <?php $__currentLoopData = $data['menus'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li class="dropdown">
            <a href="#" class="menu-toggle nav-link has-dropdown"><i class="<?php echo e($m->icon ?? ''); ?>" style="font-size:24px;margin-left:-1px;"></i><span><?php echo e($m->titulo); ?></span></a>
            <ul class="dropdown-menu">
            <?php $__currentLoopData = $m->submenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li><a href="<?php echo e(url($sm->ruta)); ?>"><?php echo e($sm->titulo); ?></a></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
   
      
          </ul>
        </li>
      </ul>
    </aside>
  </div><?php /**PATH /var/www/CoffeeToGo/resources/views/layout/sidebar.blade.php ENDPATH**/ ?>