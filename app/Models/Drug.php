<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    protected $fillable = ['drug_name', 'eat', 'portion', 'frequency', 'note', 'effect'];

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class, 'diagnosis_id');
    }

    public function doctor()
    {
        return $this->belongsToThrough(
            Doctor::class,
            [MedicalCase::class, PatientCase::class, TeethRecord::class, Diagnosis::class],
            null,
            '',
            [
                MedicalCase::class => 'doctor_id',
                PatientCase::class => 'case_id',
                TeethRecord::class => 'patientCase_id',
                Diagnosis::class => 'record_id',
            ]
        );
    }
}
