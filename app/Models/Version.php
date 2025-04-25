<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Version extends Model
{
    use HasFactory;

    protected $fillable = [
        'workbook_id',
        'content',
    ];

    public function workbook()
    {
        return $this->belongsTo(Workbook::class);
    }
}
