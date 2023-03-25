<?php

namespace App\Http\Resources;

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
        ];
    }
}
