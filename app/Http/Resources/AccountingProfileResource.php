<?php

namespace App\Http\Resources;

use App\Enums\AccountingProfileType;
use App\Enums\OfficeType;
use App\Http\Controllers\Api\AccountingProfileController;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountingProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->office->type == OfficeType::Separate) {
            $role = HasRole::where(['roleable_id' => $this->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->first();
        } else {
            $role = HasRole::where(['roleable_id' => $this->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => $this->office->owner->user_id])->first();
        }
        return [
            'id' => $this->id,
            'patient' => new MyPatientsResource($role),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'supplier_name' => $this->supplier_name,
            'initial_balance' => $this->initial_balance,
            'type' => AccountingProfileType::getKey($this->type),
            'invoice' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
            'office_id' => $this->office_id,
            'total' => AccountingProfileController::accountOutcomeInt($this->id)
        ];
    }
}
