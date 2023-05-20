<?php

namespace App\Policies;

use App\Models\HasRole;
use App\Models\MedicalInformation;
use App\Models\Office;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicalInformationPolicy
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
    public function view(User $user, MedicalInformation $medicalInformation): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MedicalInformation $medicalInformation): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MedicalInformation $medicalInformation): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MedicalInformation $medicalInformation): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MedicalInformation $medicalInformation): bool
    {
        //
    }
}
