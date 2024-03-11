<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Coffee To Go</title>
        <meta name="keywords" content="The coffee experience">
        <meta name="description" content="@yield('meta_description', config('app.name'))">
        <meta name="author" content="@yield('meta_author', config('app.name'))">
        <!-- Favicon -->
        
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type='image/x-icon' href="{{asset("assets/images/coffeeMini.png")}}">

        <link rel="stylesheet" href="{{asset("assets/css/app.min.css")}}">
        <link rel="stylesheet" href="{{asset("assets/bundles/izitoast/css/iziToast.min.css")}}">

        <!-- Template CSS -->
        <link rel="stylesheet" href="{{asset("assets/css/style.css")}}">
        <link rel="stylesheet" href="{{asset("assets/css/components.css")}}">
        <!-- Custom style CSS -->
        <link rel="stylesheet" href="{{asset("assets/css/custom.css")}}">
        <link rel="stylesheet" href="{{asset("assets/css/space.css")}}">
        <link rel="stylesheet" href="{{asset("assets/bundles/ionicons/css/ionicons.min.css")}}">
        <script type="module" src="{{ asset('js/app.js') }}"></script>
        @yield('styles')

        
    
    </head>

    <body style="sidebar-gone">
    <input type="hidden" value="{{url('/')}}" id="base_path">
    <input type="hidden" value="{{$data['panel_configuraciones']->color_fondo ?? 1}}" id="cp_color_fondo">
    <input type="hidden" value="{{$data['panel_configuraciones']->color_sidebar ?? 1}}" id="cp_color_sidebar">
    <input type="hidden" value="{{$data['panel_configuraciones']->color_tema ?? 'white'}}" id="cp_color_tema">
    <input type="hidden" value="{{$data['panel_configuraciones']->mini_sidebar ?? 1}}" id="cp_mini_sidebar">
    <input type="hidden" value="{{$data['panel_configuraciones']->sticky_topbar ?? 1}}" id="cp_sticky_topbar">
   
        <!-- Begin page -->
        <div class="loader"></div>
            <div id="app">
                <div class="main-wrapper main-wrapper-1" >
                    @include('layout.topbar')

                    <!-- ============================================================== -->
                    <!-- Start right Content here -->
                    <!-- ============================================================== -->
            
                    @yield('content')

                    <!-- ============================================================== -->
                    <!-- End Right content here -->
                    <!-- ============================================================== -->
                    @include('layout.configbar')
                    @yield('popup')
                    @include('layout.footer')
                </div>
        </div>
        
        @include('layout.msjAlerta')
        <!-- General JS Scripts -->
        <script src="{{asset("assets/bundles/sweetalert/sweetalert.min.js")}}"></script>
        <script src="{{asset("assets/js/app.min.js")}}"></script>
        <!-- Page Specific JS File -->
        <script src="{{asset("assets/js/page/index.js")}}"></script>
        <!-- Template JS File -->
        <script src="{{asset("assets/js/scripts.js")}}"></script>
        <!-- Custom JS File -->
        <script src="{{asset("assets/js/custom.js")}}"></script>
        <script src="{{asset("assets/bundles/izitoast/js/iziToast.min.js")}}"></script>
        <script src="{{asset("assets/js/space.js")}}"></script>
        <script src="{{asset("assets/js/page/ion-icons.js")}}"></script>
        @yield('script')

        <form id="formGastoEditar" action="{{URL::to('gastos/editar')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idGastoEditar" id="idGastoEditar" value="-1">
          </form>

          <form id="formCancelarMovimiento" action="{{URL::to('bodega/movimiento/cancelar')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idMovimientoCancelar" id="idMovimientoCancelar" value="-1">
            <input type="hidden" name="detalleMovimientoCancelar" id="detalleMovimientoCancelar" value="-1">
          </form>

          <form id="formVerMovimiento" action="{{URL::to('bodega/inventario/movimiento')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idMov" id="idMov" value="-1">
          </form>

          <form id="formGastoEliminar" action="{{URL::to('gastos/sinaprobar/eliminar')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idGastoEliminar" id="idGastoEliminar" value="-1">
          </form>

          <form id="formGastoAdminEliminar" action="{{URL::to('gastos/eliminar')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idGastoEliminar" id="idGastoAdminEliminar" value="-1">
          </form>
          
          <form id="formGastoRechazar" action="{{URL::to('gastos/rechazar')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idGastoRechazar" id="idGastoRechazar" value="-1">
          </form>

          <form id="formGasto" action="{{URL::to('gastos/gasto')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idGasto" id="idGasto" value="-1">
          </form>

          <form id="formIngreso" action="{{URL::to('ingresos/ingreso')}}" style="display: none"  method="POST">
            {{csrf_field()}}
            <input type="hidden" name="idIngreso" id="idIngreso" value="-1">
          </form>


    </body>
  

   
</html>