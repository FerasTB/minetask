<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalService extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'cost', 'office_id', 'doctor_id', 'COA_id'];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function COA()
    {
        return $this->belongsTo(Office::class, 'COA_id');
    }
}
