<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Notebook;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NamedLink extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];

    public $timestamps = ['created_at'];

    /**
     * Ссылка принадлежит тетради
     */
    public function notebook(): BelongsTo
    {
        return $this->belongsTo(Notebook::class);
    }
}
