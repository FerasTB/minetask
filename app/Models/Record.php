<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    protected $fillable = ['payment_fee'];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function case()
    {
        return $this->belongsTo(MedicalCase::class, 'case_id');
    }

    public function diagnosis()
    {
        return $this->hasOne(Diagnosis::class, 'record_id');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'record_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'record_id');
    }
}
