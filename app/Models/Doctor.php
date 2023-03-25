<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected   $fillable = ['first_name', 'last_name', 'practicing_from'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'doctor_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'doctor_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'doctor_id');
    }

    public function cases()
    {
        return $this->hasMany(MedicalCase::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
}
