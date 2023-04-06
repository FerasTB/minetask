<?php

namespace App\Policies;

use App\Models\Operation;
use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OperationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Record $record): bool
    {
        if ($user->doctor) {
            return $record->case->doctor->id == $user->doctor->id;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Operation $operation): bool
    {
        if ($user->doctor) {
            return $operation->record->case->doctor->id == $user->doctor->id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Record $record): bool
    {
        if ($user->doctor) {
            return $record->case->doctor->id == $user->doctor->id;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Operation $operation): bool
    {
        if ($user->doctor) {
            return $operation->record->case->doctor->id == $user->doctor->id;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Operation $operation): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Operation $operation): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Operation $operation): bool
    {
        //
    }
}