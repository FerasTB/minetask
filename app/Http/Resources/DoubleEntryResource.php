<?php

namespace App\Http\Resources;

use App\Enums\DoubleEntryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoubleEntryResource extends JsonResource
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
            'type' => DoubleEntryType::getKey($this->type),
            'total_price' => $this->total_price,
            'receipt' => new ReceiptResource($this->receipt),
            'invoice' => new InvoiceResource($this->invoice),
            'invoice_item' => new InvoiceItemsResource($this->invoiceItem),
        ];
    }
}
