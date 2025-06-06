@extends('welcome')
@section('title', 'Вход')
@section('content')
    <div class="container vh-100">
        <div class="row vh-100 align-items-center">
            <div class="col"></div>
            <div class="col-6">
                <form method="post" action="">
                    @csrf
                    <h2 class="text-center my-5">Вход</h2>
@if (session()->has('authError'))
    <div class="alert alert-danger">
        Неправильный логин или пароль
    </div>
@endif

                    @component('components.input', ['type' => 'text', 'id' => 'login', 'name' => 'login', 'label' => 'Логин'])@endcomponent
                    @component('components.input', ['type' => 'password', 'id' => 'password', 'name' => 'password', 'label' => 'Пароль'])@endcomponent
                    <button type="submit" class="btn btn-primary form-control">Войти</button>
                </form>
            </div>
            <div class="col"></div>
        </div>
    </div>
@endsection
