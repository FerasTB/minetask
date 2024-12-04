<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public const ADMIN = '1';
    public const Patient = '2';
    public const DentalDoctor = '3';
    public const DentalDoctorTechnician = '4';
    public const DentalLabDoctor = '5';
    public const DentalLabTechnician = '6';
    // public const DentalLabTechnician = '6';
    public const PhototherapyDoctor = '8';
    public const PhototherapyTechnician = '9';
    public const Technicians = ['DentalDoctorTechnician', 'PhototherapyTechnician', 'DentalLabTechnician'];
    public const AddPatient = ['DentalDoctorTechnician', 'PhototherapyTechnician', 'PhototherapyDoctor', 'DentalDoctor'];


    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'roleable', 'model_has_roles');
    }
}
