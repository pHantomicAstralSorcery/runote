<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Page;
use App\Models\ResponseField;
use App\Models\Notebook;
use App\Models\BlockNotebook;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Block extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Связь: блок принадлежит странице
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Блок теперь НЕ содержит ResponseFields напрямую.
     * ResponseFields теперь принадлежат NotebookSnapshot.
     * Эту связь удаляем, так как она больше не соответствует новой структуре.
     * Если вам нужно получить поля-ответы, это будет через Snapshot.
     */
    // public function responseFields(): HasMany
    // {
    //     return $this->hasMany(ResponseField::class);
    // }

    /**
     * Связь: блок может быть использован в нескольких тетрадях (через pivot)
     * Это по-прежнему актуально для структуры редактора.
     */
    public function notebooks(): BelongsToMany
    {
        return $this->belongsToMany(Notebook::class)
                    ->using(BlockNotebook::class)
                    ->withPivot('custom_label')
                    ->withTimestamps();
    }
}
