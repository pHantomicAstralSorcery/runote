@extends('welcome')

@section('title', 'Статистика по тетради: ' . $notebook->title)

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <a href="{{ route('notebooks.edit', $notebook) }}#settings-tab" class="btn btn-outline-secondary mb-2 mb-md-0">
            <i class="bi bi-arrow-left"></i> Назад к редактированию
        </a>
        <h1 class="text-center my-0 mx-auto" style="flex-grow: 1;">
            <span class="fw-light">Статистика:</span> {{ $notebook->title }}
        </h1>
        <div style="min-width: 210px;"></div> {{-- Spacer --}}
    </div>

    {{-- Навигация по табам --}}
    <ul class="nav nav-tabs mb-4" id="statsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overall-progress-tab" data-bs-toggle="tab" data-bs-target="#overall-progress-pane" type="button" role="tab" aria-controls="overall-progress-pane" aria-selected="true">
                <i class="bi bi-pie-chart-fill me-2"></i>Общий прогресс
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="links-list-tab" data-bs-toggle="tab" data-bs-target="#links-list-pane" type="button" role="tab" aria-controls="links-list-pane" aria-selected="false">
                <i class="bi bi-list-task me-2"></i>Список ссылок
            </button>
        </li>
    </ul>

    {{-- Содержимое табов --}}
    <div class="tab-content" id="statsTabsContent">
        {{-- Таб 1: Общий прогресс --}}
        <div class="tab-pane fade show active" id="overall-progress-pane" role="tabpanel" aria-labelledby="overall-progress-tab">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Сводная информация</h4>

                    <div class="row text-center g-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-link-45deg text-primary"></i> Всего ссылок</h5>
                                    <p class="card-text fs-2 fw-bold">{{ $overallStats['totalLinks'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-check-circle-fill text-success"></i> Завершенные</h5>
                                    <p class="card-text fs-2 fw-bold">{{ $overallStats['completedLinks'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-hourglass-split text-warning"></i> В процессе</h5>
                                    <p class="card-text fs-2 fw-bold">{{ $overallStats['uncompletedLinks'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-5">Общий процент выполнения всех заданий</h5>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $overallStats['overallCompletionPercent'] }}%;" aria-valuenow="{{ $overallStats['overallCompletionPercent'] }}" aria-valuemin="0" aria-valuemax="100">
                            <strong class="fs-6">{{ $overallStats['overallCompletionPercent'] }}%</strong>
                        </div>
                    </div>
                    
                    <h5 class="mt-4">Статистика ответов</h5>
                    @php
                        $totalAnswered = $overallStats['totalCorrectAnswers'] + $overallStats['totalIncorrectAnswers'];
                        $correctPercentage = $totalAnswered > 0 ? round(($overallStats['totalCorrectAnswers'] / $totalAnswered) * 100) : 0;
                        $incorrectPercentage = $totalAnswered > 0 ? round(($overallStats['totalIncorrectAnswers'] / $totalAnswered) * 100) : 0;
                        // Исправлено: обращаемся к totalFieldsOverall через $overallStats
                        $unansweredPercentage = $overallStats['totalFieldsOverall'] > 0 ? round(($overallStats['totalUnanswered'] / $overallStats['totalFieldsOverall']) * 100) : 0;
                    @endphp

                    {{-- Прогресс-бар для правильных/неправильных ответов (только отвеченные) --}}
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $correctPercentage }}%;" aria-valuenow="{{ $correctPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            @if($correctPercentage > 5) {{-- Показываем процент, если достаточно места --}}
                                <strong>{{ $correctPercentage }}%</strong>
                            @endif
                        </div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $incorrectPercentage }}%;" aria-valuenow="{{ $incorrectPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            @if($incorrectPercentage > 5) {{-- Показываем процент, если достаточно места --}}
                                <strong>{{ $incorrectPercentage }}%</strong>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1 text-muted">
                        <span><i class="bi bi-check-lg text-success"></i> Правильных: <strong>{{ $overallStats['totalCorrectAnswers'] }}</strong></span>
                        <span><i class="bi bi-x-lg text-danger"></i> Неправильных: <strong>{{ $overallStats['totalIncorrectAnswers'] }}</strong></span>
                        <span>Всего отвечено: <strong>{{ $totalAnswered }}</strong></span>
                    </div>

                    {{-- Дополнительный прогресс-бар для неотвеченных полей --}}
                    <div class="progress mt-3" style="height: 25px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $unansweredPercentage }}%;" aria-valuenow="{{ $unansweredPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            @if($unansweredPercentage > 5)
                                <strong>{{ $unansweredPercentage }}%</strong>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1 text-muted">
                        <span><i class="bi bi-question-lg text-info"></i> Неотвеченных полей: <strong>{{ $overallStats['totalUnanswered'] }}</strong></span>
                        {{-- Исправлено: обращаемся к totalFieldsOverall через $overallStats --}}
                        <span>Всего полей: <strong>{{ $overallStats['totalFieldsOverall'] }}</strong></span>
                    </div>

                </div>
            </div>
        </div>

        {{-- Таб 2: Список ссылок --}}
        <div class="tab-pane fade" id="links-list-pane" role="tabpanel" aria-labelledby="links-list-tab">
            @if(empty($linksData))
                <div class="alert alert-info">Для этой тетради еще не создано ни одной ссылки.</div>
            @else
                <div class="list-group shadow-sm">
                    @foreach($linksData as $data)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <h5 class="mb-1">{{ $data['link']->title }}</h5>
                                    <small class="text-muted">Токен: {{ $data['link']->token }}</small>
                                </div>
                                <div class="col-md-5">
                                    <div class="progress mb-1" style="height: 20px;">
                                        @php
                                            $linkTotalAnswered = $data['stats']['correctAnswers'] + $data['stats']['incorrectAnswers'];
                                            $linkCorrectPercentage = $linkTotalAnswered > 0 ? round(($data['stats']['correctAnswers'] / $linkTotalAnswered) * 100) : 0;
                                            $linkIncorrectPercentage = $linkTotalAnswered > 0 ? round(($data['stats']['incorrectAnswers'] / $linkTotalAnswered) * 100) : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $linkCorrectPercentage }}%;" aria-valuenow="{{ $linkCorrectPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                            @if($linkCorrectPercentage > 10) {{ $linkCorrectPercentage }}% @endif
                                        </div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $linkIncorrectPercentage }}%;" aria-valuenow="{{ $linkIncorrectPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                            @if($linkIncorrectPercentage > 10) {{ $linkIncorrectPercentage }}% @endif
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 small text-muted">
                                        <span><i class="bi bi-check-lg text-success"></i> {{ $data['stats']['correctAnswers'] }}</span>
                                        <span><i class="bi bi-x-lg text-danger"></i> {{ $data['stats']['incorrectAnswers'] }}</span>
                                        <span>Неотвечено: {{ $data['stats']['totalFields'] - $data['stats']['answeredResponses'] }}</span>
                                        <span>Всего: {{ $data['stats']['totalFields'] }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end mt-2 mt-md-0">
                                    <a href="{{ route('student_notebook_instances.progress', $data['instance']->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-search"></i> Подробнее
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Сохранение активного таба в localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const triggerTabList = document.querySelectorAll('#statsTabs button');
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', event => {
                localStorage.setItem('activeStatsTab', triggerEl.getAttribute('id'));
            });
        });

        const activeTabId = localStorage.getItem('activeStatsTab');
        if (activeTabId) {
            const someTabTriggerEl = document.querySelector(`#${activeTabId}`);
            if (someTabTriggerEl) {
                const tab = new bootstrap.Tab(someTabTriggerEl);
                tab.show();
            }
        }
    });
</script>
@endsection
