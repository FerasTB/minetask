<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $fillable = ['note', 'amount', 'debt_id'];

    public function patient()
    {
        return $this->belongsToThrough(
            Patient::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Patient::class => 'patient_id'
            ]
        );
    }

    public function doctor()
    {
        return $this->belongsToThrough(
            Doctor::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Doctor::class => 'doctor_id'
            ]
        );
    }
}