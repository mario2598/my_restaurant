 <!--Alerta de error general -->
 <?php if(session('error') != null): ?>
 <script>
   window.addEventListener("load", initialice, false);
   function initialice() {
     iziToast.error({
       title: "<?php echo e(session('error')['titulo']); ?>",
       message: "<?php echo e(session('error')['descripcion']); ?>",
       position: 'topRight'
     });
   }
 </script>
  <?php session(['error'=>null]); ?>

<?php endif; ?>
<!--FIN Alerta de error general -->

<!--Alerta de error general -->
<?php if(session('success') != null): ?>
<script>
  window.addEventListener("load", initialice, false);
  function initialice() {
    iziToast.success({
      title: "<?php echo e(session('success')['titulo']); ?>",
      message: "<?php echo e(session('success')['descripcion']); ?>",
      position: 'topRight'
    });
  }
</script>
 <?php session(['success'=>null]); ?>

<?php endif; ?>
<!--FIN Alerta de error general -->

<!--Alerta de info general -->

<?php if(session('info') != null): ?>
<script>
  window.addEventListener("load", initialice, false);
  function initialice() {
    iziToast.show({
      title: "<?php echo e(session('info')['titulo']); ?>",
      message: "<?php echo e(session('info')['descripcion']); ?>",
      position: 'topCenter'
    });
  }
</script>
 <?php session(['info'=>null]); ?>

<?php endif; ?>
<!--FIN Alerta de error general --><?php /**PATH D:\Proyectos\2023\Laravel\Space Rest\resources\views/layout/msjAlerta.blade.php ENDPATH**/ ?>