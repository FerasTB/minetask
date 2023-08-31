<?php

namespace App\Http\Resources;

use App\Enums\DoubleEntryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectDoubleEntryInvoiceResource extends JsonResource
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
            'note' => $this->note,
            'date_of_transaction' => $this->date_of_transaction,
            'receipt_number' => $this->receipt_number,
            'direct_double_entry' => DirectDoubleEntryResource::collection($this->directDoubleEntries),
        ];
    }
}
