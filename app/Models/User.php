<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = ['id'];

    public function createdQuizzes()
    {
        return $this->hasMany(Quiz::class, 'user_id');
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_user')->withPivot('score')->withTimestamps();
    }

    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function workbooks()
    {
        return $this->hasMany(Workbook::class, 'user_id');
    }    
}
