<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Spark Admin - Premium Bootstrap 5 Admin Dashboard')</title>

    {{-- SEO --}}
    <meta name="description" content="@yield('meta_description', 'Spark Admin - Premium Bootstrap 5 Admin Dashboard Template')">
    <meta name="author" content="Spark Admin Team">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.ico') }}">

    {{-- Third-Party CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/apexcharts/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

    {{-- Main Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    @stack('styles')
</head>

<body>

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Main Content Area --}}
    <div class="main-wrapper">

        {{-- Top Navbar --}}
        @include('partials.topbar')

        {{-- Page Content --}}
        <div class="page-content">
            @yield('content')
        </div>

        {{-- Footer --}}
        @include('partials.footer')

    </div>
    {{-- END: Main Content Area --}}

    {{-- Scripts --}}
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('scripts')

</body>

</html>
