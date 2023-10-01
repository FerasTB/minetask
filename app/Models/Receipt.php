<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $fillable = ['note', 'date_of_payment', 'total_price', 'invoice_id', 'doctor_id', 'accounting_profile_id', 'running_balance', 'receipt_number'];

    public function patient()
    {
        return $this->belongsToThrough(
            Patient::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Patient::class => 'patient_id'
            ]
        );
    }

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

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_receipt')
            ->withPivot(['total_price']);
    }

    public function getReceiptNumberAttribute()
    {
        return str_pad($this->attributes['receipt_number'], 5, '0', STR_PAD_LEFT);
    }
}
