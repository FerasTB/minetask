<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCase extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'status', 'note', 'number_of_sessions', 'time_per_session'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }


    public function case()
    {
        return $this->belongsTo(MedicalCase::class, 'case_id');
    }

    public function teethRecords()
    {
        return $this->hasMany(TeethRecord::class, 'patientCase_id');
    }
}
