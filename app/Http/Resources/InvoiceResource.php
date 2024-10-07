<?php

namespace App\Http\Resources;

use App\Enums\DentalDoctorTransaction;
use App\Enums\DentalLabTransaction;
use App\Enums\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'note' => $this->note,
            'date_of_invoice' => $this->date_of_invoice,
            'total_price' => $this->total_price,
            'office' => new OfficeResource($this->whenLoaded('office')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'patient' => new PatientInfoForDoctorResource($this->whenLoaded('patient')),
            'items' => InvoiceItemsResource::collection($this->whenLoaded('items')),
            'receipts' => $this->receipts,
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
            'running_balance' => $this->running_balance,
            // 'supplier' => $this->whenLoaded('account.supplier_name'),
            'supplier' => new SupplierNameResource($this->whenLoaded('account')),
            'invoice_number' => $this->invoice_number,
            'creator' => $this->creator ? $this->creator->full_name : null,

            'created_at' => $this->created_at,
            'teeth_record_id' => $this->teeth_record_id,
        ];
    }
}
