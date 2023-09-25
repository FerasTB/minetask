<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPrefix extends Model
{
    use HasFactory;

    protected $fillable = ['dental_lab_id', 'prefix', 'type', 'last_transaction_number', 'doctor_id', 'office_id'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
