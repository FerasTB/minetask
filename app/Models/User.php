<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'infoable_id',
        'infoable_type',
        'role',
        'phone',
        'current_role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['roles', 'allRoles'];

    // public function infoable(): MorphTo
    // {
    //     return $this->morphTo();
    // }

    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    public function roles()
    {
        return $this->hasMany(HasRole::class, 'user_id');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function canAccessFilament(): bool
    {
        return str_ends_with($this->email, '@marstaan.com') && $this->hasVerifiedEmail();
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    public function allRoles()
    {
        return $this->morphToMany(Role::class, 'roleable', 'model_has_roles', 'roleable_id');
    }

    public function hasRole(Role $role)
    {
        return $this->allRoles()->wherePivot('role_id', '=', $role->id);
    }
}
