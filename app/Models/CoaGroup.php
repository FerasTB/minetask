<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoaGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'note', 'doctor_id', 'office_id'];

    public function COAS()
    {
        return $this->hasMany(COA::class, 'group_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
