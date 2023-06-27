<?php

namespace App\Policies;

use App\Models\COA;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoiceItemPolicy
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
    public function view(User $user, InvoiceItem $invoiceItem): bool
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
    public function update(User $user, InvoiceItem $invoiceItem): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvoiceItem $invoiceItem): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvoiceItem $invoiceItem): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvoiceItem $invoiceItem): bool
    {
        //
    }

    public function myCOA(User $user, COA $coa): bool
    {
        return $coa->doctor->id == $user->doctor->id;
    }
}
