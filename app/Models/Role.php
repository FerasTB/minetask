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
    public const DentalLabDoctor = '4';
    public const DentalLabTechnician = '5';
    public const DentalDoctorTechnician = '6';


    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'roleable', 'model_has_roles');
    }
}
