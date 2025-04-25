@extends('welcome')
@section('title', 'Список вопросов')
@section('content')
<div class="container">
    <a href="{{ route('quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-secondary my-2">← Вернуться к настройкам теста</a>
    <h2 class="text-center my-4">Список вопросов для "{{ $quiz->title }}"</h2>
@if(session('success'))
    <div class="alert alert-success text-center mt-3">
        {{ session('success') }}
    </div>
@endif

    @if($questions->isEmpty())
<div class="d-flex justify-content-center">
        <a href="{{ route('questions.create', $quiz->id) }}" class="btn btn-outline-success mb-3 w-25">+ Создать вопрос</a>
    </div>
        <div class="alert alert-info text-center mt-3">
            Список вопросов пуст.
        </div>
    @else
<!-- Форма фильтрации -->
    <form method="GET" action="{{ route('questions.index', $quiz->id) }}" class="mb-3">
        <div class="row g-3 align-items-center">
            <!-- Поиск по тексту вопроса -->
            <div class="col-md-3">
                @component('components.input', [
                    'type'        => 'text',
                    'id'          => 'search',
                    'name'        => 'search',
                    'label'       => 'Текст вопроса',
                    'placeholder' => '',
                    'value'       => request('search')
                ])
                @endcomponent
            </div>
            <!-- Выбор: есть баннер или нет -->
            <div class="col-md-3">
                @component('components.select', [
                    'id'                => 'banner',
                    'name'              => 'banner',
                    'label'             => 'Баннер',
                    'placeholder'       => 'Все',
                    'options'           => [
                        'yes' => 'Есть баннер',
                        'no'  => 'Нет баннера'
                    ],
                    'selected'          => request('banner'),
                    'placeholderDisabled' => false  // чтобы можно было выбрать пустое значение
                ])
                @endcomponent
            </div>
            <!-- Выбор типа вопроса -->
            <div class="col-md-3">
                @component('components.select', [
                    'id'                => 'question_type',
                    'name'              => 'question_type',
                    'label'             => 'Тип вопроса',
                    'placeholder'       => 'Все',
                    'options'           => [
                        'single'   => 'Одиночный',
                        'multiple' => 'Множественный',
                        'text'     => 'Текстовый'
                    ],
                    'selected'          => request('question_type'),
                    'placeholderDisabled' => false
                ])
                @endcomponent
            </div>
            <!-- Фильтр по баллам -->
            <div class="col-md-2">
                @component('components.input', [
                    'type'        => 'number',
                    'id'          => 'question_points',
                    'name'        => 'question_points',
                    'label'       => 'Баллы',
                    'placeholder' => '',
                    'value'       => request('question_points'),
                    'min'         => 1
                ])
                @endcomponent
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary mb-3">Применить</button>
            </div>
        </div>
    </form>

<form id="deleteSelectedForm" action="{{ route('questions.deleteSelected', $quiz->id) }}" method="POST" style="display:inline;">
    @csrf
    <div class="d-flex justify-content-end mb-3">
    <span class="mt-1 me-1">Выбрано вопросов: <span id="selectedCount" class="me-4">0</span></span>
            <a href="{{ route('questions.create', $quiz->id) }}" class="btn btn-outline-success btn-sm me-3">+ Создать вопрос</a>
        <button type="button" class="btn btn-outline-danger btn-sm me-3" data-bs-toggle="modal" data-bs-target="#deleteAllModal">⨉ Удалить все вопросы</button>
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSelectedModal" disabled id="deleteSelectedBtn">⨉ Удалить выбранные вопросы</button>

    </div>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
<th class="text-center">
    <a href="{{ route('questions.index', array_merge(request()->query(), ['quiz' => $quiz->id, 'sort' => 'position', 'order' => request('order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
        №
        @if(request('sort')==='position')
            {{ request('order', 'asc')==='asc' ? '↑' : '↓' }}
        @endif
    </a>
