<?php

namespace App\Policies;

use App\Models\Diagnosis;
use App\Models\Drug;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DrugPolicy
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
    public function view(User $user, Drug $drug): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Diagnosis $diagnosis): bool
    {
        if ($user->doctor) {
            return $diagnosis->record->PatientCase->case->doctor->id == $user->doctor->id;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Drug $drug): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Drug $drug): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Drug $drug): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Drug $drug): bool
    {
        //
    }
}
