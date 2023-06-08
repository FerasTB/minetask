<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugList extends Model
{
    use HasFactory;

    protected $fillable = ['drug_name', 'eat', 'portion', 'frequency', 'effect', 'doctor_id', 'diagnosis_id'];
}
