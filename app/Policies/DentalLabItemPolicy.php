<?php

namespace App\Policies;

use App\Enums\SubRole;
use App\Models\DentalLab;
use App\Models\DentalLabItem;
use App\Models\HasRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DentalLabItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DentalLabItem $dentalLabItem): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DentalLabItem $dentalLabItem): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DentalLabItem $dentalLabItem): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DentalLabItem $dentalLabItem): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DentalLabItem $dentalLabItem): bool
    {
        //
    }

    public function createForLab(User $user, DentalLab $lab): bool
    {
        $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $role != null;
    }

    public function inLab(User $user, DentalLab $lab): bool
    {
        $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $role != null;
    }

    public function updateForLab(User $user, DentalLabItem $item, DentalLab $lab): bool
    {
        $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $role != null && $role->sub_role == SubRole::DentalLabOwner;
    }
}
