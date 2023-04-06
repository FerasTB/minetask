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
        return [
            'id' => $this->id,
            'case_name' => $this->case_name,
        ];
    }
}
