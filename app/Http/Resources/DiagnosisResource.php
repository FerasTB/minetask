<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosisResource extends JsonResource
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
            'drugs' => DrugResource::collection($this->whenLoaded('drug')),
            'teeth' => ToothResource::collection($this->whenLoaded('teeth')),
        ];
    }
}
