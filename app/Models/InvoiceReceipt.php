<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReceipt extends Model
{
    use HasFactory;

    protected $fillable = ['accounting_profile_id', 'doctor_id', 'total_price', 'date_of_payment', 'note', 'running_balance', 'invoice_number'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
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
