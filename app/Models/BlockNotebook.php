<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BlockNotebook extends Pivot
{
    protected $table = 'block_notebook';
    protected $fillable = [
        'block_id',
        'notebook_id',
        'custom_label',
    ];
}
