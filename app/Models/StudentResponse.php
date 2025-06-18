<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentResponse extends Model
{
    protected $table = 'student_responses';

    protected $fillable = [
        'student_notebook_instance_id',
        'response_field_uuid', // Теперь это UUID
        'user_input',
        'is_correct',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'checked_at' => 'datetime',
    ];

    /** Связь: Ответ принадлежит экземпляру тетради ученика */
    public function studentNotebookInstance(): BelongsTo
    {
        return $this->belongsTo(StudentNotebookInstance::class);
    }

    /**
     * Связь: Ответ относится к конкретному полю-ответу (через UUID).
     * Это "виртуальная" связь, так как она не использует PK/FK напрямую.
     * Если вам нужен объект ResponseField, его придется искать по UUID в связанном снимке.
     */
    public function responseField(): BelongsTo // Keep BelongsTo for type hinting simplicity, but note the UUID usage
    {
        // Это более сложная связь, т.к. response_field_uuid не является прямым FK.
        // Для получения объекта ResponseField обычно требуется дополнительная логика.
        // Например:
        // $response->studentNotebookInstance->snapshot->responseFields()->where('uuid', $response->response_field_uuid)->first();
        // Тем не менее, для общей структуры модели оставим так, но понимаем, что это не классическая FK.
        return $this->belongsTo(ResponseField::class, 'response_field_uuid', 'uuid');
    }

    /** Пользователь, проверивший ответ */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
