@extends('welcome')
@section('title', 'Редактирование теста '. $quiz->title)
@section('content')
    <div class="container">
<a href="{{ route('quizzes.myQuizzes') }}" class="btn btn-sm btn-secondary my-2">← Вернуться к списку тестов</a>
	    <div class="row">
	    <div class="col"></div>
<div class="col">
	
        <h2 class="text-center my-3">Редактировать тест {{ $quiz->title }}</h2>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
        <form action="{{ route('quizzes.update', $quiz->id) }}" method="POST">
            @csrf
            @method('PUT')

            @component('components.input', [
                'type' => 'text',
                'id' => 'title',
                'name' => 'title',
                'label' => 'Название',
                'placeholder' => ' ',
                'value' => $quiz->title
            ])
            @endcomponent

            @component('components.textarea', [
                'id' => 'description',
                'name' => 'description',
                'label' => 'Описание',
                'placeholder' => 'Введите описание',
                'value' => $quiz->description
            ])
            @endcomponent

            @component('components.select', [
                'id' => 'access_type',
                'name' => 'access_type',
                'label' => 'Тип доступа',
                'placeholder' => 'Выберите тип доступа',
                'options' => ['open' => 'Открытый', 'link' => 'По ссылке', 'hidden' => 'Скрытый'],
                'selected' => $quiz->access_type,
'placeholderDisabled' => true
            ])
            @endcomponent

            @component('components.select', [
                'id' => 'time_limit_type',
                'name' => 'time_limit_type',
                'label' => 'Тип ограничения по времени',
                'placeholder' => 'Выберите тип ограничения по времени',
                'options' => ['none' => 'Без ограничения', 'custom' => 'Пользовательское'],
                'selected' => $quiz->time_limit_type,
'placeholderDisabled' => true
            ])
            @endcomponent

            <div id="time-limit-wrapper" class="{{ old('time_limit_type', $quiz->time_limit_type) === 'custom' ? '' : 'd-none' }}">
                @component('components.input', [
                    'type' => 'number',
                    'id' => 'time_limit',
                    'name' => 'time_limit',
                    'label' => 'Ограничение по времени (минуты)',
                    'placeholder' => ' ',
                    'value' => $quiz->time_limit,
                    'min' => 1,
                ])
                @endcomponent
            </div>
            @component('components.select', [
                'id' => 'attempt_limit_type',
                'name' => 'attempt_limit_type',
                'label' => 'Тип ограничения по попыткам',
                'placeholder' => 'Выберите тип ограничения по попыткам',
                'options' => ['none' => 'Без ограничения', 'custom' => 'Пользовательское'],
                'selected' => $quiz->attempt_limit_type,
'placeholderDisabled' => true
            ])
            @endcomponent

            <div id="attempt-limit-wrapper" class="{{ old('attempt_limit_type', $quiz->attempt_limit_type) === 'custom' ? '' : 'd-none' }}">
                @component('components.input', [
                    'type' => 'number',
                    'id' => 'attempt_limit',
                    'name' => 'attempt_limit',
                    'label' => 'Ограничение по попыткам (разы)',
                    'placeholder' => ' ',
                    'value' => $quiz->attempt_limit,
                    'min' => 1,
                ])
                @endcomponent
            </div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <button type="submit" class="btn btn-primary">↺ Обновить</button>
    <a href="{{ route('questions.index', $quiz->id) }}" class="btn btn-secondary">Редактировать вопросы →</a>
</div>
        </form>
<div div class="d-flex justify-content-center mt-2">
@if ($quiz->questions()->count() > 0 && !$quiz->is_published)
            <form action="{{ route('quizzes.publish', [$quiz->id]) }}" method="POST" style="display: inline;" >
                @csrf
                <button type="submit" class="btn btn-primary" style="width:22rem;">⇱ Опубликовать тест</button>
            </form>
        @endif
</div>
    </div>
<div class="col"></div>
    </div>
    </div>

    <script>
document.getElementById('time_limit_type').addEventListener('change', function () {
    const timeLimitWrapper = document.getElementById('time-limit-wrapper');
    const timeLimitInput = document.getElementById('time_limit');
    if (this.value === 'custom') {
        timeLimitWrapper.classList.remove('d-none');
    } else {
        timeLimitWrapper.classList.add('d-none');
        timeLimitInput.value = '';  // Очищаем значение, если поле скрыто
    }
});
document.getElementById('attempt_limit_type').addEventListener('change', function () {
    const attemptLimitWrapper = document.getElementById('attempt-limit-wrapper');
    const attemptLimitInput = document.getElementById('attempt_limit');
    if (this.value === 'custom') {
        attemptLimitWrapper.classList.remove('d-none');
    } else {
        attemptLimitWrapper.classList.add('d-none');
        attemptLimitInput.value = '';  // Очищаем значение, если поле скрыто
    }
});
    </script>
@endsection
