<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalLab extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'end_time', 'start_time', 'number', 'address', 'image', 'name'];

    public function COAS()
    {
        return $this->hasMany(COA::class, 'dental_lab_id')->whereNull('doctor_id');
    }

    public function transactionPrefix()
    {
        return $this->hasMany(TransactionPrefix::class, 'dental_lab_id');
    }

    public function accountingProfiles()
    {
        return $this->hasMany(AccountingProfile::class, 'dental_lab_id');
    }

    public function hasDoctorAccount(Doctor $doctor, Office $office)
    {
        return AccountingProfile::where([
            'dental_lab_id' => $this->id,
            'doctor_id' => $doctor->id,
            'office_id' => $office->id,
        ])->first() != null;
    }
}
