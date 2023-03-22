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
        'first_consultation_fee', 'followup_consultation_fee',
        'time_per_client', 'address', 'city', 'office_image', 'office_name',
    ];


    public function availabilities()
    {
        return $this->hasMany(availability::class, 'office_id');
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