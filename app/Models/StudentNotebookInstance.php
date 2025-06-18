<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasOne; // Убрал, если не используется

class StudentNotebookInstance extends Model
{
    protected $table = 'student_notebook_instances';
    protected $guarded = ['id'];

    protected $casts = [
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Связь: экземпляр принадлежит именной ссылке (один к одному)
     */
    public function namedLink(): BelongsTo
    {
        return $this->belongsTo(NamedLink::class);
    }

    /**
     * Связь: экземпляр связан с конкретным снимком тетради
     */
    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(NotebookSnapshot::class, 'notebook_snapshot_id');
    }

    /**
     * Связь: экземпляр имеет много ответов ученика
     */
    public function studentResponses(): HasMany
    {
        return $this->hasMany(StudentResponse::class, 'student_notebook_instance_id');
    }

    /**
     * Связь для подсчета только правильных ответов.
     */
    public function correctResponses(): HasMany
    {
        return $this->hasMany(StudentResponse::class, 'student_notebook_instance_id')->where('is_correct', true);
    }

    /**
     * === НОВЫЙ МЕТОД ===
     * Связь для подсчета только неправильных ответов (исключая пустые).
     */
    public function incorrectResponses(): HasMany
    {
        return $this->hasMany(StudentResponse::class, 'student_notebook_instance_id')
                    ->where('is_correct', false)
                    ->whereNotNull('user_input'); // Убеждаемся, что ответ был дан
    }

    /**
     * Аксессор: процент заполнения для данного экземпляра.
     * Вычисляется на основе полей в snapshot и student_responses.
     */
    public function getCompletionPercentAttribute(): float
    {
        if (!$this->snapshot) {
            return 0.0;
        }

        $totalFields = $this->snapshot->responseFields()->count();

        if ($totalFields === 0) {
            return 0.0;
        }

        $filledResponses = $this->studentResponses()
                                ->whereNotNull('user_input')
                                ->count();

        return round(($filledResponses / $totalFields) * 100, 2);
    }

    /**
     * Аксессор: количество правильных ответов для данного экземпляра.
     * (Теперь это можно использовать, но withCount('correctResponses') уже считает напрямую)
     */
    public function getCorrectAnswersCountAttribute(): int
    {
        return $this->correctResponses()->count(); // Используем новую связь
    }
}
