<?php

namespace App\Models;

use Dotenv\Store\File\Paths;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = ['patient_name', 'steps', 'status', 'delivery_date', 'received_date', 'accounting_profile_id', 'note', 'attached_materials', 'patient_id'];

    public function doctor()
    {
        return $this->belongsToThrough(
            Doctor::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Doctor::class => 'doctor_id'
            ]
        );
    }

    public function lab()
    {
        return $this->belongsToThrough(
            DentalLab::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                DentalLab::class => 'dental_lab_id'
            ]
        );
    }

    public function office()
    {
        return $this->belongsToThrough(
            Office::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Office::class => 'office_id'
            ]
        );
    }

    public function account()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function details()
    {
        return $this->hasMany(LabOrderDetail::class, 'lab_order_id');
    }
}
