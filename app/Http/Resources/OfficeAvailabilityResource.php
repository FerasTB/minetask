<?php

namespace App\Http\Resources;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeAvailabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $doctor = Doctor::find($this->doctor_id);
        return [
            'id' => $this->id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'day_name' => $this->day_name,
            'is_available' => (bool)$this->is_available,
            'reason_unavailability' => $this->reason_unavailability,
            'created_at' => $this->created_at,
            'doctor' => new DoctorResource($doctor),
        ];
    }
}
