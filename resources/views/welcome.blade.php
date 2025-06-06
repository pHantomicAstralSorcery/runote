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
    <link rel="stylesheet" href="{{ asset('assets/img/bootstrap-icons/bootstrap-icons.min.css') }}">
    <script src="{{ asset('assets/js/bootstrap.bundle.js') }}"></script>
    <title>Runote - @yield('title', 'Главная')</title>
</head>
<body>
@include('components.header')
<main>
    @yield('content')
</main>
@include('components.footer')
<div class="notification-container" id="notificationContainer"></div>
</body>
<script>
class Notification {
  static show(message, type = 'info', duration = 3000) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    container.appendChild(notification);
    setTimeout(() => notification.classList.add('show'), 10);

    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => notification.remove(), 300);
    }, duration);
  }
}
</script>
</html>
