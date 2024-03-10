<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeWithDoctorsRecourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'office' => new OfficeResource($this),
            'people' => EmployeeInOfficeResource::collection($this->roles),
        ];
    }
}
