<?php

namespace App\Http\Resources;

use App\Http\Controllers\Api\PatientInfoController;
use App\Models\Doctor;
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
        return [
            'id' => $this->id,
            'office' => new OfficeResource($this->whenLoaded('office')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'case_name' => $this->case_name,
            'closable' => $this->case_name != Doctor::DefaultCase,
        ];
    }
}
