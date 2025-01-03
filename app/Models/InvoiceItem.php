<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['teeth_record_id', 'name', 'description', 'amount', 'total_price', 'price_per_one', 'invoice_id', 'coa_id', 'operation_id'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function coa()
    {
        return $this->belongsTo(COA::class, 'coa_id');
    }

    public function invoiceReceipt()
    {
        return $this->belongsTo(InvoiceReceipt::class, 'invoice_receipt_id');
    }

    public function teethRecord()
    {
        return $this->belongsTo(TeethRecord::class, 'teeth_record_id');
    }
}
