<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalService extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'cost', 'office_id'];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
