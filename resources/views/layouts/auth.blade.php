<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Spark Admin')</title>

    {{-- SEO --}}
    <meta name="description" content="@yield('meta_description', 'Spark Admin - Premium Bootstrap 5 Admin Dashboard Template')">
    <meta name="author" content="Spark Admin Team">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.ico') }}">

    {{-- Third-Party CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-icons/bootstrap-icons.css') }}">

    {{-- Main Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    @stack('styles')
</head>
<body>

    @yield('content')

    {{-- Bootstrap JS --}}
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @stack('scripts')

</body>
</html>
