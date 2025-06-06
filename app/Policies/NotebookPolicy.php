<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notebook;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotebookPolicy
{
    use HandlesAuthorization;

    // Только автор может обновлять и удалять
    public function update(User $user, Notebook $notebook): bool
    {
        return $user->id === $notebook->user_id;
    }

    public function delete(User $user, Notebook $notebook): bool
    {
        return $user->id === $notebook->user_id;
    }

    // Разрешаем создание всем аутентифицированным
    public function create(User $user): bool
    {
        return ! is_null($user);
    }

    // Только автор и админ могут просматривать
    public function view(User $user, Notebook $notebook): bool
    {
        return $user->id === $notebook->user_id || $user->isAdmin();
    }
}
