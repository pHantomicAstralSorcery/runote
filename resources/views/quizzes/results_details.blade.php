@extends('welcome')
@section('title', 'Подробные результаты')
@section('content')
<div class="container">
    <a href="{{ route('quizzes.completedQuizzes') }}" class="btn btn-sm btn-secondary my-3">← К пройденным тестам</a>
    <h1 class="my-4 text-center">Подробные результаты для "{{ $quiz->title }}"</h1>

    <!-- Отображение общего балла -->
    <div class="alert alert-info text-center mb-4">
        <strong>Общий балл:</strong> {{ $quizUser->pivot->score }}
    </div>

    <!-- Цикл по вопросам с пагинацией -->
    @if($questions->isEmpty())
        <div class="alert alert-warning text-center">
            В этом тесте нет вопросов.
        </div>
    @else
        @foreach($questions as $question)
            <div class="card mt-3">
                <div class="card-body">
                    <!-- Заголовок вопроса и его баллы -->
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title">{{ $question->question_text }}</h5>
                        <span class="badge bg-primary">Балл: {{ $question->question_points }}</span>
                    </div>

                    <!-- Ответы на вопрос -->
                    @if($question->question_type == 'single' || $question->question_type == 'multiple')
                        @php
                            // Получаем ответ пользователя для этого вопроса
                            $userAnswer = $answers->where('question_id', $question->id)->first();
                            $userAnswerOptions = $userAnswer ? json_decode($userAnswer->answer) : [];
                        @endphp

                        @foreach($question->options as $option)
                            <div class="form-check">
                                <input class="form-check-input" type="{{ $question->question_type == 'single' ? 'radio' : 'checkbox' }}" disabled 
                                    {{ in_array($option->id, $userAnswerOptions) ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    {{ $option->option_text }}
                                </label>

                                @if(in_array($option->id, $userAnswerOptions)) <!-- Проверяем, был ли этот вариант выбран -->
                                    <div>
                                        @if($option->is_correct)
                                            <span class="badge bg-success">Правильно</span>
                                        @else
                                            <span class="badge bg-danger">Неправильно</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @elseif($question->question_type == 'text')
                        @php
                            // Получаем правильный ответ
                            $correctOption = $question->options->where('is_correct', true)->first();
                            // Получаем ответ пользователя для текстового вопроса
                            $userTextAnswer = $answers->where('question_id', $question->id)->first()?->answer ?? 'Не предоставлен';
                            
                            // Проверяем, совпадает ли ответ пользователя с правильным ответом
                            $isCorrect = strtolower(trim($userTextAnswer)) === strtolower(trim($correctOption?->option_text ?? ''));
                        @endphp

                        <!-- Выводим ответ пользователя и его статус -->
                        <p>
                            <strong class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }}">
                                Ваш ответ:
                            </strong> 
                            {{ $userTextAnswer }}
                        </p>
                        <!-- Выводим правильный ответ -->
                        <p>
                            <strong class="badge bg-primary">Правильный ответ:</strong> 
                            {{ $correctOption ? $correctOption->option_text : 'Не указан' }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Пагинация -->
        <div class="d-flex justify-content-center mt-3">
            {{ $questions->links('components.pagination') }} 
        </div>
    @endif
</div>
@endsection