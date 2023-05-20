<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasRoleProperty extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'write', 'read', 'edit'];

    public function role()
    {
        return $this->belongsTo(HasRole::class, 'has_role_id');
    }
}
