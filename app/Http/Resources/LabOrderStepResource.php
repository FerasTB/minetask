<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabOrderStepResource extends JsonResource
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
            'note' => $this->note,
            'rank' => $this->rank,
            'order' => new LabOrderResource($this->whenLoaded('order')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'is_finished' => $this->isFinished,
        ];
    }
}
