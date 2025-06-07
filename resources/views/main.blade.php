@extends('welcome')

@section('title', 'Главная')

@section('content')
<div class="container my-5">
  <!-- Заголовок страницы -->
  <div class="row">
    <div class="col text-center">
      <h1 class="display-4">Добро пожаловать в RUNOTE</h1>
      <p class="lead">Инновационная платформа для создания интерактивных электронных рабочих тетрадей и <span class="badge bg-success">NEW!</span> тестов.</p>
      <div class="d-flex flex-md-row flex-column justify-content-center mt-3 ">
      <a href="{{ route('notebooks.index') }}" class="btn btn-primary btn-lg me-md-3 mb-3 mb-md-0">⪼ Создать тетрадь ⪻</a>
      <a href="{{ route('quizzes.index') }}" class="btn btn-success btn-lg">⪼  Начать тестирование ⪻</a>
</div>
    </div>
  </div>

<!-- Блок "Создавайте тесты" -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6">
      <h2>Создавайте тетради</h2>
      <p>С помощью нашей платформы вы легко создадите электронную рабочую тетрадь с разнообразными заданиями и интерактивными элементами. Настраивайте структуру, добавляйте текст, изображения и упражнения — всё для удобного и эффективного обучения!</p>
    </div>
    <div class="col-md-6">
            <img src="{{ asset('assets/img/static_img/create-note.png')}}" class="d-block w-100" alt="Создавайте тетради">
    </div>
  </div>

 <!-- Блок "Проходите тесты" -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6 order-md-2">
      <h2>Проверяйте учеников</h2>
      <p>Отслеживайте прогресс учеников и оценивайте их работу в режиме онлайн. Получайте быстрые и точные результаты, чтобы своевременно выявлять пробелы и помогать в их устранении. Контроль знаний стал проще и удобнее!</p>
    </div>
    <div class="col-md-6 order-md-1">
            <img src="{{ asset('assets/img/static_img/check.png')}}" class="d-block w-100" alt="Проверяйте учеников">
    </div>
  </div>


  <!-- Блок "Создавайте тесты" -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6">
      <h2><span class="badge bg-success">NEW!</span> Создавайте тесты</h2>
      <p>Наша платформа позволяет вам создавать тесты с множеством вопросов, настраиваемыми вариантами ответов и гибкими настройками. Не бойтесь экспериментировать – ошибки превращаются в опыт!</p>
    </div>
    <div class="col-md-6">
      <div id="carouselCreateTests" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#carouselCreateTests" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#carouselCreateTests" data-bs-slide-to="1" aria-label="Slide 2"></button>
          <button type="button" data-bs-target="#carouselCreateTests" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="{{ asset('assets/img/static_img/create-1.png')}}" class="d-block w-100" alt="Создание тестов 1">
          </div>
          <div class="carousel-item">
            <img src="{{ asset('assets/img/static_img/create-2.png')}}" class="d-block w-100" alt="Создание тестов 2">
          </div>
          <div class="carousel-item">
            <img src="{{ asset('assets/img/static_img/create-3.png')}}" class="d-block w-100" alt="Создание тестов 3">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselCreateTests" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Предыдущий</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselCreateTests" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Следующий</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Блок "Проходите тесты" -->
  <div class="row my-5 align-items-center">
    <div class="col-md-6 order-md-2">
      <h2><span class="badge bg-success">NEW!</span> Проходите тесты</h2>
      <p>Проверяйте свои знания, проходите тесты и получайте мгновенную обратную связь. Наш интерфейс интуитивно понятен – даже если вы решили вернуться и изменить ответ, ваш финальный выбор всегда будет засчитан!</p>
    </div>
    <div class="col-md-6 order-md-1">
      <div id="carouselTakeTests" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#carouselTakeTests" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#carouselTakeTests" data-bs-slide-to="1" aria-label="Slide 2"></button>
          <button type="button" data-bs-target="#carouselTakeTests" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="{{ asset('assets/img/static_img/pass-1.png')}}" class="d-block w-100" alt="Прохождение тестов 1">
          </div>
          <div class="carousel-item">
            <img src="{{ asset('assets/img/static_img/pass-2.png')}}" class="d-block w-100" alt="Прохождение тестов 2">
          </div>
          <div class="carousel-item">
            <img src="{{ asset('assets/img/static_img/pass-3.png')}}" class="d-block w-100" alt="Прохождение тестов 3">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselTakeTests" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Предыдущий</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselTakeTests" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Следующий</span>
        </button>
      </div>
    </div>
  </div>
</div>
@endsection
