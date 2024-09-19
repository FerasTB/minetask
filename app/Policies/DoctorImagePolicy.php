<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\DoctorImage;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Patient;
use App\Models\TeethRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DoctorImagePolicy
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
    public function view(User $user, DoctorImage $doctorImage): bool
    {
        return $user->doctor && $doctorImage->doctor_id == $user->doctor->id;
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
    public function update(User $user, DoctorImage $doctorImage): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DoctorImage $doctorImage): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DoctorImage $doctorImage): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DoctorImage $doctorImage): bool
    {
        //
    }

    public function inOfficeAndHavePatient(User $user, Patient $patient, Office $office, Doctor $doctor): bool
    {
        $officeRole = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        $role = HasRole::where(['user_id' => $doctor->user->id, 'roleable_id' => $patient->id, 'roleable_type' => 'App\Models\Patient'])->first();
        return $role != null && $officeRole != null;
    }

    public function inOfficeAndHavePatientAndRecord(User $user, Patient $patient, Office $office, Doctor $doctor, TeethRecord $record): bool
    {
        $officeRole = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        $role = HasRole::where(['user_id' => $doctor->user->id, 'roleable_id' => $patient->id, 'roleable_type' => 'App\Models\Patient'])->first();
        return $role != null && $officeRole != null && $record->PatientCase->case->doctor->id == $doctor->id;
    }
}
