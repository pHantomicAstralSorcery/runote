@extends('welcome')
@section('title', 'Статистика теста ' . $quiz->title)
@section('content')
<div class="container">
    <h1 class="text-center my-4">Статистика теста "{{ $quiz->title }}"</h1>

    <form action="{{ route('quizzes.statistics', $quiz->id) }}" method="GET" class="mb-3">
        <div class="row g-3 align-items-center">
            <!-- Поиск по логину -->
            <div class="col-auto">
@component('components.input', [
    'type' => 'text',
    'id' => 'question_text',
    'name' => 'question_text',
    'label' => 'Логин пользователя',
    'placeholder' => '',
    'value' => request('search') ,
]) @endcomponent
            </div>

            <!-- Фильтр по баллам -->
            <div class="col-auto">
    @component('components.input', [
        'type' => 'number',
        'id' => 'min_score',
        'name' => 'min_score',
        'label' => 'Минимальный балл:',
        'value' => request('max_score') ,
        'min' => 1,
    ]) @endcomponent
            </div>
            <div class="col-auto">
    @component('components.input', [
        'type' => 'number',
        'id' => 'max_score',
        'name' => 'max_score',
        'label' => 'Максимальный балл:',
        'value' => request('max_score'),
        'min' => 1,
    ]) @endcomponent
            </div>

            <!-- Фильтр по дате начала -->
            <div class="col-auto">
@component('components.input', [
    'type' => 'date',
    'id' => 'from_date',
    'name' => 'from_date',
    'label' => 'От даты:',
    'value' => request('from_date'),
]) @endcomponent
            </div>
            <div class="col-auto">
@component('components.input', [
    'type' => 'date',
    'id' => 'to_date',
    'name' => 'to_date',
    'label' => 'До даты:',
    'value' => request('to_date'),
]) @endcomponent
            </div>

            <!-- Кнопка применения фильтров -->
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3">Применить</button>
            </div>
        </div>
    </form>

    @if($attempts->isEmpty())
        <div class="alert alert-info mt-3">
            Никто еще не прошел этот тест.
        </div>
    @else

        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
@component('components.sort-column', ['route' => 'quizzes.statistics', 'custom_req' => ['quiz' => $quiz->id], 'column' => 'users.login', 'label' => 'Пользователь'])@endcomponent
@component('components.sort-column', ['route' => 'quizzes.statistics', 'custom_req' => ['quiz' => $quiz->id], 'column' => 'pivot_attempt_number', 'label' => 'Попытка'])@endcomponent
@component('components.sort-column', ['route' => 'quizzes.statistics', 'custom_req' => ['quiz' => $quiz->id], 'column' => 'pivot_score', 'label' => 'Балл'])@endcomponent
@component('components.sort-column', ['route' => 'quizzes.statistics', 'custom_req' => ['quiz' => $quiz->id], 'column' => 'pivot_started_at', 'label' => 'Время начала'])@endcomponent
@component('components.sort-column', ['route' => 'quizzes.statistics', 'custom_req' => ['quiz' => $quiz->id], 'column' => 'time_spent', 'label' => 'Затраченное время'])@endcomponent
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                        <tr>
                            <td class="text-center">{{ $attempt->login ?? 'Аноним' }}</td> 
                            <td class="text-center">{{ $attempt->pivot->attempt_number }}</td> 
                            <td class="text-center">{{ $attempt->pivot->score }} / {{ $quiz->questions()->sum('question_points') }}</td> 
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
                                <a href="{{ route('quizzes.results.details', ['quiz' => $quiz->id, 'attempt_number' => $attempt->pivot->attempt_number]) }}" class="btn btn-outline-primary btn-sm">Подробнее →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <div class="d-flex justify-content-center mt-3">
            {{ $attempts->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection