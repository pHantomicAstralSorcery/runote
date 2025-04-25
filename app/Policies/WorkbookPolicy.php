<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workbook;

class WorkbookPolicy
{
    /**
     * Определяет, может ли пользователь просматривать тетрадь.
     */
    public function view(User $user, Workbook $workbook): bool
    {
        return $user->id === $workbook->user_id;
    }

    /**
     * Может ли пользователь редактировать/обновлять тетрадь.
     */
    public function update(User $user, Workbook $workbook): bool
    {
        return $user->id === $workbook->user_id;
    }

    /**
     * Может ли пользователь удалить тетрадь.
     */
    public function delete(User $user, Workbook $workbook): bool
    {
        return $user->id === $workbook->user_id;
    }

    // при необходимости добавьте другие методы: create, restore, etc.
}
