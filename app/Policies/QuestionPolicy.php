<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    /**
     * Определяет, может ли пользователь просматривать вопрос.
     * Допустим, если пользователь владеет тестом, к которому принадлежит вопрос.
     */
    public function view(User $user, Question $question)
    {
        return $question->quiz->user_id === $user->id;
    }

    /**
     * Определяет, может ли пользователь создавать вопрос.
     * Здесь нужно проверить, что пользователь является владельцем теста.
     */
    public function create(User $user, $quiz)
    {
        // Предполагается, что вы передаёте тест (Quiz) или его id.
        return $quiz->user_id === $user->id;
    }

    /**
     * Определяет, может ли пользователь редактировать вопрос.
     */
    public function update(User $user, Question $question)
    {
        return $question->quiz->user_id === $user->id;
    }

    /**
     * Определяет, может ли пользователь удалять вопрос.
     */
    public function delete(User $user, Question $question)
    {
        return $question->quiz->user_id === $user->id;
    }

public function deleteSelected(User $user, Quiz $quiz): bool
{
    return $quiz->user_id === $user->id || $user->isAdmin();
}

public function deleteAll(User $user, Quiz $quiz): bool
{
    return $quiz->user_id === $user->id || $user->isAdmin();
}
}
