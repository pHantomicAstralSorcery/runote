<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizUserAnswer extends Model
{
    // Разрешенные для массового присвоения поля
    protected $guarded = ['id'];

    // Отношения с другими моделями
    public function quizUser()
    {
        return $this->belongsTo(QuizUser::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
