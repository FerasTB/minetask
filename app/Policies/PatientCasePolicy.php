<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PatientCasePolicy
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
    public function view(User $user, PatientCase $patientCase): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, MedicalCase $case, Patient $patient): bool
    {
        $role = HasRole::where(['user_id' => $user->id, 'roleable_id' => $patient->id, 'roleable_type' => 'App\Models\Patient'])->first();
        if ($role != null) {
            return ($user->role === Role::Doctor && $user->doctor && $case->doctor_id == $user->doctor->id);
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PatientCase $case): bool
    {
        return $case->doctor_id == $user->doctor->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PatientCase $patientCase): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PatientCase $patientCase): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PatientCase $patientCase): bool
    {
        //
    }
}
