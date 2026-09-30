<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Project $project): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // El analista puede actualizar proyectos donde es responsable.
        return $project->responsible_id === null || $project->responsible_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }
}