<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectDoubleEntryInvoice extends Model
{
    use HasFactory;

    protected $fillable = ['office_id', 'doctor_id', 'total_price', 'date_of_transaction', 'note'];

    public function directDoubleEntries()
    {
        return $this->hasMany(DirectDoubleEntry::class, 'direct_double_entry_invoice_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
