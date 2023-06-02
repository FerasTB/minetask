<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['step', 'patientCase_id', 'taken_date', 'end_time', 'start_time', 'patient_id', 'office_id', 'status', 'note', 'color'];

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

    public function record()
    {
        return $this->hasOne(TeethRecord::class, 'appointment_id');
    }

    public function patientCase()
    {
        return $this->belongsTo(patientCase::class, 'patientCase_id');
    }
}
