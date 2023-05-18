<?php

namespace App\Http\Resources;

use App\Enums\COAType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class COAResource extends JsonResource
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
            'office' => new OfficeResource($this->office),
            'doctor' => new DoctorResource($this->doctor),
            'note' => $this->note,
            'initial_balance' => $this->initial_balance,
            'type' => COAType::getKey($this->type),
        ];
    }
}
