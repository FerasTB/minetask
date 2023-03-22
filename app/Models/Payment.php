<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['payment_amount'];

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }
}
