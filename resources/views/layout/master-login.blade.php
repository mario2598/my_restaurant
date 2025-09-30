<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'MI RESTAURANTE') }}</title>
        <meta name="keywords" content="{{ config('app.name', 'MI RESTAURANTE') }}">
        <meta name="description" content="@yield('meta_description', config('app.slogan'))">
        <meta name="author" content="@yield('meta_author', config('app.name'))">
        <!-- Favicon -->
        
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type='image/x-icon' href="{{asset("assets/images/coffeeMini.png")}}">

        <link rel="stylesheet" href="{{asset("assets/css/app.min.css")}}">
        <!-- Template CSS -->
        <link rel="stylesheet" href="{{asset("assets/css/style.css")}}">
        <link rel="stylesheet" href="{{asset("assets/css/components.css")}}">
        <!-- Custom style CSS -->
        <link rel="stylesheet" href="{{asset("assets/css/custom.css")}}">
        <link rel="stylesheet" href="{{asset("assets/bundles/izitoast/css/iziToast.min.css")}}">
    </head>

    <body>
        <!-- Begin page -->
        <div class="loader"></div>
        <div id="app">
            <div class="main-wrapper main-wrapper-1">
                
                <!-- ============================================================== -->
                <!-- Start right Content here -->
                <!-- ============================================================== -->
        
                @yield('content')

                <!-- ============================================================== -->
                <!-- End Right content here -->
                <!-- ============================================================== -->

            </div>
        </div>
        @include('layout.msjAlerta')
        <!-- General JS Scripts -->
        <script src="{{asset("assets/js/app.min.js")}}"></script>
        <!-- JS Libraies -->
        <!-- Page Specific JS File -->
        <script src="{{asset("assets/js/page/index.js")}}"></script>
        <!-- Template JS File -->
        <script src="{{asset("assets/js/scripts.js")}}"></script>
        <!-- Custom JS File -->
        <script src="{{asset("assets/js/custom.js")}}"></script>
        <script src="{{asset("assets/bundles/izitoast/js/iziToast.min.js")}}"></script>
    </body>
  

   
</html>