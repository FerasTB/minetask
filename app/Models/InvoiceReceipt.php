<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReceipt extends Model
{
    use HasFactory;

    protected $fillable = ['accounting_profile_id', 'doctor_id', 'total_price', 'date_of_payment', 'note', 'running_balance', 'invoice_number'];

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

    public function accountingProfile()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_receipt_id');
    }
}
