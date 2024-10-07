<?php

namespace App\Http\Resources;

use App\Enums\AppointmentStatus as EnumsAppointmentStatus;
use App\Enums\DoctorRoleForPatient;
use App\Enums\PatientInClinicStatus;
use App\Models\AppointmentStatus;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\TemporaryInformation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'taken_date' => $this->taken_date,
            'status' => EnumsAppointmentStatus::getKey($this->status),
            'patient' => new PatientInfoForDoctorResource($this->whenLoaded('patient')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'office' => new OfficeResource($this->whenLoaded('office')),
            // 'patientCase' => new PatientCaseResource($this->patientCase),
            'case' => new PatientCaseResource($this->whenLoaded('case')),
            'room' => new OfficeRoomResource($this->whenLoaded('room')),
            'record' => new TeethRecordForAppointmentResource($this->whenLoaded('record')),
            'note' => $this->note,
            'step' => $this->step,
            'color' => $this->color,
            'color' => $this->creator ? $this->creator->full_name : null,
            'closable' => $this->case_name != Doctor::DefaultCase,
            'is_patient_in_clinic' => PatientInClinicStatus::getKey($this->is_patient_in_clinic),
        ];
    }
}
