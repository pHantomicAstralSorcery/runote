@extends('welcome')
@section('title', 'Ваши тесты')
@section('content')
<div class="container">
    <h1 class="text-center my-4">Ваши тесты</h1>
@if(session('success'))
    <div class="alert alert-success text-center mt-3">
        {{ session('success') }}
    </div>
@endif
    @if($quizzes->isEmpty())
<div class="d-flex justify-content-center">
            <a href="{{ route('quizzes.create') }}" class="btn btn-outline-success w-25">+ Создать тест</a>
        </div>
        <div class="alert alert-info text-center mt-3">
            У вас еще нет созданных тестов.
        </div>
@else
      <!-- Форма фильтрации -->
<form method="GET" action="{{ route('quizzes.myQuizzes') }}" class="mb-3">
        <div class="row g-3 align-items-center">
            <!-- Поиск по названию -->
            <div class="col-md-3">
                @component('components.input', [
                    'type'        => 'text',
                    'id'          => 'search',
                    'name'        => 'search',
                    'label'       => 'Название',
                    'placeholder' => '',
                    'value'       => request('search')
                ])
                @endcomponent
            </div>

            <!-- Выбор доступа -->
            <div class="col-md-2">
                @component('components.select', [
                    'id'          => 'access',
                    'name'        => 'access',
                    'label'       => 'Доступ',
                    'placeholder' => 'Все',
                    'options'     => [
                        'open'   => 'open',
                        'link'   => 'link',
                        'hidden' => 'hidden'
                    ],
                    'selected'    => request('access'),
'placeholderDisabled' => false
                ])
                @endcomponent
            </div>

            <!-- Ограничение по времени (тип) -->
            <div class="col-md-2">
                @component('components.select', [
                    'id'          => 'time_limit_type',
                    'name'        => 'time_limit_type',
                    'label'       => 'Огранич. время',
                    'placeholder' => 'Все',
                    'options'     => [
                        'none'   => 'Без ограничения',
                        'custom' => 'Ограничено'
                    ],
                    'selected'    => request('time_limit_type'),
'placeholderDisabled' => false
                ])
                @endcomponent
            </div>

            <!-- Ограничение попыток (тип) -->
            <div class="col-md-2">
                @component('components.select', [
                    'id'          => 'attempt_limit_type',
                    'name'        => 'attempt_limit_type',
                    'label'       => 'Огранич. попытки',
                    'placeholder' => 'Все',
                    'options'     => [
                        'none'   => 'Без ограничения',
                        'custom' => 'Ограничено'
                    ],
                    'selected'    => request('attempt_limit_type'),
'placeholderDisabled' => false
                ])
                @endcomponent
            </div>

            <!-- Фильтр по публикации -->
            <div class="col-md-2">
                @component('components.select', [
                    'id'          => 'is_published',
                    'name'        => 'is_published',
                    'label'       => 'Публикация',
                    'placeholder' => 'Все',
                    'options'     => [
                        '1' => 'Опубликовано',
                        '0' => 'Не опубликовано'
                    ],
                    'selected'    => request('is_published'),
'placeholderDisabled' => false
                ])
                @endcomponent
            </div>

            <div class="col-md-1 mb-3">
                <button type="submit" class="btn btn-primary">Применить</button>
            </div>
        </div>
    </form> 

  <form id="deleteSelectedForm" action="{{ route('quizzes.deleteSelected') }}" method="POST" style="display:inline;">
    @csrf
    <!-- Кнопка удаления выбранных тестов -->
    <div class="d-flex justify-content-end mb-3"> 
    <span class="mt-1 me-1">Выбрано тестов: <span id="selectedCount" class="me-4">0</span></span>
            <a href="{{ route('quizzes.create') }}" class="btn btn-outline-success btn-sm me-3">+ Создать тест</a>         
<button type="button" class="btn btn-outline-danger btn-sm me-3" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                ⨉ Удалить все тесты
            </button>
<button type="button" id="deleteSelectedBtn" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSelectedModal" disabled>
    ⨉ Удалить выбранные тесты
