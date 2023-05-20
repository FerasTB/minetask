<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tooth extends Model
{
    use HasFactory;

    protected $fillable = ['number_of_tooth', 'operation_id', 'diagnosis_id'];

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class, 'diagnosis_id');
    }
}
