<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentStatus extends Model
{
    use HasFactory;

    protected $fillable = ['status_name'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'status_id');
    }
}
