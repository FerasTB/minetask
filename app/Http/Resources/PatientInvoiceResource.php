<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'note' => $this->note,
            'date_of_invoice' => $this->date_of_invoice,
            'total_price' => $this->total_price,
            'office' => $this->office,
            'items' => InvoiceItemsResource::collection($this->items),
        ];
    }
}
