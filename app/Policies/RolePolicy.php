<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view_any_role');
    }

    public function view(User $user, Role $role)
    {
        return $user->can('view_role');
    }

    public function create(User $user)
    {
        return $user->can('create_role');
    }

    public function update(User $user, Role $role)
    {
        return $user->can('update_role');
    }

    public function delete(User $user, Role $role)
    {
        return $user->can('delete_role');
    }

    public function deleteAny(User $user)
    {
        return $user->can('delete_any_role');
    }

    public function forceDelete(User $user, Role $role)
    {
        return $user->can('{{ ForceDelete }}');
    }

    public function forceDeleteAny(User $user)
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    public function restore(User $user, Role $role)
    {
        return $user->can('{{ Restore }}');
    }

    public function restoreAny(User $user)
    {
        return $user->can('{{ RestoreAny }}');
    }

    public function replicate(User $user, Role $role)
    {
        return $user->can('{{ Replicate }}');
    }

    public function reorder(User $user)
    {
        return $user->can('{{ Reorder }}');
    }
}
