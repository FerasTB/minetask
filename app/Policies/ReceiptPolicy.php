<?php

namespace App\Policies;

use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReceiptPolicy
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
    public function view(User $user, Receipt $receipt): bool
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
    public function update(User $user, Receipt $receipt): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Receipt $receipt): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Receipt $receipt): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Receipt $receipt): bool
    {
        //
    }

    public function myInvoice(User $user, Invoice $invoice): bool
    {
        return $user->doctor && $user->doctor->id == $invoice->doctor->id;
    }

    public function createReceiptForDentalLab(User $user, AccountingProfile $profile, Doctor $doctor): bool
    {
        $role = HasRole::where(['roleable_id' => $profile->office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $doctor->id == $profile->doctor->id && $role != null;
    }

    public function acceptDoctorReceipt(User $user, Receipt $receipt): bool
    {
        $role = HasRole::where(['roleable_id' => $receipt->lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        return $role != null;
    }
}
