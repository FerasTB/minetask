<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Language extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users(): MorphToMany
    {
        return $this->morphedByMany(UserInfo::class, 'languageable', 'model_has_languages');
    }
}
