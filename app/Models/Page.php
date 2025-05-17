<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Notebook;
use App\Models\Block;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Page extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];

    /**
     * Страница принадлежит тетради
     */
    public function notebook(): BelongsTo
    {
        return $this->belongsTo(Notebook::class);
    }

    /**
     * Страница содержит упорядоченные блоки
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)
                    ->orderBy('order');
    }
}
