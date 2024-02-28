<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInfo extends Model
{
    use HasFactory;

    protected $fillable = ['country', 'numberPrefix', 'current_language_id'];

    public function currentLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'current_language_id');
    }

    public function allLanguage()
    {
        return $this->morphToMany(Language::class, 'languageable', 'model_has_languages', 'languageable_id');
    }

    public function hasLanguage(Language $lang)
    {
        return UserInfo::whereHas(
            'allLanguage',
            function ($query) use ($lang) {
                $query->where([
                    'language_id' => $lang->id,
                    'languageable_id' => auth()->user()->info->id,
                    'languageable_type' => 'App\Models\UserInfo',
                ]);
            }
        )->first() != null;
    }
}
