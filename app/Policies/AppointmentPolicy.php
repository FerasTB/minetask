<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\OfficeRoom;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Office $office): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return ($role != null);
    }

    public function viewAnyWithRoom(User $user, Office $office, OfficeRoom $room): bool
    {
        $role = HasRole::where(['roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office', 'user_id' => $user->id])->first();
        return ($role != null && $room->office->id == $office->id);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return ($user->doctor && $user->doctor->id == $appointment->doctor->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    public function createForDoctor(User $user, Office $office, Doctor $doctor): bool
    {
        $role = HasRole::where(['user_id' => $doctor->user->id, 'roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office'])->first();
        $role2 = HasRole::where(['user_id' => $user->id, 'roleable_id' => $office->id, 'roleable_type' => 'App\Models\Office'])->first();
        return ($role != null) && ($role2 != null);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment, Doctor $doctor): bool
    {
        return ($doctor->id == $appointment->doctor->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Appointment $appointment): bool
    {
        //
    }

    public function viewPatient(User $user): bool
    {
        return $user->role == Role::Patient && $user->patient;
    }
}
