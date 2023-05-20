<?php

namespace App\Http\Resources;

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
            'office' => new OfficeResource($this->office),
            'doctor' => new DoctorResource($this->doctor),
            'items' => InvoiceItemsResource::collection($this->items),
            'created_at' => $this->created_at,
        ];
    }
}
