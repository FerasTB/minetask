<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeRoom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'office_id'];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'office_room_id');
    }
}
