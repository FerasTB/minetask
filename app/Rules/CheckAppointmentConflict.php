<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Doctor;
use Carbon\Carbon;

class CheckAppointmentConflict implements Rule
{
    protected $doctorId;
    protected $officeId;
    protected $startTime;
    protected $endTime;

    public function __construct($doctorId, $officeId, $startTime, $endTime)
    {
        $this->doctorId = $doctorId;
        $this->officeId = $officeId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function passes($attribute, $value)
    {
        // Convert to Carbon instances for easier date manipulation
        $start = Carbon::parse($this->startTime);
        $end = Carbon::parse($this->endTime);

        // Fetch the doctor's appointments in the same office
        $appointments = Doctor::find($this->doctorId)
            ->appointments()
            ->whereDate('taken_date', $start->format('Y-m-d'))
            ->whereHas('office', function ($query) {
                $query->where('office_id', $this->officeId);
            })
            ->get();

        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment->start_time);
            $appointmentEnd = Carbon::parse($appointment->end_time);

            // Check if the new appointment conflicts with existing ones
            if ($start < $appointmentEnd && $end > $appointmentStart) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The selected time slot conflicts with an existing appointment in the same office.';
    }
}
