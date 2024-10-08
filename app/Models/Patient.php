<?php

namespace App\Models;

use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Patient extends Model
{
    use HasFactory;

    protected $with = ['temporaries'];

    protected $fillable = ['parent_id', 'user_id', 'mother_name', 'father_name', 'marital', 'note', 'birth_date', 'email', 'phone', 'last_name', 'first_name', 'gender'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): MorphToMany
    {
        return $this->morphToMany(user::class, 'roleable');
    }

    public function info(): HasOne
    {
        return $this->hasOne(UserInfo::class, 'patient_id');
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

    public function teethRecords()
    {
        return $this->hasManyThrough(TeethRecord::class, Report::class, 'patient_id', 'report_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'patient_id');
    }

    public function doctorImage()
    {
        return $this->hasMany(DoctorImage::class, 'patient_id');
    }

    public function parent()
    {
        return $this->belongsTo(Patient::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Patient::class, 'parent_id');
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(HasRole::class, 'roleable');
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class, 'patient_id');
    }
}
