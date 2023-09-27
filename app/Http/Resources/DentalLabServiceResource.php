<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalLabServiceResource extends JsonResource
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
            'COA' => new DentalLabCOAResource($this->whenLoaded('COA')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
        ];
    }
}
