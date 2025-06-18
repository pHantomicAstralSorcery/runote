<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Added for studentResponses
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ResponseField extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'validation_rules' => 'array',
        'correct_answers'  => 'array',
    ];

    /**
     * Поле-ответ принадлежит снимку тетради
     */
    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(NotebookSnapshot::class, 'notebook_snapshot_id');
    }

    /**
     * Связь: поле-ответ имеет много ответов ученика (через UUID)
     */
    public function studentResponses(): HasMany
    {
        // Связь один-ко-многим, где response_field_uuid в student_responses соответствует uuid этого поля
        return $this->hasMany(StudentResponse::class, 'response_field_uuid', 'uuid');
    }
}
