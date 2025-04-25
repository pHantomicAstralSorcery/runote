<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="shortcut icon" href="{{ asset('assets/img/favicon/favicon.png') }}" type="image/png">
<link rel="icon" href="{{ asset('assets/img/favicon/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <title>Runote - @yield('title', 'Главная')</title>
</head>
<body>
@include('components.header')
<main>
    @yield('content')
</main>
@include('components.footer')
<script src="{{ asset('assets/js/bootstrap.bundle.js') }}"></script>
@yield('scripts')
</body>
</html>
