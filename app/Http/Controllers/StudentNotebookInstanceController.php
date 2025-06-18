<?php

namespace App\Http\Controllers;

use App\Models\StudentNotebookInstance;
use App\Models\StudentResponse; // Добавил для использования в новом методе
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable; // Добавляем Throwable для обработки исключений

class StudentNotebookInstanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Только авторизованные пользователи могут просматривать прогресс
    }

    /**
     * Display the progress of a specific student notebook instance.
     * Отображает прогресс конкретного экземпляра тетради ученика для преподавателя.
     */
    public function showProgress(StudentNotebookInstance $studentNotebookInstance)
    {
        // Загружаем связанные данные: namedLink, snapshot, и studentResponses
        $studentNotebookInstance->load([
            'namedLink.notebook.user', // Для проверки владельца тетради
            'snapshot.responseFields',
            'studentResponses'
        ]);

        // Проверяем, что текущий пользователь является владельцем тетради, к которой относится этот экземпляр
        if (Auth::id() !== $studentNotebookInstance->namedLink->notebook->user->id) {
            abort(403, 'Доступ запрещен. Вы не являетесь владельцем этой тетради.');
        }

        // Извлекаем все поля-ответа из снимка, с которым работает ученик
        $responseFields = $studentNotebookInstance->snapshot->responseFields;

        // Создаем карту ответов ученика для удобства
        $studentResponsesMap = $studentNotebookInstance->studentResponses->keyBy('response_field_uuid');

        // Подготавливаем данные о полях и ответах для вывода в представлении
        $fieldsWithResponses = $responseFields->map(function ($field) use ($studentResponsesMap) {
            $studentResponse = $studentResponsesMap->get($field->uuid);
            return [
                'field' => $field,
                // user_input может быть JSON для файлов, поэтому передаем его как есть
                'user_input' => $studentResponse ? $studentResponse->user_input : null, 
                'is_correct' => $studentResponse ? $studentResponse->is_correct : null,
                'student_response_id' => $studentResponse ? $studentResponse->id : null, // ID ответа для кнопок
            ];
        });

        // Вычисляем общие метрики
        $totalFields = $responseFields->count();
        $filledFields = $studentNotebookInstance->studentResponses->whereNotNull('user_input')->count();
        $correctFields = $studentNotebookInstance->studentResponses->where('is_correct', true)->count();
        $completionPercent = $studentNotebookInstance->completion_percent; // Используем аксессор из модели
        $lastAccessedAt = $studentNotebookInstance->last_accessed_at;
        $lastActiveMinutes = $studentNotebookInstance->last_active_minutes;

        return view('notebooks.student_notebook_instances.progress', compact(
            'studentNotebookInstance',
            'fieldsWithResponses',
            'totalFields',
            'filledFields',
            'correctFields',
            'completionPercent',
            'lastAccessedAt',
            'lastActiveMinutes'
        ));
    }

    /**
     * Mark a specific student response as correct or incorrect.
     */
    public function markResponseCorrectness(Request $request, StudentResponse $studentResponse)
    {
        try {
            // Проверяем, что текущий пользователь является владельцем тетради, к которой относится ответ
            // Загружаем namedLink.notebook.user для StudentNotebookInstance, связанного с StudentResponse
            $studentResponse->load('studentNotebookInstance.namedLink.notebook.user');

            if (Auth::id() !== $studentResponse->studentNotebookInstance->namedLink->notebook->user->id) {
                return response()->json(['message' => 'Доступ запрещен. Вы не являетесь владельцем этой тетради.'], 403);
            }

            $data = $request->validate([
                'is_correct' => 'nullable|boolean', // true, false или null
            ]);

            $studentResponse->update(['is_correct' => $data['is_correct']]);

            // Возвращаем обновленный статус
            return response()->json([
                'status' => 'success',
                'message' => 'Статус ответа обновлен.',
                'is_correct' => $studentResponse->is_correct
            ]);

        } catch (Throwable $e) {
            \Log::error('Ошибка при маркировке ответа: ' . $e->getMessage(), [
                'response_id' => $studentResponse->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Ошибка при обновлении статуса ответа: ' . $e->getMessage()], 500);
        }
    }
}
