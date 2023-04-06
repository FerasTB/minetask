<?php

namespace App\Models;

use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeethRecord extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'appointment_id', 'patientCase_id', 'report_id'];

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id')->where('report_type', ReportType::TeethReport);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function PatientCase()
    {
        return $this->belongsTo(PatientCase::class, 'patientCase_id');
    }

    public function diagnosis()
    {
        return $this->hasOne(Diagnosis::class, 'record_id');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'record_id');
    }
}
