<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Block;
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
     * Поле-ответ принадлежит блоку
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}
