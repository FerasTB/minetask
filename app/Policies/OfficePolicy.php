<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\SubRole;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OfficePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->email && $user->email == "feras@marstaan.com";
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Office $office): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->role == Role::Doctor && $user->doctor);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Office $office): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Office $office): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Office $office): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Office $office): bool
    {
        //
    }

    public function officeOwner(User $user, Office $office): bool
    {
        $role = HasRole::where(['user_id' => $user->id, 'roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office'])->first();
        if ($role != null) {
            return ($role->sub_role == SubRole::OfficeOwner);
        }
        return false;
    }
}
