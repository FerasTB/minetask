<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Models\MedicalCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $case = MedicalCase::find($this->case_id);
        $appointment = Appointment::find($this->appointment_id);
        return [
            'id' => $this->id,
            'description' => $this->description,
            'case' => new MedicalCaseResource($case),
            'appointment' => new AppointmentResource($appointment),
        ];
    }
}
