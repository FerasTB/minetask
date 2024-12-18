<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['Reason_for_cancellation', 'created_by', 'is_patient_in_clinic', 'record_id', 'step', 'patientCase_id', 'taken_date', 'end_time', 'start_time', 'patient_id', 'office_id', 'status', 'note', 'color', 'doctor_id', 'office_room_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            $appointment->created_by = Auth::check() ? Auth::id() : null;
        });
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function room()
    {
        return $this->belongsTo(OfficeRoom::class, 'office_room_id');
    }

    public function record()
    {
        return $this->hasOne(TeethRecord::class, 'appointment_id');
    }

    public function case()
    {
        return $this->belongsTo(PatientCase::class, 'patientCase_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
