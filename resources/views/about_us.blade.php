@extends('welcome')

@section('title', 'О нас')

@section('content')
<div class="container my-5">
  <!-- Заголовок страницы -->
  <div class="row">
    <div class="col text-center">
      <h1 class="display-4">О RUNOTE</h1>
      <p class="lead">Мы создаем будущее тестирования.</p>
    </div>
  </div>

  <!-- Блок "Наша миссия" -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6">
      <h2>Наша миссия</h2>
      <p>RUNOTE стремится предоставить удобную платформу для создания и прохождения тестов, которая поможет каждому проверить свои знания и улучшить навыки. Мы уверены, что даже в мире тестов можно найти место для юмора и инноваций.</p>
    </div>
    <div class="col-md-6">
            <img src="{{ asset('assets/img/static_img/our-mission.png')}}" class="d-block w-100" alt="Наша миссия">
    </div>
  </div>

  <!-- Блок "Наша команда" -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6">
            <img src="{{ asset('assets/img/static_img/our-team.png')}}" class="d-block w-100" alt="Наша команда">
    </div>
    <div class="col-md-6">
      <h2>Наша команда</h2>
      <p>Мы — небольшая команда профессионалов, объединенных страстью к инновациям в образовании. Наша цель — сделать процесс обучения интересным, эффективным и, конечно, немного забавным. Ведь даже тесты могут вдохновлять!</p>
    </div>
  </div>
</div>
@endsection
