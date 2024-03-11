<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GYM BAR</title>
    <meta name="keywords" content="Elevate your gym experience">
    <meta name="description" content="@yield('meta_description', config('app.name'))">
    <meta name="author" content="@yield('meta_author', config('app.name'))">
    <!-- Favicon -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type='image/x-icon' href="{{ asset('assets/images/coffeeMini.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/izitoast/css/iziToast.min.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <!-- Custom style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/space.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/ionicons/css/ionicons.min.css') }}">
    <script type="module" src="{{ asset('js/app.js') }}"></script>
    <link id="ligthStyle" href="{{ asset('assets/bundles/lightgallery/dist/css/lightgallery.css') }}" rel="stylesheet">
    @yield('styles')
</head>

<body style="sidebar-gone">
    <input type="hidden" value="{{url('/')}}" id="base_path">
    <!-- Begin page -->
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            @include('layout.topbarUsuarioE')
            @yield('content')
            @yield('popup')
            @include('layout.footer')
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('assets/bundles/sweetalert/sweetalert.min.js') }}"></script>
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
    <script id="script1" src="{{ asset('assets/bundles/lightgallery/dist/js/lightgallery-all.js') }}"></script>
    <!-- Page Specific JS File -->
    <script id="script2" src="{{ asset('assets/js/page/light-gallery.js') }}"></script>
    @yield('script')



</body>



</html>
