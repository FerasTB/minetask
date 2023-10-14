<?php

namespace App\Policies;

use App\Models\AccountingProfile;
use App\Models\HasRole;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LabOrderPolicy
{

    public function storeForDoctor(User $user, AccountingProfile $profile): bool
    {
        return $profile->doctor->id == $user->doctor->id;
    }

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
    public function view(User $user, LabOrder $labOrder): bool
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
    public function update(User $user, LabOrder $labOrder): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LabOrder $labOrder): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LabOrder $labOrder): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LabOrder $labOrder): bool
    {
        //
    }
}
