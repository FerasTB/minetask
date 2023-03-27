<?php

namespace App\Http\Resources;

use App\Http\Controllers\Api\PatientInfoController;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalCaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $patient = Patient::find($this->patient_id);
        return [
            'id' => $this->id,
            'case_name' => $this->case_name,
            'is_closed' => (bool)$this->is_closed,
            'payment_fee' => $this->payment_fee,
            'patient' => new PatientInfoForDoctorResource($patient),
        ];
    }
}
