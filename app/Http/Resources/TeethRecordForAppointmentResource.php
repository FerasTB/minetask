<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeethRecordForAppointmentResource extends JsonResource
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
            'number_of_teeth' => $this->number_of_teeth,
            'is_closed' => $this->is_closed,
            'after_treatment_instruction' => $this->after_treatment_instruction,
            'anesthesia_type' => $this->anesthesia_type,
            'diagnosis' => new DiagnosisResource($this->whenLoaded('diagnosis')),
            'operations' => OperationResource::collection($this->whenLoaded('operations')),
        ];
    }
}
