<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalLabItem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'COA_id', 'dental_lab_id', 'doctor_id', 'description', 'cost'];

    public function COA()
    {
        return $this->belongsTo(COA::class, 'COA_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function lab()
    {
        return $this->belongsTo(DentalLab::class, 'dental_lab_id');
    }
}
