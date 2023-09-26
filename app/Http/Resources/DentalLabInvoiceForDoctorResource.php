<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalLabInvoiceForDoctorResource extends JsonResource
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
            'lab' => new DentalLabResource($this->lab),
            'doctor' => new DoctorResource($this->doctor),
            'office' => new OfficeResource($this->office),
            'items' => InvoiceItemsResource::collection($this->items),
            'invoice_number' => $this->invoice_number,
            'created_at' => $this->created_at,
        ];
    }
}
