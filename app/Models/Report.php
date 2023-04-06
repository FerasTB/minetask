<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['report_type'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function TeethRecords()
    {
        return $this->hasMany(TeethRecords::class, 'report_id');
    }
}
