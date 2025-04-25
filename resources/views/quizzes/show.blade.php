@extends('welcome')
@section('title', 'Тест '. $quiz->title)
@section('content')
    <div class="container">
        <a href="{{ route('quizzes.index') }}" class="btn btn-sm btn-secondary my-3">← Назад к тестам</a>
<div class="row">
<div class="col"></div>
<div class="col">
 <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center">{{ $quiz->title }}</h2>
                <p class="card-text">{{ $quiz->description }}</p>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Тип доступа: {{ $quiz->access_type }}</li>
                    @if($quiz->time_limit_type == 'custom')
                        <li class="list-group-item">Ограничение по времени: {{ $quiz->time_limit }} минут</li>
                    @endif
                    @if($quiz->attempt_limit_type == 'custom')
                        <li class="list-group-item">Ограничение по попыткам: {{ $quiz->attempt_limit }} р.</li>
                    @endif
<li class="list-group-item">Максимальный балл: {{ $quiz->questions()->sum('question_points') }}</li>
                    <li class="list-group-item">Создано: {{ $quiz->user->login ?? 'Неизвестный автор' }}</li>
                </ul>

                <a href="{{ route('quizzes.take', ['quiz' => $quiz->id]) }}" class="btn btn-success mt-3 form-control">▷ Пройти</a>
            </div>
        </div>
 </div>
<div class="col"></div>
 </div>
       
    </div>
@endsection
