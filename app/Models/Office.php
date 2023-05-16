<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_consultation_fee', 'address',
        'office_image', 'office_name', 'number',
        'start_time', 'end_time', 'type',
    ];


    public function availabilities()
    {
        return $this->hasMany(availability::class, 'office_id');
    }

    public function services()
    {
        return $this->hasMany(MedicalService::class, 'office_id');
    }

    public function roles(): MorphMany
    {
        return $this->morphMany(HasRole::class, 'roleable');
    }

    public function owner(): MorphOne
    {
        return $this->morphOne(HasRole::class, 'roleable')->oldestOfMany();
    }
}
