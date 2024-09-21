<?php

namespace App\Policies;

use App\Enums\SubRole;
use App\Models\COA;
use App\Models\DirectDoubleEntryInvoice;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use PhpParser\Comment\Doc;

class DirectDoubleEntryInvoicePolicy
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
    public function view(User $user, DirectDoubleEntryInvoice $directDoubleEntryInvoice): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, COA $coa, COA $coa2, Doctor $doctor): bool
    {
        $sameCOA = $coa->doctor_id == $coa2->doctor_id && $coa->office_id == $coa2->office_id;
        if ($coa->doctor_id == null && $sameCOA) {
            $role = HasRole::where(['roleable_id' => $coa->office_id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
            return $role != null && $role->sub_role == SubRole::OfficeOwner;
        }
        return $coa->doctor_id == $doctor->id && $sameCOA;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DirectDoubleEntryInvoice $directDoubleEntryInvoice): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DirectDoubleEntryInvoice $directDoubleEntryInvoice): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DirectDoubleEntryInvoice $directDoubleEntryInvoice): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DirectDoubleEntryInvoice $directDoubleEntryInvoice): bool
    {
        //
    }
}
