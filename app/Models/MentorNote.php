<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorNote extends Model
{
    protected $guarded = ['id'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}