<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Определяет, может ли пользователь просматривать список документов.
     */
    public function viewAny(User $user): bool
    {
        // Разрешаем просмотр списка документов всем аутентифицированным пользователям
        return true;
    }

    /**
     * Определяет, может ли пользователь просматривать документ.
     */
    public function view(User $user, Document $document): bool
    {
        // Просматривать документ может его владелец (mentee) или наставник (mentor), если требуется
        return $user->id === $document->mentee_id || $user->id === $document->mentor_id;
    }

    /**
     * Определяет, может ли пользователь создать документ.
     */
    public function create(User $user): bool
    {
        // Все аутентифицированные пользователи могут создавать документы
        return true;
    }

    /**
     * Определяет, может ли пользователь обновлять документ.
     */
    public function update(User $user, Document $document): bool
    {
        // Обновлять документ может только владелец (например, если ученик редактирует свою тетрадь)
        return $user->id === $document->mentee_id;
    }

    /**
     * Определяет, может ли пользователь удалить документ.
     */
    public function delete(User $user, Document $document): bool
    {
        // Удалять документ может только его владелец
        return $user->id === $document->mentee_id;
    }

    /**
     * Определяет, может ли пользователь восстановить документ.
     */
    public function restore(User $user, Document $document): bool
    {
        // Восстанавливать документ может только владелец
        return $user->id === $document->mentee_id;
    }

    /**
     * Определяет, может ли пользователь окончательно удалить документ.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        // Окончательно удалить документ может только его владелец
        return $user->id === $document->mentee_id;
    }
public function update(User $user, Document $document)
    {
        return $user->canEditDocument($document);
    }

    public function createNote(User $user, Document $document)
    {
        return $document->mentor_id === $user->id;
    }
}
