<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Владелец теста
    public function user()
    {
        return $this->belongsTo(User::class);
    }

public function updatePublishedStatus()
{
    // Получаем количество вопросов у теста
    $questionsCount = $this->questions()->count();

    // Если вопросов нет, устанавливаем тест как не опубликованный
    if ($questionsCount === 0) {
        $this->update(['is_published' => false]);
    }
}

public function users()
{
    return $this->belongsToMany(User::class, 'quiz_user')
                ->withPivot(['id', 'score', 'attempt_number', 'started_at', 'completed_at', 'status'])
                ->withTimestamps();
}




}
