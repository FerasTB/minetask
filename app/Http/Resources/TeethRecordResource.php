<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Models\MedicalCase;
use App\Models\PatientCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeethRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $case = PatientCase::find($this->patientCase_id);
        if ($this->appointment_id) {
            $appointment = Appointment::find($this->appointment_id);
        } else {
            $appointment = false;
        }
        return [
            'id' => $this->id,
            'description' => $this->description,
            'patientCase' => new PatientCaseResource($case),
            'number_of_teeth' => $this->number_of_teeth,
            'after_treatment_instruction' => $this->after_treatment_instruction,
            'anesthesia_type' => $this->anesthesia_type,
            'appointment' => $appointment ? new AppointmentResource($appointment) : "no appointment",
        ];
    }
}
