<?php

namespace App\Policies;

use App\Enums\SubRole;
use App\Models\AccountingProfile;
use App\Models\DentalLab;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccountingProfilePolicy
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
    public function view(User $user, AccountingProfile $accountingProfile): bool
    {
        $role = HasRole::where(['roleable_id' => $accountingProfile->office_id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return ($accountingProfile->doctor_id == $user->doctor->id) || ($role != null && $role->sub_role == SubRole::OfficeOwner);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
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

    public function inOffice(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccountingProfile $accountingProfile): bool
    {
        return $accountingProfile->doctor_id == $user->doctor->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccountingProfile $accountingProfile): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AccountingProfile $accountingProfile): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AccountingProfile $accountingProfile): bool
    {
        //
    }
}
