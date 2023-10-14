<?php

namespace App\Http\Resources;

use App\Enums\AccountingProfileType;
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
            'type' => AccountingProfileType::getKey($this->type),
            'invoice' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
            'order' => LabOrderResource::collection($this->whenLoaded('labOrders')),
            'office' => new OfficeResource($this->whenLoaded('office')),
            'new' => $this->whenLoaded('receipts') != [] || $this->whenLoaded('invoices') != [] ? true : false,
            'total' => AccountingProfileController::accountOutcomeInt($this->id)
        ];
    }
}
