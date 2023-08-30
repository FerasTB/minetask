<?php

namespace App\Http\Resources;

use App\Models\Diagnosis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DrugPatientIndexResource extends JsonResource
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
            'drug_name' => $this->drug_name,
            'eat' => $this->eat,
            'portion' => $this->portion,
            'frequency' => $this->frequency,
            'note' => $this->note,
            'diagnosis' => new DiagnosisResource($this->whenLoaded('diagnosis')),
            'created_at' => $this->created_at,
            // 'effect' => $this->effect,
        ];
    }
}
