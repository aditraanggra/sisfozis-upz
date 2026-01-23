<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Fidyah;
use Illuminate\Auth\Access\HandlesAuthorization;

class FidyahPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_fidyah');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Fidyah $fidyah): bool
    {
        return $user->can('view_fidyah');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_fidyah');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Fidyah $fidyah): bool
    {
        return $user->can('update_fidyah');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Fidyah $fidyah): bool
    {
        return $user->can('delete_fidyah');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_fidyah');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Fidyah $fidyah): bool
    {
        return $user->can('force_delete_fidyah');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_fidyah');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Fidyah $fidyah): bool
    {
        return $user->can('restore_fidyah');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_fidyah');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Fidyah $fidyah): bool
    {
        return $user->can('replicate_fidyah');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_fidyah');
    }
}
