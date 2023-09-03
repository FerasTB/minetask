<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorImage extends Model
{
    use HasFactory;

    protected $fillable = ['note', 'url', 'name', 'patient_id', 'teeth_record_id', 'doctor_id', 'office_id'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function teethRecord()
    {
        return $this->belongsTo(TeethRecord::class, 'teeth_record_id');
    }
}
