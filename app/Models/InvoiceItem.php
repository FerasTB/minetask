<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'amount', 'total_price', 'price_per_one', 'invoice_id'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function invoiceReceipt()
    {
        return $this->belongsTo(InvoiceReceipt::class, 'invoice_receipt_id');
    }
}
