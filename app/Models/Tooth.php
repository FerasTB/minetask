<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tooth extends Model
{
    use HasFactory;

    protected $fillable = ['number_of_tooth', 'operation_id', 'diagnosis_id'];

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class, 'diagnosis_id');
    }

    // Accessor to get tooth name
    public function getToothNameAttribute()
    {
        $number = $this->number_of_tooth;
        $quadrant = (int) ($number / 10);  // تحديد الربع
        $tooth = $number % 10;  // تحديد رقم السن داخل الربع

        $quadrantNames = [
            1 => 'الفك العلوي الأيمن',
            2 => 'الفك العلوي الأيسر',
            3 => 'الفك السفلي الأيسر',
            4 => 'الفك السفلي الأيمن',
        ];

        if (isset($quadrantNames[$quadrant])) {
            return "الربع " . $quadrantNames[$quadrant] . "، سن رقم " . $tooth;
        }

        return "سن غير معروف";
    }
}
