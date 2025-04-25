<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NamedLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'workbook_id',
        'name',
        'slug',
        'active',
        'open_at',
        'close_at',
    ];

    protected $casts = [
        'active'   => 'boolean',
        'open_at'  => 'datetime',
        'close_at' => 'datetime',
    ];

    public function workbook()
    {
        return $this->belongsTo(Workbook::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
