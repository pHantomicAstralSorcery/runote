<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NamedLink extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id']; // 'id' is guarded by default, so explicit is fine.

    // Allow mass assignment for these fields
    protected $fillable = [
        'notebook_id',
        'token',
        'title',
        'is_active', // Now stored directly here
    ];

    // No need for public $timestamps = ['created_at']; if you have created_at and updated_at
    // Laravel handles created_at and updated_at automatically if they exist in table.
    // If you only want created_at, you can set public $timestamps = false; and manually update.
    // Assuming you have updated_at too, default timestamp handling is fine.

    /**
     * Связь: ссылка принадлежит тетради
     */
    public function notebook(): BelongsTo
    {
        return $this->belongsTo(Notebook::class);
    }

    /**
     * Связь: именная ссылка имеет один экземпляр тетради для ученика
     */
    public function studentInstance(): HasOne
    {
        return $this->hasOne(StudentNotebookInstance::class);
    }
}
