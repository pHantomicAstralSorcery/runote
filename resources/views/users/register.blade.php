@extends('welcome')
@section('title', 'Регистрация')
@section('content')
    <div class="container vh-100">
        <div class="row vh-100 align-items-center">
            <div class="col"></div>
            <div class="col-6">
                <form method="post" action="">
                    @csrf
                    <h2 class="text-center my-5">Регистрация</h2>
                    @component('components.input', ['type' => 'text', 'id' => 'login', 'name' => 'login', 'label' => 'Логин'])@endcomponent
                    @component('components.input', ['type' => 'email', 'id' => 'email', 'name' => 'email', 'label' => 'Почта'])@endcomponent
                    @component('components.input', ['type' => 'password', 'id' => 'password', 'name' => 'password', 'label' => 'Пароль'])@endcomponent
                    @component('components.input', ['type' => 'password', 'id' => 'password_repeat', 'name' => 'password_repeat', 'label' => 'Повтор пароля'])@endcomponent

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1" required checked>
                        <label class="form-check-label" for="exampleCheck1">Согласен <a href="{{route('requirement')}}">с условиями</a></label>
                    </div>
                    <button type="submit" class="btn btn-primary form-control">Зарегистрироваться</button>
                </form>
            </div>
            <div class="col"></div>
        </div>
    </div>
@endsection
