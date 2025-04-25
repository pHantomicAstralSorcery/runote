<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuizPolicy
{
public function viewAny(User $user): bool
{
    return true;
}

public function view(User $user, Quiz $quiz): bool
{
    if ($quiz->access_type === 'hidden') {
         return $quiz->user_id === $user->id || $user->isAdmin();
    }
    return $quiz->access_type === 'open' || $quiz->access_type === 'link';
}


public function statistics(User $user, Quiz $quiz): bool
{
    return $quiz->user_id === $user->id || $user->isAdmin();
}


    public function create(User $user)
    {
        return isset($user);
    }

public function update(User $user, Quiz $quiz): bool
{
    return $user->id === $quiz->user_id || $user->isAdmin();
}

public function delete(User $user, Quiz $quiz): bool
{
    return $user->id === $quiz->user_id || $user->isAdmin();
}

public function publish(User $user, Quiz $quiz): bool
{
    return $quiz->user_id === $user->id 
        && $quiz->questions()->exists();
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
