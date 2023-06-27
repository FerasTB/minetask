<?php

namespace App\Policies;

use App\Enums\AccountingProfileType;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\InvoiceReceipt;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoiceReceiptsPolicy
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
    public function view(User $user, InvoiceReceipt $invoiceReceipt): bool
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
    public function update(User $user, InvoiceReceipt $invoiceReceipt): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvoiceReceipt $invoiceReceipt): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvoiceReceipt $invoiceReceipt): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvoiceReceipt $invoiceReceipt): bool
    {
        //
    }

    public function patientAccount(User $user, Doctor $doctor, AccountingProfile $account): bool
    {
        return $account->type == AccountingProfileType::PatientAccount &&
            $account->doctor && $account->doctor->id == $doctor->id;
    }
}
