<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Workbook;

class Field extends Model
{
    use HasFactory;

    protected $fillable = [
        'workbook_id',
        'label',
        'type',
        'options',
        'validation_rules',
        'key',
        'correct_answer'
    ];

    protected $casts = [
        'options'           => 'array',
        'validation_rules'  => 'array',
    ];

    public function workbook()
    {
        return $this->belongsTo(Workbook::class);
    }
}
