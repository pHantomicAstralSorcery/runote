@extends('welcome')

@section('title', 'О нас')

@section('content')
<div class="container my-5">
  <!-- Заголовок страницы -->
  <div class="row">
    <div class="col text-center">
      <h1 class="display-4">О RUNOTE</h1>
      <p class="lead">Мы создаем будущее образования.</p>
    </div>
  </div>

  <!-- Блок "Наша миссия" (текст - картинка) -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6">
      <h2>Наша миссия</h2>
      <p>RUNOTE стремится предоставить удобную платформу для создания электронных рабочих тетрадей и прохождения тестов, которая поможет каждому проверить свои знания и улучшить навыки. Мы уверены, что цифре будущее, а мы поможем вам это будущее создать!</p>
    </div>
    <div class="col-md-6">
            <img src="{{ asset('assets/img/static_img/our-mission.png')}}" class="d-block w-100" alt="Наша миссия">
    </div>
  </div>

  <!-- Блок "Наша команда" (картинка - текст) -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6 order-md-2"> {{-- На ПК будет первой колонкой (слева) --}}
      <h2>Наша команда</h2>
      <p>Мы — небольшая команда профессионалов, объединенных страстью к инновациям в образовании. Наша цель — сделать процесс обучения интересным, эффективным и, конечно, немного забавным. Тетради и тесты <3</p>
    </div>
    <div class="col-md-6 order-md-1"> {{-- На ПК будет второй колонкой (справа) --}}
            <img src="{{ asset('assets/img/static_img/our-team.png')}}" class="d-block w-100" alt="Наша команда">
    </div>
  </div>
</div>
@endsection
