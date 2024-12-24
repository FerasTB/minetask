<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemsResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'total_price' => $this->total_price,
            'price_per_one' => $this->price_per_one,
            'teeth_record_number' => $this->teeth_record_id ? $this->teethRecord->unique_number : 0,
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
        ];
    }
}
