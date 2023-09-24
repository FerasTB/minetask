<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelHasRole extends Model
{
    use HasFactory;

    protected $fillable = ['roleable_type', 'roleable_id', 'role_id'];

    public function roleable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'roleable_type', 'roleable_id');
    }
}
