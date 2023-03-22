<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['taken_date', 'end_time', 'start_time'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function availability()
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    public function status()
    {
        return $this->belongsTo(AppointmentStatus::class, 'status_id');
    }

    public function record()
    {
        return $this->hasOne(Record::class, 'appointment_id');
    }
}
