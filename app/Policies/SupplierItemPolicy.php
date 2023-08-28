<?php

namespace App\Policies;

use App\Enums\OfficeType;
use App\Enums\SubRole;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\SupplierItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupplierItemPolicy
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
    public function view(User $user, SupplierItem $supplierItem): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, AccountingProfile $supplier): bool
    {
        return $user->doctor && $supplier->doctor->id == $user->doctor->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupplierItem $supplierItem): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupplierItem $supplierItem): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SupplierItem $supplierItem): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SupplierItem $supplierItem): bool
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

    public function inOffice(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null;
    }

    public function updateForDoctor(User $user, SupplierItem $item, Doctor $doctor): bool
    {
        return $item->doctor->id == $doctor->id;
    }

    public function updateForOffice(User $user, SupplierItem $item, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return $role != null && $role->sub_role == SubRole::OfficeOwner;
    }
}
