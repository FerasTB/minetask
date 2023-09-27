<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalLabService extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'cost', 'dental_lab_id', 'COA_id'];


    public function lab()
    {
        return $this->belongsTo(DentalLab::class, 'dental_lab_id');
    }

    public function COA()
    {
        return $this->belongsTo(COA::class, 'COA_id');
    }
}
