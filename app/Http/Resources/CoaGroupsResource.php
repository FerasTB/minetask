<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoaGroupsResource extends JsonResource
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
            'office' => new OfficeResource($this->whenLoaded('office')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'note' => $this->note,
            'coas' => COAResource::collection($this->whenLoaded('COAS')),
        ];
    }
}
