<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalLabItemResource extends JsonResource
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
            'name' => $this->name,
            'cost' => $this->cost,
            'description' => $this->description,
            'note' => $this->note,
            'COA' => new COAResource($this->whenLoaded('COA')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
        ];
    }
}
