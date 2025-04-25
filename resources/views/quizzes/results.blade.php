@extends('welcome')
@section('title', 'Результаты')
@section('content')
    <div class="container">
        <a href="{{ route('quizzes.index') }}" class="btn btn-sm btn-secondary my-3">← Назад к тестам</a>
        <h1 class="my-4 text-center">Результаты для "{{ $quiz->title }}"</h1>
    @if(session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif
        <div class="alert alert-success">
            <p><strong>Ваш балл:</strong> {{ $score }}</p>
        </div>

        <div class="d-flex justify-content-center">
<a href="{{ route('quizzes.results.details', ['quiz' => $quiz->id, 'attempt_number' => $attemptNumber]) }}" class="btn btn-primary w-25">Подробнее →</a>
        </div>
    </div>
@endsection
