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

    public function storeForLab(User $user, AccountingProfile $profile): bool
    {
        $role = HasRole::where(['roleable_id' => $profile->lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $profile->doctor->dental_lab_id == $profile->lab->id && $role != null;
    }

    public function acceptFromDoctor(User $user, LabOrder $order): bool
    {
        $role = HasRole::where(['roleable_id' => $order->lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return  $role != null;
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
