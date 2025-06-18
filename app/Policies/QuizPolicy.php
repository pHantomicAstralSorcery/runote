<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuizPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        // Пользователь может просматривать тест, если он открыт, доступен по ссылке,
        // или если пользователь является создателем теста, или администратором.
        if ($quiz->access_type === 'hidden') {
            return $quiz->user_id === $user->id || $user->isAdmin();
        }
        return $quiz->access_type === 'open' || $quiz->access_type === 'link';
    }

    /**
     * Determine whether the user can view quiz statistics.
     */
    public function statistics(User $user, Quiz $quiz): bool
    {
        // Статистику может просматривать только создатель теста или администратор.
        return $quiz->user_id === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can view quiz results details.
     */
    public function resultsDetails(User $user, Quiz $quiz): bool
    {
        // Подробные результаты может просматривать:
        // 1. Сам пользователь, прошедший тест.
        // 2. Создатель теста.
        // 3. Администратор.
        return $quiz->users->contains($user->id) || $quiz->user_id === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return isset($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can publish the quiz.
     */
    public function publish(User $user, Quiz $quiz): bool
    {
        return $quiz->user_id === $user->id 
            && $quiz->questions()->exists(); // Тест можно опубликовать, только если в нем есть вопросы.
    }

    /**
     * Determine whether the user can delete selected quizzes.
     */
    public function deleteSelected(User $user, Quiz $quiz): bool
    {
        return $quiz->user_id === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete all quizzes.
     */
    public function deleteAll(User $user, Quiz $quiz): bool
    {
        return $quiz->user_id === $user->id || $user->isAdmin();
    }
}
