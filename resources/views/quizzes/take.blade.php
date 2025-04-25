@extends('welcome')
@section('title', 'Прохождение теста')
@section('content')
    <div class="container">
        <a href="{{ route('quizzes.index') }}" class="btn btn-sm btn-secondary my-3">← Назад к тестам</a>
        <h2 class="my-4 text-center">{{ $quiz->title }}</h2>

        <!-- Отображение количества попыток -->
        @if ($quiz->attempt_limit_type === 'custom')
            <p class="text-center text-muted">Попытка {{ $attemptNumber }} из {{ $quiz->attempt_limit }}</p>
        @endif

        <!-- Таймер, если включено ограничение по времени -->
        @if ($quiz->time_limit_type === 'custom')
            <p class="text-center text-danger fw-bold" id="timer">Осталось: --:--:--</p>
        @endif

        <!-- Ошибки валидации -->
        @if ($errors->any())
            <div class="alert alert-danger my-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Прогресс бар -->
        <div class="progress my-3">
            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                {{ round($progress) }}%
            </div>
        </div>

        <!-- Пагинация вопросов -->
        <div class="mt-4 text-center">
            <nav aria-label="Question pagination">
                <ul class="pagination justify-content-center">
                    @for($i = 0; $i < $totalQuestions; $i++)
                        <li class="page-item {{ $i == $questionIndex ? 'active' : '' }}">
                            <a class="page-link" href="{{ route('quizzes.take', ['quiz' => $quiz->id, 'questionIndex' => $i]) }}">{{ $i + 1 }}</a>
                        </li>
                    @endfor
                </ul>
            </nav>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">{{ $currentQuestion->question_text }}</h5>
                
                <!-- Описание вопроса -->
                @if($currentQuestion->question_description)
                    <p class="text-muted">{{ $currentQuestion->question_description }}</p>
                @endif

                <!-- Изображение вопроса -->
                @if($currentQuestion->question_image)
                    <div class="mb-3 d-flex justify-content-center" style="border: 2px solid #007bff; padding: 10px; border-radius: 8px;">
                        <img src="{{ asset('storage/' . $currentQuestion->question_image) }}" class="img-fluid" alt="Question Image" style="width: 100%; height: 300px;">
                    </div>
                @endif

                <form id="quizForm" action="{{ route('quizzes.submitAnswer', ['quiz' => $quiz->id, 'questionIndex' => $questionIndex]) }}" method="POST">
                    @csrf

                    @if($currentQuestion->question_type == 'single' || $currentQuestion->question_type == 'multiple')
                        @foreach($currentQuestion->options as $option)
                            <div class="form-check">
                                <input class="form-check-input" type="{{ $currentQuestion->question_type == 'single' ? 'radio' : 'checkbox' }}" 
                                       name="answer[]" value="{{ $option->id }}" id="option{{ $option->id }}" 
                                       {{ in_array($option->id, old('answer', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="option{{ $option->id }}">
                                    {{ $option->option_text }}
                                </label>
                            </div>
                        @endforeach
                    @elseif($currentQuestion->question_type == 'text')
                        <div class="mb-3">
                            <textarea class="form-control" name="answer" rows="3" placeholder="Ваш ответ..." required>{{ old('answer') }}</textarea>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <!-- Кнопки для перехода к предыдущему и следующему вопросу -->
                        <div>
                            @if($questionIndex > 0)
                                <a href="{{ route('quizzes.take', ['quiz' => $quiz->id, 'questionIndex' => $questionIndex - 1]) }}" class="btn btn-secondary">← Предыдущий</a>
                            @endif
                        </div>

                        <div>
                            @if($questionIndex < $totalQuestions - 1)
                                <button type="submit" class="btn btn-primary">Следующий →</button>
                            @else
                                <button type="submit" class="btn btn-success">✓ Завершить тест</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@if ($quiz->time_limit_type === 'custom')
<script>
    // Передаём оставшееся время, например, рассчитанное на сервере (в секундах)
    let timeLeft = {{ $timeRemaining }};
    // Флаг защиты от повторного редиректа
    let formSubmitted = false;
    // URL, на который будем редиректить при истечении времени
    let timeoutUrl = "{{ route('quizzes.timeout', ['quiz' => $quiz->id]) }}";

    // Функция форматирования времени (часы:минуты:секунды)
    function formatTime(seconds) {
        let h = Math.floor(seconds / 3600);
        let m = Math.floor((seconds % 3600) / 60);
        let s = seconds % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function updateTimer() {
        if (timeLeft > 0) {
            document.getElementById('timer').innerText = "Осталось: " + formatTime(timeLeft);
            timeLeft--;
            setTimeout(updateTimer, 1000);
        } else {
            if (!formSubmitted) {
                formSubmitted = true;
                // Перенаправляем пользователя на маршрут завершения теста по таймауту
                window.location.href = timeoutUrl;
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        updateTimer();
    });
</script>
@endif

@endsection
