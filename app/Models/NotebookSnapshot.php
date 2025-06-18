<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotebookSnapshot extends Model
{
    protected $table = 'notebook_snapshots'; // Убедитесь, что имя таблицы правильное
    protected $guarded = ['id']; // Разрешаем массовое присвоение для всех полей, кроме 'id'

    /**
     * Связь: снимок принадлежит тетради
     */
    public function notebook(): BelongsTo
    {
        return $this->belongsTo(Notebook::class);
    }

    /**
     * Связь: снимок имеет много полей-ответов
     */
    public function responseFields(): HasMany
    {
        return $this->hasMany(ResponseField::class, 'notebook_snapshot_id');
    }

    /**
     * Связь: снимок может быть текущим для нескольких экземпляров тетрадей учеников
     */
    public function studentNotebookInstances(): HasMany
    {
        return $this->hasMany(StudentNotebookInstance::class, 'notebook_snapshot_id');
    }
}
