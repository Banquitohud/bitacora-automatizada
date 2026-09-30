<?php

namespace App\Policies;

use App\Models\DailyCase;
use App\Models\User;

class DailyCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, DailyCase $case): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, DailyCase $case): bool
    {
        if (! $user->is_active) {
            return false;
        }

        // El administrador puede editar todo.
        if ($user->isAdmin()) {
            return true;
        }

        // El analista puede editar casos asignados a él o casos sin asignar.
        return $case->analyst_id === null || $case->analyst_id === $user->id;
    }

    public function delete(User $user, DailyCase $case): bool
    {
        return $user->isAdmin();
    }
}