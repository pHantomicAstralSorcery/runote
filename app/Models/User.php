<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; // Добавлено для notebooks

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

    /**
     * Связь: пользователь создал много тетрадей.
     */
    public function notebooks(): HasMany
    {
        return $this->hasMany(Notebook::class, 'user_id');
    }

    /**
     * Проверяет, является ли пользователь администратором.
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}
