<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCase extends Model
{
    use HasFactory;

    protected $fillable = ['case_name', 'is_closed'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function record()
    {
        return $this->hasMany(Record::class, 'case_id');
    }
}
