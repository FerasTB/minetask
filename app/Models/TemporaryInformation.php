<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryInformation extends Model
{
    use HasFactory;

    protected $fillable = ['mother_name', 'father_name', 'marital', 'note', 'birth_date', 'email', 'last_name', 'first_name', 'doctor_id', 'gender'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
