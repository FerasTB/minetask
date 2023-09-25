<?php

namespace App\Http\Resources;

use App\Enums\SubRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalLabThroughHasRoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'role_in_lab' => SubRole::getKey($this->sub_role),
            'lab' => new DentalLabResource($this->whenLoaded('roleable')),
        ];
    }
}
