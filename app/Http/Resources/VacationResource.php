<?php

namespace App\Http\Resources;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VacationResource extends JsonResource
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
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'taken_date' => $this->taken_date,
            'note' => $this->note,
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'office' => new OfficeResource($this->whenLoaded('office')),
        ];
    }
}
