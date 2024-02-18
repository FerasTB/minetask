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
            'entryType' => 'Entry',
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'receipt' => new ReceiptResource($this->receipt),
            'invoice' => new InvoiceResource($this->invoice),
            'invoice_item' => new InvoiceItemsResource($this->invoiceItem),
        ];
    }
}
