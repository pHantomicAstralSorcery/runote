@extends('welcome')
@section('title', 'Пройденные тесты')
@section('content')
<div class="container">
    <h1 class="text-center my-4">Пройденные тесты</h1>
    @if($attempts->isEmpty())
<div class="d-flex justify-content-center">
        <a href="{{ route('quizzes.index') }}" class="btn btn-success mb-3 w-25">Пройти тест</a>
    </div>
        <div class="alert alert-info mt-3">
            Вы еще не прошли ни одного теста.
        </div>
    @else
    

    <!-- Форма фильтрации -->
    <form action="{{ route('quizzes.completedQuizzes') }}" method="GET" class="mb-3">
        <div class="row g-3 align-items-center">
            <!-- Поиск по названию теста -->
            <div class="col-auto">
                @component('components.input', [
                    'type' => 'text',
                    'id' => 'search',
                    'name' => 'search',
                    'label' => 'Название теста:',
                    'placeholder' => '',
                    'value' => request('search'), // Сохраняем значение поиска
                ]) @endcomponent
            </div>

            <!-- Фильтр по минимальному баллу -->
            <div class="col-auto">
                @component('components.input', [
                    'type' => 'number',
                    'id' => 'min_score',
                    'name' => 'min_score',
                    'label' => 'Минимальный балл:',
                    'value' => request('min_score'), // Сохраняем значение минимума
                    'min' => 0,
                    'step' => 0.01,
                ]) @endcomponent
            </div>

            <!-- Фильтр по максимальному баллу -->
            <div class="col-auto">
                @component('components.input', [
                    'type' => 'number',
                    'id' => 'max_score',
                    'name' => 'max_score',
                    'label' => 'Максимальный балл:',
                    'value' => request('max_score'), // Сохраняем значение максимума
                    'min' => 0,
                    'step' => 0.01,
                ]) @endcomponent
            </div>

            <!-- Фильтр по дате начала -->
            <div class="col-auto">
                @component('components.input', [
                    'type' => 'date',
                    'id' => 'from_date',
                    'name' => 'from_date',
                    'label' => 'От даты:',
                    'value' => request('from_date'), // Сохраняем значение от даты
                ]) @endcomponent
            </div>

            <!-- Фильтр по дате завершения -->
            <div class="col-auto">
                @component('components.input', [
                    'type' => 'date',
                    'id' => 'to_date',
                    'name' => 'to_date',
                    'label' => 'До даты:',
                    'value' => request('to_date'), // Сохраняем значение до даты
                ]) @endcomponent
            </div>

            <!-- Кнопка применения фильтров -->
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3">Применить</button>
            </div>
        </div>
    </form>


        <div class="table-responsive">
            <table class="table table-striped table-sm">
<thead>
    <tr>
        <!-- Сортировка по названию теста -->
        <th class="text-center">
            <a href="{{ route('quizzes.completedQuizzes', array_merge(request()->query(), ['sort' => 'title', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                Название
                @if(request('sort') === 'title')
                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                @endif
            </a>
        </th>

        <!-- Сортировка по номеру попытки -->
        <th class="text-center">
            <a href="{{ route('quizzes.completedQuizzes', array_merge(request()->query(), ['sort' => 'pivot_attempt_number', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                Попытка
                @if(request('sort') === 'pivot_attempt_number')
                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                @endif
            </a>
        </th>

        <!-- Сортировка по баллам -->
        <th class="text-center">
            <a href="{{ route('quizzes.completedQuizzes', array_merge(request()->query(), ['sort' => 'pivot_score', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                Балл
                @if(request('sort') === 'pivot_score')
                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                @endif
            </a>
        </th>

        <!-- Сортировка по времени начала -->
        <th class="text-center">
            <a href="{{ route('quizzes.completedQuizzes', array_merge(request()->query(), ['sort' => 'pivot_started_at', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                Время начала
                @if(request('sort') === 'pivot_started_at')
                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                @endif
            </a>
        </th>

        <!-- Сортировка по затраченному времени -->
        <th class="text-center">
            <a href="{{ route('quizzes.completedQuizzes', array_merge(request()->query(), ['sort' => 'time_spent', 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                Затраченное время
                @if(request('sort') === 'time_spent')
                    {{ request('order') === 'asc' ? '↑' : '↓' }}
                @endif
            </a>
        </th>
        <th></th>
    </tr>
</thead>
                <tbody>
                    @foreach($attempts as $attempt) 
                        <tr>
                            <td class="text-center">{{ $attempt->title }}</td>
                            <td class="text-center">
                                @if($attempt->attempt_limit)
                                    {{ $attempt->pivot->attempt_number }} / {{ $attempt->attempt_limit }}
                                @else
                                    {{ $attempt->pivot->attempt_number }}
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $attempt->pivot->score }} / {{ $attempt->questions()->sum('question_points') }}
                            </td>
                       <td class="text-center">{{ $attempt->pivot->started_at ?? 'Не начато' }}</td> 
<td class="text-center">
                                @if ($attempt->pivot->started_at && $attempt->pivot->completed_at)
                                    @php
        $start = \Carbon\Carbon::parse($attempt->pivot->started_at);
        $end = \Carbon\Carbon::parse($attempt->pivot->completed_at);
        $difference = $end->diff($start);
    @endphp
    {{ $difference->h }} ч {{ $difference->i }} мин {{ $difference->s }} сек
                                @else
                                    Не завершено
                                @endif
                            </td> 
                            <td class="text-center">
                                <a href="{{ route('quizzes.results.details', ['quiz' => $attempt->id, 'attempt_number' => $attempt->pivot->attempt_number]) }}" class="btn btn-outline-primary btn-sm">Подробнее →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <div class="d-flex justify-content-center mt-4">
            {{ $attempts->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection