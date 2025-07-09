<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Distribution;
use Illuminate\Auth\Access\HandlesAuthorization;

class DistributionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_distribution');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Distribution $distribution): bool
    {
        return $user->can('view_distribution');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_distribution');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Distribution $distribution): bool
    {
        return $user->can('update_distribution');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Distribution $distribution): bool
    {
        return $user->can('delete_distribution');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_distribution');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Distribution $distribution): bool
    {
        return $user->can('force_delete_distribution');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_distribution');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Distribution $distribution): bool
    {
        return $user->can('restore_distribution');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_distribution');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Distribution $distribution): bool
    {
        return $user->can('replicate_distribution');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_distribution');
    }
}
