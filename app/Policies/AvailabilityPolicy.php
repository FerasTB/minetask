<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\SubRole;
use App\Models\Availability;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AvailabilityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->doctor);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Availability $availability): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Office $office): bool
    {
        if ($user->doctor) {
            $role = HasRole::where(['user_id' => $user->id, 'roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office'])->get()->first();
            if ($role != null) {
                return ($user->role === Role::Doctor);
            }
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Availability $availability): bool
    {
        return $user->doctor->id == $availability->doctor_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Availability $availability): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Availability $availability): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Availability $availability): bool
    {
        //
    }
}
