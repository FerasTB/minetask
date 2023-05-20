<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COA extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'type', 'office_id', 'name', 'note', 'initial_balance'];

    const Cash = "Cash";
    const Payable =  "Payable";
    const Receivable =  "Receivable";

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function services()
    {
        return $this->hasMany(MedicalService::class, 'COA_id');
    }

    public function doubleEntries()
    {
        return $this->hasMany(DoubleEntry::class, 'COA_id');
    }
}
