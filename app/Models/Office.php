<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_consultation_fee', 'address',
        'office_image', 'office_name', 'number',
        'start_time', 'end_time', 'type',
    ];


    public function availabilities()
    {
        return $this->hasMany(availability::class, 'office_id');
    }

    public function services()
    {
        return $this->hasMany(MedicalService::class, 'office_id');
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(HasRole::class, 'roleable');
    }

    public function owner(): MorphOne
    {
        return $this->morphOne(HasRole::class, 'roleable')->oldestOfMany();
    }

    public function cases()
    {
        return $this->hasMany(MedicalCase::class, 'office_id');
    }

    public function vacations()
    {
        return $this->hasMany(Vacation::class, 'office_id');
    }

    public function accountingProfiles()
    {
        return $this->hasMany(AccountingProfile::class, 'office_id');
    }

    public function patientAccountingProfiles()
    {
        return $this->hasMany(AccountingProfile::class, 'office_id')->where(['doctor_id' => null, 'patient_id' != null]);
    }

    public function supplierAccountingProfiles()
    {
        return $this->hasMany(AccountingProfile::class, 'office_id')->where(['doctor_id' => null, 'patient_id' => null]);
    }

    public function teethRecords()
    {
        return $this->hasManyDeep(TeethRecord::class, [MedicalCase::class, PatientCase::class], [
            'office_id', // Foreign key on the "case" table.
            'case_id',    // Foreign key on the "patient case" table.
            'patientCase_id'     // Foreign key on the "teeth record" table.
        ]);
    }

    public function COAS()
    {
        return $this->hasMany(COA::class, 'office_id')->whereNull('doctor_id');
    }

    public function coaGroups()
    {
        return $this->hasMany(CoaGroup::class, 'office_id')->whereNull('doctor_id');
    }

    public function cash()
    {
        return $this->hasOne(COA::class, 'office_id')->where(['name' => COA::Cash, 'doctor_id' => null])->get();
    }

    public function receivable()
    {
        return $this->hasOne(COA::class, 'office_id')->where(['name' => COA::Receivable, 'doctor_id' => null])->get();
    }

    public function payable()
    {
        return $this->hasOne(COA::class, 'office_id')->where(['name' => COA::Payable, 'doctor_id' => null])->get();
    }
}
