<?php

namespace App\Http\Resources;

use App\Http\Controllers\Api\AccountingProfileController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalLabAccountingProfileResource extends JsonResource
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
            // 'patient' => new MyPatientsResource($role),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'supplier_name' => $this->supplier_name,
            'initial_balance' => $this->initial_balance,
            'type' => $this->type,
            'invoice' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
            'office_id' => new OfficeResource($this->whenLoaded('office')),
            'total' => AccountingProfileController::accountOutcomeInt($this->id)
        ];
    }
}
