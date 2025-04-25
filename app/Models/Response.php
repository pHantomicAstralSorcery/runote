<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'named_link_id',
        'field_id',
        'value',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function namedLink()
    {
        return $this->belongsTo(NamedLink::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
