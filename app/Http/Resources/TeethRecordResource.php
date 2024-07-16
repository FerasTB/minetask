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
        return [
            'id' => $this->id,
            'description' => $this->description,
            'is_closed' => $this->is_closed,
            'patientCase' => new PatientCaseResource($this->PatientCase),
            'doctor' => new DoctorResource($this->PatientCase->case->doctor),
            'number_of_teeth' => $this->number_of_teeth,
            'after_treatment_instruction' => $this->after_treatment_instruction,
            'anesthesia_type' => $this->anesthesia_type,
            'appointment' => $this->appointment != null ? new AppointmentResource($this->appointment) : "no appointment",
            'diagnosis' => new DiagnosisResource($this->diagnosis),
            'operations' => OperationResource::collection($this->whenLoaded('operations')),
            'image' => DoctorImageResource::collection($this->doctorImage),
        ];
    }
}
