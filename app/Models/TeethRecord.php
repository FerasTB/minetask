<?php

namespace App\Models;

use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeethRecord extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Check the last record to get its "unique_number"
            $lastRecord = TeethRecord::orderBy('id', 'desc')->first();

            $lastNumber = $lastRecord?->unique_number ?? 0;

            // Increment by 1 for the new record
            $model->unique_number = $lastNumber + 1;
        });
    }

    protected $fillable = ['is_closed', 'description', 'appointment_id', 'patientCase_id', 'report_id', 'number_of_teeth', 'after_treatment_instruction', 'anesthesia_type'];

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

    public function doctorImage()
    {
        return $this->hasMany(DoctorImage::class, 'teeth_record_id');
    }
}
