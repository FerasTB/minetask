<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoubleEntry extends Model
{
    use HasFactory;

    protected $fillable = ['COA_id', 'invoice_id', 'invoice_item_id', 'receipt_id', 'total_price', 'type'];

    public function COA()
    {
        return $this->belongsTo(COA::class, 'COA_id');
    }
}
