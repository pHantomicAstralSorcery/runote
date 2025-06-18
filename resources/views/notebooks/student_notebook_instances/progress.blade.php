@extends('welcome')

@section('title', 'Прогресс тетради ученика')

@section('content')
<div class="container my-4">
    <h1 class="text-center mb-4">Прогресс тетради ученика</h1>

    @if($studentNotebookInstance)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-person-fill me-2"></i>Информация об ученике:
                    @if($studentNotebookInstance->namedLink)
                        {{ $studentNotebookInstance->namedLink->title }}
                    @else
                        (Имя не указано)
                    @endif
                </h4>
            </div>
            <div class="card-body">
                <p><strong>ID экземпляра:</strong> {{ $studentNotebookInstance->id }}</p>
                @if($studentNotebookInstance->namedLink)
                    <p><strong>Ссылка:</strong>
                        <a href="{{ route('named_links.view', $studentNotebookInstance->namedLink->token) }}" target="_blank" class="text-decoration-none">
                            {{ route('named_links.view', $studentNotebookInstance->namedLink->token) }}
                        </a>
                    </p>
                @endif
                @if($studentNotebookInstance->snapshot)
                    <p><strong>Версия тетради:</strong> Снимок #{{ $studentNotebookInstance->snapshot->version_number }} (ID: {{ $studentNotebookInstance->snapshot->id }})</p>
                @endif
                <p><strong>Последний доступ:</strong> {{ $lastAccessedAt ? $lastAccessedAt->format('d.m.Y H:i') : 'N/A' }}</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Общая статистика</h4>
            </div>
            <div class="card-body">
                <p><strong>Всего полей-ответов:</strong> {{ $totalFields }}</p>
                <p><strong>Заполнено полей:</strong> {{ $filledFields }}</p>
                <p><strong>Правильных ответов:</strong> {{ $correctFields }}</p>
                <div class="d-flex align-items-center">
                    <p class="mb-0 me-2"><strong>Процент заполнения:</strong></p>
                    <div class="progress flex-grow-1" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: {{ $completionPercent }}%;" aria-valuenow="{{ $completionPercent }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $completionPercent }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><i class="bi bi-list-check me-2"></i>Ответы по полям</h4>
            </div>
            <div class="card-body">
                @if($fieldsWithResponses->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>UUID Поля</th>
                                    <th>Тип Поля</th>
                                    <th>Название</th>
                                    <th>Ответ ученика</th>
                                    <th>Корректность</th>
                                    <th>Действия</th> {{-- Новый столбец для кнопок --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fieldsWithResponses as $index => $item)
                                    <tr data-response-id="{{ $item['student_response_id'] }}" data-field-type="{{ $item['field']->field_type }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td><code>{{ $item['field']->uuid }}</code></td>
                                        <td>{{ $item['field']->field_type }}</td>
                                        <td>{{ $item['field']->label ?? 'Без названия' }}</td>
                                        <td>
                                            @if($item['field']->field_type === 'file')
                                                @if($item['user_input'])
                                                    @php
                                                        $fileData = json_decode($item['user_input'], true);
                                                    @endphp
                                                    @if(json_last_error() === JSON_ERROR_NONE && isset($fileData['url']))
                                                        <div class="p-2 border rounded bg-light text-break" style="max-width: 300px;">
                                                            <a href="{{ asset($fileData['url']) }}" target="_blank" class="text-primary d-block">
                                                                <i class="bi bi-file-earmark"></i> {{ $fileData['name'] ?? 'Файл' }}
                                                            </a>
                                                            <small class="text-muted">{{ round($fileData['size'] / 1024, 2) }} КБ</small>
                                                        </div>
                                                    @else
                                                        {{-- Fallback для старых записей, где просто хранился путь --}}
                                                        <div class="p-2 border rounded bg-light text-break" style="max-width: 300px;">
                                                            <a href="{{ asset($item['user_input']) }}" target="_blank" class="text-primary d-block">
                                                                <i class="bi bi-file-earmark"></i> Скачать файл
                                                            </a>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Не заполнено</span>
                                                @endif
                                            @elseif($item['user_input'])
                                                <div class="p-2 border rounded bg-light text-break" style="max-width: 300px;">
                                                    {{ $item['user_input'] }}
                                                </div>
                                            @else
                                                <span class="text-muted">Не заполнено</span>
                                            @endif
                                        </td>
                                        <td class="correctness-status-cell">
                                            @if(is_null($item['is_correct']))
                                                <span class="badge bg-secondary">Не проверялось</span>
                                            @elseif($item['is_correct'])
                                                <span class="badge bg-success">Верно</span>
                                            @else
                                                <span class="badge bg-danger">Неверно</span>
                                            @endif
                                        </td>
                                        <td class="actions-cell">
                                            {{-- Кнопки действий для файлов отображаются только если файл загружен --}}
                                            @if($item['field']->field_type === 'file' && $item['student_response_id'] && $item['user_input'])
                                                <button class="btn btn-success btn-sm check-correct-btn" data-response-id="{{ $item['student_response_id'] }}" data-is-correct="true">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm check-incorrect-btn" data-response-id="{{ $item['student_response_id'] }}" data-is-correct="false">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                                <button class="btn btn-secondary btn-sm check-reset-btn" data-response-id="{{ $item['student_response_id'] }}" data-is-correct="null">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">Поля-ответа не найдены для этого снимка.</p>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            <a href="{{ route('notebooks.edit', $studentNotebookInstance->namedLink->notebook) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle me-2"></i>Вернуться к тетради
            </a>
        </div>

    @else
        <div class="alert alert-warning text-center" role="alert">
            Информация о прогрессе не найдена.
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Функция для отображения уведомлений (копирование из edit.blade.php или другого общего места)
        function showNotification(message, type = 'info', duration = 3000) {
            let container = document.getElementById('notificationContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'notificationContainer';
                container.style.position = 'fixed';
                container.style.top = '1rem';
                container.style.right = '1rem';
                container.style.zIndex = '1050';
                container.style.display = 'flex';
                container.style.flexDirection = 'column-reverse';
                container.style.alignItems = 'flex-end';
                document.body.appendChild(container);
            }
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.padding = '0.75rem 1rem';
            notification.style.marginTop = '0.5rem';
            notification.style.borderRadius = '0.375rem';
            notification.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(1.25rem)';
            notification.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            notification.style.maxWidth = '300px';
            notification.style.textAlign = 'right';

            // Применение стилей для типов
            if (type === 'success') { notification.style.backgroundColor = '#d4edda'; notification.style.borderColor = '#28a745'; notification.style.color = '#155724'; notification.style.border = '1px solid'; }
            if (type === 'error') { notification.style.backgroundColor = '#f8d7da'; notification.style.borderColor = '#dc3545'; notification.style.color = '#721c24'; notification.style.border = '1px solid'; }
            if (type === 'info') { notification.style.backgroundColor = '#d1ecf1'; notification.style.borderColor = '#17a2b8'; notification.style.color = '#0c5460'; notification.style.border = '1px solid'; }


            container.appendChild(notification);
            void notification.offsetWidth; // Триггер для reflow, чтобы анимация сработала
            setTimeout(() => { notification.classList.add('show'); }, 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => { if (notification.parentNode) notification.remove(); }, 300);
            }, duration);
        }

        // Обработчики для кнопок проверки корректности
        document.querySelectorAll('.check-correct-btn, .check-incorrect-btn, .check-reset-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const responseId = this.dataset.responseId;
                let isCorrect = this.dataset.isCorrect; // Получаем значение как строку ('true', 'false', 'null')

                // Преобразуем строку в булево значение или null
                if (isCorrect === 'true') {
                    isCorrect = true;
                } else if (isCorrect === 'false') {
                    isCorrect = false;
                } else if (isCorrect === 'null') {
                    isCorrect = null;
                }

                if (!responseId) {
                    showNotification('ID ответа не найден.', 'error');
                    return;
                }

                try {
                    const response = await fetch(`/student-responses/${responseId}/mark-correctness`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_correct: isCorrect })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        showNotification(data.message || 'Статус ответа обновлен.', 'success');
                        
                        // Обновляем значок корректности в таблице
                        const row = this.closest('tr');
                        const statusCell = row.querySelector('.correctness-status-cell');
                        if (statusCell) {
                            let newBadgeHtml = '';
                            if (data.is_correct === true) {
                                newBadgeHtml = '<span class="badge bg-success">Верно</span>';
                            } else if (data.is_correct === false) {
                                newBadgeHtml = '<span class="badge bg-danger">Неверно</span>';
                            } else { // null
                                newBadgeHtml = '<span class="badge bg-secondary">Не проверялось</span>';
                            }
                            statusCell.innerHTML = newBadgeHtml;
                        }
                        
                        // Обновляем общую статистику без перезагрузки страницы (опционально, но лучше для UX)
                        // fetchOverallStatistics(); // Если есть такая функция
                        window.location.reload(); // Для простоты пока перезагрузим, но AJAX-обновление лучше
                    } else {
                        showNotification(data.message || 'Ошибка обновления статуса.', 'error');
                    }
                } catch (error) {
                    console.error('Ошибка при отправке запроса на маркировку:', error);
                    showNotification('Ошибка сети при маркировке ответа.', 'error');
                }
            });
        });
    });
</script>
@endsection