</th>

                            <!-- Сортировка по тексту вопроса -->
                            <th class="text-center">
                                <a href="{{ route('questions.index', array_merge(request()->query(), ['quiz' => $quiz->id, 'sort' => 'question_text', 'order' => request('order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                    Текст вопроса
                                    @if(request('sort')==='question_text')
                                        {{ request('order', 'asc')==='asc' ? '↑' : '↓' }}
                                    @endif
                                </a>
                            </th>
                    <th class="text-center">Баннер</th>
                    <th class="text-center">Описание</th>
                    <th class="text-center">Тип вопроса</th>
                            <!-- Сортировка по баллам -->
                            <th class="text-center">
                                <a href="{{ route('questions.index', array_merge(request()->query(), ['quiz' => $quiz->id, 'sort' => 'question_points', 'order' => request('order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                    Баллы
                                    @if(request('sort')==='question_points')
                                        {{ request('order', 'asc')==='asc' ? '↑' : '↓' }}
                                    @endif
                                </a>
                            </th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($questions as $question)
                    <tr>
                         <td><input type="checkbox" name="selected[]" value="{{ $question->id }}" class="selectQuestion"></td>
<td class="text-center">{{ $question->position }}</td>

                            <td  class="truncate text-center" style="max-width: 1rem;">{{ $question->question_text }}</td>
                            <td class="text-center">@if ($question->question_image)
                                <img src="{{ asset('storage/' . $question->question_image) }}" style="max-width: 6rem; height: 3rem; border: 2px solid grey;">
                            @else <span class="text-secondary">Нет</span> @endif</td>
                            <td class="truncate" style="max-width: 11rem;">{{ $question->question_description ?? 'Нет' }}</td>
                            <td class="text-center">{{ $question->question_type }}</td>
                            <td class="text-center">{{ $question->question_points }}</td>
                            <td class="text-center">
                                <a href="{{ route('questions.edit', [$quiz->id, $question->id]) }}" class="btn btn-secondary btn-sm">⇆ Редактировать</a>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>

<div div class="d-flex justify-content-center mt-4">
@if ($quiz->questions()->count() > 0 && !$quiz->is_published)
            <form action="{{ route('quizzes.publish', [$quiz->id]) }}" method="POST" style="display: inline;" >
                @csrf
                <button type="submit" class="btn btn-primary mt-3">⇱ Опубликовать тест</button>
            </form>
        @endif
</div>
@endif
    <!-- Пагинация -->
    <div class="d-flex justify-content-center mt-4">
        {{ $questions->links('components.pagination') }}
    </div>
</div>

<!-- Модальное окно для подтверждения удаления -->
@component('components.modal', [ 
    'id' => "deleteSelectedModal", 
    'title' => "Подтвердите удаление выбранных вопросов",
    'body' => "Вы уверены, что хотите удалить выбранные вопросы?",
    'dismissName' => "Отмена",
    'needForm' => 'false',
    'needExtForm' => 'true',
    'extFormId' => "deleteSelectedForm",
    'buttonClass' => "btn btn-danger",
    'buttonText' => "⨉ Удалить",
])@endcomponent


@component('components.modal', [ 
    'id' => "deleteAllModal", 
    'title' => "Подтвердите удаление всех вопросов",
    'body' => "Вы уверены, что хотите удалить все вопросы из этого теста? Это действие необратимо!",
    'dismissName' => "Отмена",
    'needForm' => 'true',
    'needExtForm' => 'false',
    'extFormId' => "deleteAllForm", 
    'action' => route('questions.deleteAll', $quiz->id), 
    'formId' => "deleteAllForm",
    'buttonClass' => "btn btn-danger",
    'buttonText' => "⨉ Удалить все",
])@endcomponent


<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.selectQuestion');
    const selectedCount = document.getElementById('selectedCount');
    const deleteButton = document.getElementById('deleteSelectedBtn');

    // Обработчик для чекбокса "Выбрать все"
    selectAllCheckbox.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
    });

    // Обработчик для других чекбоксов
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            selectAllCheckbox.checked = checkboxes.length === Array.from(checkboxes).filter(chk => chk.checked).length;
            updateSelectedCount();
        });
    });

    // Функция для обновления количества выбранных чекбоксов
    function updateSelectedCount() {
        const selected = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
        selectedCount.textContent = selected;

        // Включаем/выключаем кнопку удаления
        deleteButton.disabled = selected === 0;
    }
});
</script>

@endsection
