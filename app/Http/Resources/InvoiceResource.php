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
            'items' => InvoiceItemsResource::collection($this->whenLoaded('items')),
            'receipts' => $this->receipts,
            'status' => TransactionStatus::getKey($this->status),
            'type' => in_array($this->type, DentalDoctorTransaction::getValues()) ?
                DentalDoctorTransaction::getKey($this->type) :
                DentalLabTransaction::getKey($this->type),
            'isForDentalDoctor' => in_array($this->type, DentalDoctorTransaction::getValues()),
            'isForDentalLab' => in_array($this->type, DentalLabTransaction::getValues()),
            'running_balance' => $this->running_balance,
            'invoice_number' => $this->invoice_number,
            'created_at' => $this->created_at,
        ];
    }
}
