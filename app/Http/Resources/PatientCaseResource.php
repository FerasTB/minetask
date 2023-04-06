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
        $patient = Patient::find($this->patient_id);
        $case = MedicalCase::find($this->case_id);
        return [
            'patient' => new PatientInfoForDoctorResource($patient),
            'case' => new MedicalCaseResource($case),
            'status' => PatientCaseStatus::getKey($this->status),
            'note' => $this->note,
        ];
    }
}