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

        $toothNames = [
            1 => 'السن القاطع (رباعية)',
            2 => 'السن القاطع (ثنية)',
            3 => 'سن الناب',
            4 => 'السن الضاحك الأول',
            5 => 'السن الضاحك الثاني',
            6 => 'السن الطاحن الأول',
            7 => 'السن الطاحن الثاني',
            8 => 'السن الطاحن الثالث (سن العقل)',
        ];

        if (isset($quadrantNames[$quadrant]) && isset($toothNames[$tooth])) {
            return $quadrantNames[$quadrant] . "، " . $toothNames[$tooth];
        }

        return "سن غير معروف";
    }
}
