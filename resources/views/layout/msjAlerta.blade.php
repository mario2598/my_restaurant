 <!--Alerta de error general -->
 @if (session('error') != null)
 <script>
   window.addEventListener("load", initialice, false);
   function initialice() {
     iziToast.error({
       title: "{{session('error')['titulo']}}",
       message: "{{session('error')['descripcion']}}",
       position: 'topRight'
     });
   }
 </script>
  <?php session(['error'=>null]); ?>

@endif
<!--FIN Alerta de error general -->

<!--Alerta de error general -->
@if (session('success') != null)
<script>
  window.addEventListener("load", initialice, false);
  function initialice() {
    iziToast.success({
      title: "{{session('success')['titulo']}}",
      message: "{{session('success')['descripcion']}}",
      position: 'topRight'
    });
  }
</script>
 <?php session(['success'=>null]); ?>

@endif
<!--FIN Alerta de error general -->

<!--Alerta de info general -->

@if (session('info') != null)
<script>
  window.addEventListener("load", initialice, false);
  function initialice() {
    iziToast.show({
      title: "{{session('info')['titulo']}}",
      message: "{{session('info')['descripcion']}}",
      position: 'topCenter'
    });
  }
</script>
 <?php session(['info'=>null]); ?>

@endif
<!--FIN Alerta de error general -->