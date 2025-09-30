<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MI RESTAURANTE') }}</title>
    <meta name="keywords" content="{{ config('app.name', 'MI RESTAURANTE') }}">
    <meta name="description" content="@yield('meta_description', config('app.slogan'))">
    <meta name="author" content="@yield('meta_author', config('app.name'))">
    <!-- Favicon -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type='image/x-icon' href="{{ asset('assets/images/coffeeMini.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/izitoast/css/iziToast.min.css') }}">
    <link id="ligthStyle" href="{{ asset('assets/bundles/lightgallery/dist/css/lightgallery.css') }}" rel="stylesheet">
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

    <!-- Custom style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/space.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/ionicons/css/ionicons.min.css') }}">
    <script type="module" src="{{ asset('js/app.js') }}"></script>
    @yield('styles')
</head>

<body style="sidebar-gone" style="background:white;">
    <input type="hidden" value="{{ url('/') }}" id="base_path">

    <!-- Begin page -->
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            @yield('content')
            @yield('popup')
            @include('layout.footer')
        </div>
    </div>

    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <!-- Page Specific JS File -->
    <script src="{{ asset('assets/js/page/index.js') }}"></script>
    <!-- Template JS File -->
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <!-- Custom JS File -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/bundles/izitoast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/js/space.js') }}"></script>
    <script src="{{ asset('assets/js/page/ion-icons.js') }}"></script>
    <!-- JS Libraies -->
    <script id="script1" src="{{ asset('assets/bundles/lightgallery/dist/js/lightgallery-all.js') }}"></script>
    <!-- Page Specific JS File -->
    <script id="script2" src="{{ asset('assets/js/page/light-gallery.js') }}"></script>
    @yield('script')
</body>



</html>
