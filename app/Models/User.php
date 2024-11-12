<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Psy\TabCompletion\Matcher\FunctionsMatcher;

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
        'current_office_id',
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
    public function getFullNameAttribute()
    {
        if ($this->doctor) {
            return $this->doctor->first_name . ' ' . $this->doctor->last_name;
        } elseif ($this->patient) {
            return $this->patient->first_name . ' ' . $this->patient->last_name;
        } else {
            return $this->name; // Fallback if neither
        }
    }

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

    public function dentalLabSteps()
    {
        return $this->hasMany(LabOrderStep::class, 'user_id');
    }

    public function canAccessFilament(): bool
    {
        return str_ends_with($this->email, '@marstaan.com') && $this->hasVerifiedEmail();
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    public function info(): HasOne
    {
        return $this->hasOne(UserInfo::class, 'user_id');
    }

    public function allRoles()
    {
        return $this->morphToMany(Role::class, 'roleable', 'model_has_roles', 'roleable_id');
    }

    public function hasRole(Role $role)
    {
        return User::whereHas(
            'allRoles',
            function ($query) use ($role) {
                $query->where([
                    'role_id' => $role->id,
                    'roleable_id' => auth()->id(),
                    'roleable_type' => 'App\Models\User',
                ]);
            }
        )->first() != null;
    }

    public function temporaryTasks()
    {
        return $this->hasMany(TemporaryTask::class, 'user_id');
    }
}