</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
           <thead>
                    <tr>
                    <th ><input type="checkbox" id="selectAll"></th>
                        <!-- ⇱ - сортировка по публикации -->
                        <th class="text-center">
                            <a href="{{ route('quizzes.myQuizzes', array_merge(request()->query(), ['sort' => 'is_published', 'order' => request('order', 'desc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                ⇱
                                @if(request('sort')==='is_published')
                                    {{ request('order', 'desc')==='asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <!-- Название -->
                        <th class="text-center">
                            <a href="{{ route('quizzes.myQuizzes', array_merge(request()->query(), ['sort' => 'title', 'order' => request('order', 'desc')==='asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Название
                                @if(request('sort')==='title')
                                    {{ request('order', 'desc')==='asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <!-- Доступ (без сортировки) -->
                        <th class="text-center">Доступ</th>
                        <!-- Огранич. время (тип) -->
                        <th class="text-center">
                            <a href="{{ route('quizzes.myQuizzes', array_merge(request()->query(), ['sort' => 'time_limit_type', 'order' => request('order', 'desc')==='asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Огранич. время
                                @if(request('sort')==='time_limit_type')
                                    {{ request('order', 'desc')==='asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <!-- Таймер (время) -->
                        <th class="text-center">
                            <a href="{{ route('quizzes.myQuizzes', array_merge(request()->query(), ['sort' => 'time_limit', 'order' => request('order', 'desc')==='asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Таймер
                                @if(request('sort')==='time_limit')
                                    {{ request('order', 'desc')==='asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <!-- Огранич. попытки (тип) -->
                        <th class="text-center">
                            <a href="{{ route('quizzes.myQuizzes', array_merge(request()->query(), ['sort' => 'attempt_limit_type', 'order' => request('order', 'desc')==='asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Огранич. попытки
                                @if(request('sort')==='attempt_limit_type')
                                    {{ request('order', 'desc')==='asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <!-- Кол-во попыток -->
                        <th class="text-center">
                            <a href="{{ route('quizzes.myQuizzes', array_merge(request()->query(), ['sort' => 'attempt_limit', 'order' => request('order', 'desc')==='asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Кол-во попыток
                                @if(request('sort')==='attempt_limit')
                                    {{ request('order', 'desc')==='asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <!-- Действия -->
                        <th></th>
                    </tr>
                </thead>
            <tbody>
                @foreach($quizzes as $quiz)
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="{{ $quiz->id }}" class="selectQuiz"></td>
                        <td class="text-center">@if($quiz->is_published)
                            <span class="text-success">✓</span>
                        @else
                            <span class="text-danger">⨉</span>
                        @endif</td>
                        <td class="truncate text-center" style="max-width: 10rem;">{{ $quiz->title }}</td>
                        <td class="text-center">{{ $quiz->access_type }}</td>
                        <td class="text-center">{{ $quiz->time_limit_type }}</td>
                        <td class="text-center"  style="max-width: 7rem;">@if($quiz->time_limit)
                            {{ $quiz->time_limit }} мин.
                        @else
                            <span class="text-secondary">Без ограничения</span>
                        @endif</td>
                        <td class="text-center">{{ $quiz->attempt_limit_type }}</td>
                        <td class="text-center" style="max-width: 1rem;">@if($quiz->attempt_limit)
                            {{ $quiz->attempt_limit }}
                        @else
                            <span class="text-secondary">Без ограничения</span>
                        @endif</td>
                        <td class="text-center">
                            <a href="{{ route('quizzes.statistics', $quiz->id) }}" class="btn btn-primary btn-sm">⁒ Статистика</a>
                            <a href="{{ route('quizzes.edit', $quiz->id) }}" class="btn btn-secondary btn-sm">⇆ Редактировать</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>

    @endif

<!-- Модальное окно для подтверждения удаления -->
@component('components.modal', [ 
    'id' => "deleteSelectedModal", 
    'title' => "Подтвердите удаление выбранных тестов",
    'body' => "Вы уверены, что хотите удалить выбранные тесты?",
    'dismissName' => "Отмена",
    'needForm' => 'false',
    'needExtForm' => 'true',
    'extFormId' => "deleteSelectedForm",
    'buttonClass' => "btn btn-danger",
    'buttonText' => "⨉ Удалить",
])@endcomponent

<!-- Модальное окно для подтверждения удаления всех вопросов -->
@component('components.modal', [ 
    'id' => "deleteAllModal", 
    'title' => "Подтвердите удаление всех тестов",
    'body' => "Вы уверены, что хотите удалить все тесты? Это действие необратимо!",
    'dismissName' => "Отмена",
    'needForm' => 'true',
    'needExtForm' => 'false',
    'action' => route('quizzes.deleteAll'), 
    'formId' => "deleteAllForm",
    'buttonClass' => "btn btn-danger",
    'buttonText' => "⨉ Удалить все",
])@endcomponent


    <!-- Пагинация -->
    <div class="d-flex justify-content-center mt-4">
        {{ $quizzes->links('components.pagination') }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.selectQuiz');
    const selectedCount = document.getElementById('selectedCount');
    const deleteButton = document.getElementById('deleteSelectedBtn');

    // Обработчик для чекбокса "Выбрать все"
    selectAllCheckbox.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
    });

    // Обработчик для остальных чекбоксов
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