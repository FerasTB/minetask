<?php

namespace App\Http\Resources;

use App\Enums\LabOrderStatus;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabOrderResource extends JsonResource
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
            'received_date' => $this->received_date,
            'delivery_date' => $this->delivery_date,
            'status' => LabOrderStatus::getKey($this->status),
            'steps' => $this->steps,
            'current_step' => $this->currentStep == null ? null : $this->currentStep->rank,
            'order_steps' => LabOrderStepResource::collection($this->whenLoaded('orderSteps')),
            'attached_materials' => $this->attached_materials,
            'note' => $this->note,
            'patient_name' => $this->patient_name,
            'details' => LabOrderDetailResource::collection($this->whenLoaded('details')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'office' => new OfficeResource($this->whenLoaded('office')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'account' => new DentalLabAccountingProfileResource($this->whenLoaded('account')),
        ];
    }
}
