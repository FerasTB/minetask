<?php

namespace App\Policies;

use App\Enums\SubRole;
use App\Models\COA;
use App\Models\DentalLab;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class COAPolicy
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
    public function view(User $user, COA $coa): bool
    {
        if ($coa->doctor_id == null) {
            $role = HasRole::where(['roleable_id' => $coa->office_id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
            return $role != null && $role->sub_role == SubRole::OfficeOwner;
        }
        return $user->doctor && $coa->doctor_id == $user->doctor->id;
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
    public function update(User $user, COA $coa): bool
    {
        if ($coa->doctor_id == null) {
            $role = HasRole::where(['roleable_id' => $coa->office_id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
            return $role != null && $role->sub_role == SubRole::OfficeOwner;
        }
        return $coa->doctor_id == $user->doctor->id;
    }

    public function updateBalanceForLab(User $user, COA $coa): bool
    {
        if ($coa->doctor_id == null) {
            $role = HasRole::where(['roleable_id' => $coa->dental_lab_id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
            return $role != null && $role->sub_role == SubRole::DentalLabOwner;
        }
        return $coa->doctor_id == $user->doctor->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, COA $cOA): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, COA $cOA): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, COA $cOA): bool
    {
        //
    }

    public function createForDoctor(User $user, Doctor $doctor): bool
    {
        return $user->doctor->id == $doctor->id;
    }

    public function createForOffice(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null;
    }

    public function createForLab(User $user, DentalLab $lab): bool
    {
        $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $role != null;
    }

    public function updateForDoctor(User $user, COA $coa, Doctor $doctor): bool
    {
        return $coa->doctor->id == $doctor->id;
    }

    public function updateForLab(User $user, COA $coa, DentalLab $lab): bool
    {
        $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $role != null && $role->sub_role == SubRole::DentalLabOwner;
    }

    public function updateForOffice(User $user, COA $coa, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null && $role->sub_role == SubRole::OfficeOwner;
    }

    public function inOffice(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null;
    }

    public function officeOwner(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null && $role->sub_role == SubRole::OfficeOwner;
    }

    public function labOwner(User $user, DentalLab $lab): bool
    {
        $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $role != null && $role->sub_role == SubRole::DentalLabOwner;
    }
}
