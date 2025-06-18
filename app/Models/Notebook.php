<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Page;
use App\Models\NotebookSnapshot;
use App\Models\Block;
use App\Models\BlockNotebook;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Notebook extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];

    /**
     * Связь: тетрадь → страницы
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Связь: тетрадь → снимки (версии)
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(NotebookSnapshot::class);
    }

    /**
     * Связь: тетрадь → текущий активный снимок
     */
    public function currentSnapshot(): BelongsTo
    {
        return $this->belongsTo(NotebookSnapshot::class, 'current_snapshot_id');
    }

    /**
     * Связь: тетрадь имеет много именованных ссылок (для учеников)
     */
    public function namedLinks(): HasMany
    {
        return $this->hasMany(NamedLink::class);
    }

    /**
     * Связь: тетрадь ↔ блоки (через кастомный pivot)
     * Используется для структуры тетради в редакторе, не для отображения ученикам.
     */
    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class)
                    ->using(BlockNotebook::class)
                    ->withPivot('custom_label')
                    ->withTimestamps();
    }

    /**
     * Связь: тетрадь принадлежит пользователю (автору).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Аксессор: процент заполнения (для всех полей-ответов во всех блоках)
     * Эта логика устарела и будет заменена подсчетом прогресса в StudentNotebookInstance.
     * Оставлена для совместимости, но не будет использоваться в новой архитектуре.
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
