<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryTask extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'data', 'task_type'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
