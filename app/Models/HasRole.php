<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class HasRole extends Model
{
    use HasFactory;

    protected $fillable = ['sub_role', 'roleable_type', 'roleable_id', 'user_id', 'expense_coa_id', 'revenue_coa_id'];

    public function roleable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'roleable_type', 'roleable_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setting()
    {
        return $this->hasOne(EmployeeSetting::class, 'has_role_id');
    }

    public function properties()
    {
        return $this->hasMany(HasRoleProperty::class, 'has_role_id');
    }
}
