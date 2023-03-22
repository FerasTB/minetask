<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['birth_date', 'email', 'phone', 'last_name', 'first_name'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'patient_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function record()
    {
        return $this->hasMany(Record::class, 'patient_id');
    }

    public function temporaries()
    {
        return $this->hasMany(TemporaryInformation::class, 'patient_id');
    }
}
