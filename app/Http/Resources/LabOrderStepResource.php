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
            // 'current_step' => $this->whenLoaded('order.currentStep'),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'employee' => new DentalLabEmployeeResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'is_finished' => $this->isFinished,
        ];
    }
}
