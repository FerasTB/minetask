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

    public function invoiceReceipt()
    {
        return $this->belongsTo(InvoiceReceipt::class, 'invoice_receipt_id');
    }

    // Scopes
    public function scopeWhereConnectedDateBetween($query, $fromDate, $toDate)
    {
        $query->where(function ($q) use ($fromDate, $toDate) {
            $q->where(function ($q) use ($fromDate, $toDate) {
                $q->whereNotNull('invoice_id')
                    ->whereHas('invoice', function ($q) use ($fromDate, $toDate) {
                        $q->whereBetween('date_of_invoice', [$fromDate, $toDate]);
                    });
            })
                ->orWhere(function ($q) use ($fromDate, $toDate) {
                    $q->whereNotNull('invoice_item_id')
                        ->whereHas('invoiceItem.invoice', function ($q) use ($fromDate, $toDate) {
                            $q->whereBetween('date_of_invoice', [$fromDate, $toDate]);
                        });
                })
                ->orWhere(function ($q) use ($fromDate, $toDate) {
                    $q->whereNotNull('receipt_id')
                        ->whereHas('receipt', function ($q) use ($fromDate, $toDate) {
                            $q->whereBetween('date_of_payment', [$fromDate, $toDate]);
                        });
                })
                ->orWhere(function ($q) use ($fromDate, $toDate) {
                    $q->whereNotNull('invoice_receipt_id')
                        ->whereHas('invoiceReceipt', function ($q) use ($fromDate, $toDate) {
                            $q->whereBetween('date_of_payment', [$fromDate, $toDate]);
                        });
                });
        });
    }

    public function scopeWhereConnectedDateBefore($query, $date)
    {
        $query->where(function ($q) use ($date) {
            $q->where(function ($q) use ($date) {
                $q->whereNotNull('invoice_id')
                    ->whereHas('invoice', function ($q) use ($date) {
                        $q->where('date_of_invoice', '<', $date);
                    });
            })
                ->orWhere(function ($q) use ($date) {
                    $q->whereNotNull('invoice_item_id')
                        ->whereHas('invoiceItem.invoice', function ($q) use ($date) {
                            $q->where('date_of_invoice', '<', $date);
                        });
                })
                ->orWhere(function ($q) use ($date) {
                    $q->whereNotNull('receipt_id')
                        ->whereHas('receipt', function ($q) use ($date) {
                            $q->where('date_of_payment', '<', $date);
                        });
                })
                ->orWhere(function ($q) use ($date) {
                    $q->whereNotNull('invoice_receipt_id')
                        ->whereHas('invoiceReceipt', function ($q) use ($date) {
                            $q->where('date_of_payment', '<', $date);
                        });
                });
        });
    }
}
