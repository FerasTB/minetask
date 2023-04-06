<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = ['operation_description', 'operation_name'];

    public function record()
    {
        return $this->belongsTo(TeethRecord::class, 'record_id');
    }
}
