<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    use HasFactory;

    protected $fillable = ['description'];

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function drug()
    {
        return $this->hasMany(Drug::class, 'diagnosis_id');
    }
}