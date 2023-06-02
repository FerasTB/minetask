<?php

namespace App\Http\Resources;

use App\Enums\AppointmentStatus as EnumsAppointmentStatus;
use App\Enums\DoctorRoleForPatient;
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
        $doctor = Doctor::find($this->doctor_id);
        $office = Office::find($this->office_id);
        $patient = Patient::find($this->patient_id);
        return [
            'id' => $this->id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'taken_date' => $this->taken_date,
            'status' => EnumsAppointmentStatus::getKey($this->status),
            'patient' => new PatientInfoForDoctorResource($patient),
            'doctor' => new DoctorResource($doctor),
            'office' => new OfficeResource($office),
            'patientCase' => new PatientCaseResource($this->patientCase),
            'note' => $this->note,
            'step' => $this->step,
            'color' => $this->color,
        ];
    }
}
