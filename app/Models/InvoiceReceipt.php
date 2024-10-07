<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InvoiceReceipt extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $fillable = ['created_by', 'accounting_profile_id', 'doctor_id', 'total_price', 'date_of_payment', 'note', 'running_balance', 'invoice_number'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($InvoiceReceipt) {
            $InvoiceReceipt->created_by = Auth::check() ? Auth::id() : null;
        });
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
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

    public function account()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_receipt_id');
    }
}
