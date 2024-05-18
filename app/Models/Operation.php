<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = ['operation_description', 'operation_name'];

    protected $appends = ['is_paid', 'price'];

    protected function isPaid(): Attribute
    {
        return new Attribute(
            get: fn () => $this->invoiceItem && $this->invoiceItem->invoice && $this->invoiceItem->invoice->status === TransactionStatus::Paid
        );
    }

    protected function price(): Attribute
    {
        return new Attribute(
            get: fn () => $this->invoiceItem?->total_price
        );
    }
    public function record()
    {
        return $this->belongsTo(TeethRecord::class, 'record_id');
    }

    public function teeth()
    {
        return $this->hasMany(Tooth::class, 'operation_id');
    }

    public function invoiceItem()
    {
        return $this->hasOne(InvoiceItem::class, 'operation_id');
    }
}
