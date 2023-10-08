<?php

namespace App\Policies;

use App\Models\AccountingProfile;
use App\Models\HasRole;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{

    public function createDentalLabInvoiceForDoctor(User $user, AccountingProfile $profile): bool
    {
        $lab = $profile->lab;
        $role = null;
        if ($lab) {
            $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        }
        return $role != null;
    }

    public function acceptDentalLabInvoice(User $user, Invoice $invoice): bool
    {
        $role = HasRole::where(['roleable_id' => $invoice->office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null && $invoice->doctor && auth()->user()->doctor;
    }

    public function storeDentalLabInvoiceForDoctor(User $user, AccountingProfile $profile): bool
    {
        if ($profile->office == null) {
            return false;
        }
        $role = HasRole::where(['roleable_id' => $profile->office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null && $profile->doctor->id == auth()->user()->doctor->id;
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
    public function view(User $user, Invoice $invoice): bool
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
    public function update(User $user, Invoice $invoice): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        //
    }
}
