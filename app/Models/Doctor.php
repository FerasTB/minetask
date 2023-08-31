<?php

namespace App\Models;

use App\Http\Resources\AccountingProfileResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Doctor extends Model
{
    use HasFactory;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;


    protected   $fillable = ['first_name', 'last_name', 'practicing_from'];

    const DefaultCase = 'خدمات عامة';

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

    public function vacations()
    {
        return $this->hasMany(Vacation::class, 'doctor_id');
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

    public function accountingProfiles()
    {
        return $this->hasMany(AccountingProfile::class, 'doctor_id');
    }

    public function services()
    {
        return $this->hasMany(MedicalService::class, 'doctor_id');
    }

    public function COAS()
    {
        return $this->hasMany(COA::class, 'doctor_id');
    }

    public function coaGroups()
    {
        return $this->hasMany(CoaGroup::class, 'doctor_id');
    }

    public function supplierItem()
    {
        return $this->hasMany(SupplierItem::class, 'doctor_id');
    }

    // patient with appointment
    // public function trustPatients()
    // {
    //     return $this->hasManyThrough(Patient::class, Appointment::class, 'doctor_id','');
    // }

    public function cash()
    {
        return $this->hasOne(COA::class, 'doctor_id')->where('name', COA::Cash)->get();
    }

    public function receivable()
    {
        return $this->hasOne(COA::class, 'doctor_id')->where('name', COA::Receivable)->get();
    }

    public function payable()
    {
        return $this->hasMany(COA::class, 'doctor_id')->where('name', COA::Payable)->get();
    }

    public function drugs()
    {
        return $this->hasManyDeep(Drug::class, [MedicalCase::class, PatientCase::class, TeethRecord::class, Diagnosis::class], [
            'office_id', // Foreign key on the "case" table.
            'case_id',    // Foreign key on the "patient case" table.
            'patientCase_id',    // Foreign key on the "teeth record" table.
            'record_id',    // Foreign key on the "diagnosis" table.
            'diagnosis_id'     // Foreign key on the "drug" table.
        ]);
    }

    public function DirectDoubleEntryInvoice()
    {
        return $this->hasMany(DirectDoubleEntryInvoice::class, 'doctor_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'doctor_id');
    }

    public function transactionPrefix()
    {
        return $this->hasMany(TransactionPrefix::class, 'doctor_id');
    }
}
