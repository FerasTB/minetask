<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationResource extends JsonResource
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
            'operation_name' => $this->operation_name,
            'operation_description' => $this->operation_description,
            'created_at' => $this->created_at,
            'is_paid' => $this->is_paid,
            'price' => $this->price,
            'teeth' => ToothResource::collection($this->whenLoaded('teeth')),
        ];
    }
}
