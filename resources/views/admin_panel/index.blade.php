@extends('welcome')
@section('title', 'Админ панель')
@section('content')
<div class="container mt-4">
<div class="row my-5 align-items-center">
    <h1 class="text-center">Добро пожаловать в панель администратора!</h1>
    <p class="text-center text-muted">
        Здесь вы можете управлять пользователями, тестами и настраивать параметры системы.
    </p>
    <div class="col d-flex justify-content-center">
            <img src="{{ asset('assets/img/static_img/our-team.png')}}" class="w-50" alt="Наша команда">
    </div>
</div>
</div>
@endsection
