<?php

namespace App\Policies;

use App\Enums\SubRole;
use App\Models\DentalLab;
use App\Models\DentalLabService;
use App\Models\HasRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DentalLabServicePolicy
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
    public function view(User $user, DentalLabService $dentalLabService): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, DentalLab $lab): bool
    {
        $role = HasRole::where(['user_id' => $user->id, 'roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab'])->first();
        if ($role != null) {
            return ($role->sub_role == SubRole::DentalLabOwner);
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DentalLabService $dentalLabService, DentalLab $lab): bool
    {
        $role = HasRole::where(['user_id' => $user->id, 'roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab'])->first();
        return $dentalLabService->lab->id == $lab->id && $role != null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DentalLabService $dentalLabService): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DentalLabService $dentalLabService): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DentalLabService $dentalLabService): bool
    {
        //
    }
}
