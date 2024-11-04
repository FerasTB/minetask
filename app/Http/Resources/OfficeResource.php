<?php

namespace App\Http\Resources;

use App\Enums\OfficeType;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
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
            'number' => $this->number,
            'time_per_client' => $this->time_per_client,
            'address' => $this->address,
            'office_image' => $this->office_image,
            'office_name' => $this->office_name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'specialization' => $this->specialization,
            'rooms' => OfficeRoomResource::collection($this->whenLoaded('rooms')),
            'type' => OfficeType::getKey($this->type),
            // 'owner' => new UserResource($this->whenLoaded('owner')->user),
        ];
    }
}
