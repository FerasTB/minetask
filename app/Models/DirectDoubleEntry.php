<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectDoubleEntry extends Model
{
    use HasFactory;

    protected $fillable = ['running_balance', 'accounting_profile_id', 'COA_id', 'direct_double_entry_invoice_id', 'total_price', 'type'];

    public function directDoubleEntryInvoice()
    {
        return $this->belongsTo(DirectDoubleEntryInvoice::class, 'direct_double_entry_invoice_id');
    }

    // Scopes
    public function scopeWhereConnectedDateBetween($query, $fromDate, $toDate)
    {
        $query->whereHas('directDoubleEntryInvoice', function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('date_of_transaction', [$fromDate, $toDate]);
        });
    }

    public function scopeWhereConnectedDateBefore($query, $date)
    {
        $query->whereHas('directDoubleEntryInvoice', function ($q) use ($date) {
            $q->where('date_of_transaction', '<', $date);
        });
    }

    public function COA()
    {
        return $this->belongsTo(COA::class, 'COA_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }
}
