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
            $role = auth()->user()->roles->where('roleable_id', $this->patient_id)
                ->where('roleable_type', 'App\Models\Patient')
                ->where('user_id', auth()->id())->first();
        } else {
            $role = auth()->user()->roles->where('roleable_id', $this->patient_id)
                ->where('roleable_type', 'App\Models\Patient')
                ->where('user_id', $this->office->owner->user_id)->first();
            $ownerUser = $this->office->owner->user;
            $ownerDoctor = $ownerUser->doctor;
        }
        $positive = $this->invoices;
        $totalPositive = $positive != null ?
            $positive->sum('total_price') : 0;
        $negative = $this->receipts;
        $totalNegative = $negative != null ?
            $negative->sum('total_price') : 0;
        return [
            'id' => $this->id,
            'patient' => new MyPatientsResource($role),
            'patient' => $this->office->type == OfficeType::Separate ?
                new MyPatientSeparateThroughAccountingProfileResource($this) :
                new MyPatientCombinedThroughAccountingProfileResource($this),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'supplier_name' => $this->supplier_name,
            'initial_balance' => $this->initial_balance,
            'type' => AccountingProfileType::getKey($this->type),
            'invoice' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
            'invoice_receipt' => InvoiceReceiptsResource::collection($this->whenLoaded('invoiceReceipt')),
            'office_id' => $this->office_id,
            // 'total' => AccountingProfileController::accountOutcomeInt($this->id)
            'total' => $totalPositive - $totalNegative + $this->initial_balance
        ];
    }
}
