<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Page;
use App\Models\NotebookVersion;
use App\Models\Block;
use App\Models\BlockNotebook;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Notebook extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];
     /**
     * Приводим oldSnapshot к нужному типу (JSON ↔ PHP-массив).
     * При создании/обновлении, если в атрибутах нет 'oldSnapshot', в БД
     * будет подставляться NULL.
     */
    protected $casts = [
        'oldSnapshot' => 'array',
    ];

    /**
     * Устанавливаем атрибут по умолчанию — null.
     * Это гарантирует, что при create() в запросе не попадёт PHP-массив.
     */
    protected $attributes = [
        'oldSnapshot' => null,
    ];

    /**
     * Связь: тетрадь → страницы
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Связь: тетрадь → версии
     */
    public function versions(): HasMany
    {
        return $this->hasMany(NotebookVersion::class);
    }

    /**
     * Связь: тетрадь ↔ блоки (через кастомный pivot)
     */
    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class)
                    ->using(BlockNotebook::class)
                    ->withPivot('custom_label')
                    ->withTimestamps();
    }

    /**
     * Аксессор: процент заполнения (для всех полей-ответов во всех блоках)
     */
    public function getCompletionPercentAttribute(): float
    {
        $total  = $this->blocks()
                       ->with('responseFields')
                       ->get()
                       ->pluck('responseFields')
                       ->flatten()
                       ->count();

        $filled = $this->blocks()
                       ->with('responseFields')
                       ->get()
                       ->pluck('responseFields')
                       ->flatten()
                       ->whereNotNull('user_answer')
                       ->count();

        return $total ? round($filled * 100 / $total, 2) : 0.0;
    }
}
