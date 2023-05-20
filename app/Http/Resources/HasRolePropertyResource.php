<?php

namespace App\Http\Resources;

use App\Enums\HasRolePropertyType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HasRolePropertyResource extends JsonResource
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
            'type' => HasRolePropertyType::getKey($this->type),
            'read' => $this->read,
            'write' => $this->write,
            'edit' => $this->edit,
        ];
    }
}
