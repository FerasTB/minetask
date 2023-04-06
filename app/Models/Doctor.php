<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Doctor extends Model
{
    use HasFactory;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;


    protected   $fillable = ['first_name', 'last_name', 'practicing_from'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'doctor_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'doctor_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'doctor_id');
    }

    public function cases()
    {
        return $this->hasMany(MedicalCase::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function PatientCases(): HasManyThrough
    {
        return $this->hasManyThrough(PatientCase::class, MedicalCase::class, 'doctor_id', 'case_id');
    }

    public function teethRecords()
    {
        return $this->hasManyDeep(TeethRecord::class, [MedicalCase::class, PatientCase::class], [
            'doctor_id', // Foreign key on the "case" table.
            'case_id',    // Foreign key on the "patient case" table.
            'patientCase_id'     // Foreign key on the "teeth record" table.
        ]);
    }
}
