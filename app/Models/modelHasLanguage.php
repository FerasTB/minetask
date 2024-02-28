<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class modelHasLanguage extends Model
{
    use HasFactory;

    protected $fillable = ['languageable_type', 'languageable_id', 'language_id'];


    public function languageable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'languageable_type', 'languageable_id');
    }
}
