<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalServiceResource extends JsonResource
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
            'COA' => new COAResource($this->COA),
            'doctor' => new DoctorResource($this->doctor),
            'office' => new OfficeResource($this->office),
        ];
    }
}
