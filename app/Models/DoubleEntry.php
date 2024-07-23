<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoubleEntry extends Model
{
    use HasFactory;

    protected $fillable = ['running_balance', 'accounting_profile_id', 'COA_id', 'invoice_id', 'invoice_item_id', 'receipt_id', 'total_price', 'type'];

    public function COA()
    {
        return $this->belongsTo(COA::class, 'COA_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }
}
