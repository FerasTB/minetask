<?php

namespace App\Http\Resources;

use App\Enums\DentalDoctorTransaction;
use App\Enums\DentalLabTransaction;
use App\Enums\TransactionStatus;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
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
            'total_price' => $this->total_price,
            'date_of_payment' => $this->date_of_payment,
            'note' => $this->note,
            'invoice' => new PatientInvoiceResource($this->invoice),
            'office' => new OfficeResource($this->whenLoaded('office')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'patient' => new PatientInfoForDoctorResource($this->whenLoaded('patient')),
            'running_balance' => $this->running_balance,
            'status' => TransactionStatus::getKey($this->status),
            'type' => $this->type != null ? (in_array($this->type, DentalDoctorTransaction::getValues()) ?
                DentalDoctorTransaction::getKey($this->type) :
                DentalLabTransaction::getKey($this->type)) :
                null,
            'prefix' => $this->type != null ? (in_array($this->type, DentalDoctorTransaction::getValues()) ?
                DentalDoctorTransaction::getNewValue($this->type) :
                DentalLabTransaction::getNewValue($this->type)) :
                null,
            'isForDentalDoctor' => $this->type != null ? in_array($this->type, DentalDoctorTransaction::getValues()) : null,
            'isForDentalLab' => $this->type != null ? in_array($this->type, DentalLabTransaction::getValues()) : null,
            'supplier' => new SupplierNameResource($this->whenLoaded('account')),
            'created_at' => $this->created_at,
            'receipt_number' => $this->receipt_number,
        ];
    }
}
