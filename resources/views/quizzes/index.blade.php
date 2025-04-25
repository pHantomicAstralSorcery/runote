@extends('welcome')
@section('title', 'Список тестов')
@section('content')
<div class="container">
    <h1 class="text-center my-4">Доступные тесты</h1>
        @if($quizzes->isEmpty())
<div class="d-flex justify-content-center">
            <a href="{{ route('quizzes.create') }}" class="btn btn-outline-success w-25">+ Создать тест</a>
        </div>
        <div class="alert alert-info text-center mt-3">
            На данный момент тесты отсутствуют.
        </div>
    @else
  <!-- Форма поиска -->
<form method="GET" action="{{ route('quizzes.index') }}" class="row align-items-center mb-3">
    <div class="col-md-6">
        @component('components.input', [
            'type'        => 'text',
            'id'          => 'search',
            'name'        => 'search',
            'label'       => 'Название или автор',
            'placeholder' => '',
            'value'       => request('search')
        ])
        @endcomponent
    </div>
    <div class="col-md-1 d-flex w-50 justify-content-between mb-3">
        <button type="submit" class="btn btn-primary me-2" style="width:10rem;">Найти</button>
        <a href="{{ route('quizzes.create') }}" class="btn btn-outline-success btn-sm mt-1">+ Создать тест</a>
    </div>
</form>


        <div class="table-responsive mt-3">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <!-- Заголовки со ссылками для сортировки -->
                        <th class="text-center">
                            <a href="{{ route('quizzes.index', array_merge(request()->query(), ['sort' => 'title', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Название
                                @if(request('sort') === 'title')
                                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('quizzes.index', array_merge(request()->query(), ['sort' => 'user.login', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Автор
                                @if(request('sort') === 'user.login')
                                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('quizzes.index', array_merge(request()->query(), ['sort' => 'time_limit', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Время на прохождение
                                @if(request('sort') === 'time_limit')
                                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ route('quizzes.index', array_merge(request()->query(), ['sort' => 'attempt_limit', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                Кол-во попыток
                                @if(request('sort') === 'attempt_limit')
                                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizzes as $quiz)
                        <tr>
                            <td class="text-center" style="max-width: 15rem;">{{ $quiz->title }}</td>
                            <td class="text-center" style="max-width: 10rem;">{{ $quiz->user->login }}</td>
                            <td class="text-center">
                                @if($quiz->time_limit)
                                    {{ $quiz->time_limit }} мин.
                                @else
                                    <span class="text-secondary">Без ограничения</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($quiz->attempt_limit)
                                    {{ $quiz->attempt_limit }}
                                @else
                                    <span class="text-secondary">Без ограничения</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('quizzes.show', ['quiz' => $quiz->id]) }}" class="btn btn-success btn-sm w-75">Перейти →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Пагинация -->
        <div class="d-flex justify-content-center mt-4">
            {{ $quizzes->links('components.pagination') }}
        </div>
    @endif
</div>

<!-- Модальное окно ошибки -->
@if(session('error'))
    @component('components.modal', [
        'id' => "errorModal",
        'title' => "Ошибка",
        'body' => session('error'),
        'dismissName' => "Закрыть",
        'needForm' => 'false',
        'needExtForm' => 'false',
        'buttonClass' => "btn btn-secondary",
        'needButton' => 'false',
    ])
    @endcomponent

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        });
    </script>
@endif
@endsection
