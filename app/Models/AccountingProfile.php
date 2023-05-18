<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingProfile extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'type', 'office_id', 'supplier_name', 'note', 'initial_balance', 'COA_id', 'patient_id'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'accounting_profile_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'accounting_profile_id');
    }

    public function labels()
    {
        return $this->hasMany(Label::class, 'accounting_profile_id');
    }
}
