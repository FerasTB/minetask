<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Receipt extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $fillable = ['reversed_by', 'original_receipt_id', 'created_by', 'type', 'status', 'note', 'date_of_payment', 'total_price', 'invoice_id', 'doctor_id', 'accounting_profile_id', 'running_balance', 'receipt_number'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($Receipt) {
            $Receipt->created_by = Auth::check() ? Auth::id() : null;
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

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_receipt')
            ->withPivot(['total_price']);
    }

    public function account()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }

    public function getReceiptNumberAttribute()
    {
        return str_pad($this->attributes['receipt_number'], 5, '0', STR_PAD_LEFT);
    }

    public function doubleEntries()
    {
        return $this->hasMany(DoubleEntry::class, 'receipt_id');
    }
}
