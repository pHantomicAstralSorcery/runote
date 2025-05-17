<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ResponseField;
use App\Models\NamedLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentResponse extends Model
{
    protected $table = 'student_responses';

    protected $guarded = ['id',];

    protected $casts = [
        'is_correct' => 'boolean',
        'checked_at' => 'datetime',
    ];

    /** Поле-ответ, к которому привязан ответ */
    public function responseField(): BelongsTo
    {
        return $this->belongsTo(ResponseField::class);
    }

    /** Именная ссылка (ученик), который дал ответ */
    public function namedLink(): BelongsTo
    {
        return $this->belongsTo(NamedLink::class);
    }

    /** Пользователь, проверивший ответ */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
