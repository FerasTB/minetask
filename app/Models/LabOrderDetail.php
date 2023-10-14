<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = ['note', 'materials', 'color', 'kind_of_work'];

    public function order()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function teeth()
    {
        return $this->hasMany(Tooth::class, 'lab_order_detail_id');
    }
}
