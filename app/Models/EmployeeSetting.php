<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSetting extends Model
{
    use HasFactory;

    protected $fillable = ['coa_id', 'target', 'doctors', 'note', 'doctor_id', 'rate_type', 'rate', 'salary', 'has_role_id'];

    public function role()
    {
        return $this->belongsTo(HasRole::class, 'has_role_id');
    }
}
