<?php

namespace App\Policies;

use App\Enums\SubRole;
use App\Models\HasRole;
use App\Models\MedicalService;
use App\Models\Office;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicalServicePolicy
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
    public function view(User $user, MedicalService $medicalService): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Office $office): bool
    {
        $role = HasRole::where(['user_id' => $user->id, 'roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office'])->first();
        if ($role != null) {
            return ($role->sub_role == SubRole::OfficeOwner);
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MedicalService $medicalService): bool
    {
        return $medicalService->doctor->id == $user->doctor->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MedicalService $medicalService): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MedicalService $medicalService): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MedicalService $medicalService): bool
    {
        //
    }
}
