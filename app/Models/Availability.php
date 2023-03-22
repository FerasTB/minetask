<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason_unavailability', 'is_available', 'end_time',
        'start_time', 'day_name', 'office_id'
    ];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'availability_id');
    }
}
