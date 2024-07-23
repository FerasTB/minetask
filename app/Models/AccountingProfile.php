<?php

namespace App\Models;

use App\Enums\DoubleEntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_lab_id', 'doctor_id', 'type', 'office_id',
        'supplier_name', 'note', 'initial_balance', 'COA_id', 'patient_id',
        'secondary_initial_balance'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function lab()
    {
        return $this->belongsTo(DentalLab::class, 'dental_lab_id');
    }

    public function COA()
    {
        return $this->belongsTo(COA::class, 'COA_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'accounting_profile_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'accounting_profile_id');
    }

    public function invoiceReceipt()
    {
        return $this->hasMany(InvoiceReceipt::class, 'accounting_profile_id');
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class, 'accounting_profile_id');
    }

    public function doubleEntries()
    {
        return $this->hasMany(DoubleEntry::class, 'accounting_profile_id');
    }

    public function directDoubleEntries()
    {
        return $this->hasMany(DirectDoubleEntry::class, 'accounting_profile_id');
    }

    public function getTotalBalanceAttribute()
    {
        // Use the loaded relationships to avoid N+1 queries
        $doubleEntries = $this->relationLoaded('doubleEntries')
            ? $this->doubleEntries
            : $this->doubleEntries()->get();

        $directDoubleEntries = $this->relationLoaded('directDoubleEntries')
            ? $this->directDoubleEntries
            : $this->directDoubleEntries()->get();

        // Sum positive and negative entries from both doubleEntries and directDoubleEntries
        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');

        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        return $totalPositive - $totalNegative;
    }

    public function accountOutcome()
    {
        $positive = $this->invoices();
        $totalPositive = $positive != null ?
            $positive->sum('total_price') : 0;
        $negative = $this->receipts();
        $totalNegative = $negative != null ?
            $negative->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative + $this->initial_balance;
        return $total;
    }
}
