<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Patient;
use App\Models\User;
use Dotenv\Store\File\Paths;
use Illuminate\Auth\Access\Response;

class PatientPolicy
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
    public function view(User $user, Patient $patient): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role == Role::Patient;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Patient $patient): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Patient $patient): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Patient $patient): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        //
    }

    public function viewRecord(User $user): bool
    {
        return $user->role == Role::Patient && $user->patient;
    }

    public function updatePatientInfo(User $user): bool
    {
        return $user->patient;
    }

    public function setInitialBalance(User $user, Patient $patient, Office $office): bool
    {
        $OfficeRole = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        $patientRole = $user->roles->where('roleable_id', $patient->id)->where('roleable_type', 'App\Models\Patient')->first();
        return $OfficeRole != null && $patientRole != null;
    }
}
