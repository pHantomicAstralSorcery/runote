@extends('welcome')
@section('title', 'Заблокированно')
@section('content')
<div class="container mt-4">
<div class="row my-5 align-items-center">
    <h1 class="text-center">Раздел заблокирован на переделку!</h1>
    <p class="text-center text-muted">
        Следите за обновлениями и новостями в <a href="https://t.me/runote_edu" target="_blank"> телеграмм </a>
    </p>
    <div class="col d-flex justify-content-center">
            <img src="{{ asset('assets/img/static_img/our-team.png')}}" class="w-50" alt="Наша команда">
    </div>
</div>
</div>
@endsection
