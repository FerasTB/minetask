<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabOrderDetailResource extends JsonResource
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
            'kind_of_work' => $this->kind_of_work,
            'materials' => $this->materials,
            'color' => $this->color,
            'note' => $this->note,
            'teeth' => LabOrderDetailTeethResource::collection($this->whenLoaded('teeth')),
        ];
    }
}
