<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrderStep extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;


    protected $fillable = ['rank', 'note', 'name', 'isFinished', 'user_id'];

    public function order()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function lab()
    {
        return $this->belongsToThrough(
            DentalLab::class,
            AccountingProfile::class,
            LabOrder::class,
            null,
            '',
            [
                LabOrder::class => 'lab_order_id',
                AccountingProfile::class => 'accounting_profile_id',
                DentalLab::class => 'dental_lab_id'
            ]
        );
    }
}
