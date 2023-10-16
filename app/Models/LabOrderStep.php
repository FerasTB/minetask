<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrderStep extends Model
{
    use HasFactory;

    protected $fillable = ['rank', 'note', 'name', 'isFinished', 'user_id'];

    public function order()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }
}
