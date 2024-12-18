<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Role as ModelsRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicalCasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        $technition = boolval(($user->doctor && $user->role == Role::Doctor) || in_array(auth()->user()->currentRole->name, ModelsRole::Technicians));
        return ($technition && $role != null);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MedicalCase $medicalCase): bool
    {
        if ($user->doctor) {
            return $medicalCase->doctor_id == $user->doctor->id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return ($user->doctor && $user->role == Role::Doctor && $role != null);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MedicalCase $case): bool
    {
        return $case->doctor_id == $user->doctor->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MedicalCase $medicalCase): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MedicalCase $medicalCase): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MedicalCase $medicalCase): bool
    {
        //
    }
}
