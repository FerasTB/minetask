<?php

namespace App\Http\Resources;

use App\Enums\PatientCaseStatus;
use App\Models\MedicalCase;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientCaseResource extends JsonResource
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
            'patient' => new PatientInfoForDoctorResource($this->whenLoaded('patient')),
            'case' => new MedicalCaseResource($this->whenLoaded('case')),
            'teeth_records' => new TeethRecordForAppointmentResource($this->whenLoaded('teethRecords')),
            'status' => PatientCaseStatus::getKey($this->status),
            'time_per_session' => $this->time_per_session,
            'number_of_sessions' => $this->number_of_sessions,
            'sessions_taken' => $this->teethRecords->count(),
            'status' => PatientCaseStatus::getKey($this->status),
            'note' => $this->note,
        ];
    }
}
