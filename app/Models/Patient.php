<?php

namespace App\Models;

use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Patient extends Model
{
    use HasFactory;

    protected $with = ['temporaries'];

    protected $fillable = ['note', 'birth_date', 'email', 'phone', 'last_name', 'first_name', 'gender'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): MorphToMany
    {
        return $this->morphToMany(user::class, 'roleable');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'patient_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function teethReport()
    {
        return $this->hasOne(Report::class, 'patient_id')->where('report_type', ReportType::TeethReport);
    }

    public function report()
    {
        return $this->hasMany(Report::class, 'patient_id');
    }

    public function temporaries()
    {
        return $this->hasMany(TemporaryInformation::class, 'patient_id');
    }

    public function cases()
    {
        return $this->hasMany(PatientCase::class, 'patient_id');
    }

    public function medicalInformation()
    {
        return $this->hasOne(MedicalInformation::class, 'patient_id')->where('is_temporary', false);
    }

    public function allMedicalInformation()
    {
        return $this->hasMany(MedicalInformation::class, 'patient_id');
    }

    public function accountingProfiles()
    {
        return $this->hasMany(AccountingProfile::class, 'patient_id');
    }

    public function debts()
    {
        return $this->hasManyThrough(Debt::class, AccountingProfile::class, 'patient_id', 'accounting_profile_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'patient_id');
    }
}
