<?php

namespace App\Http\Resources;

use App\Models\AccountingProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceReceiptsResource extends JsonResource
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
            'date_of_payment' => $this->date_of_payment,
            'total_price' => $this->total_price,
            'account' => new AccountingProfileResource($this->whenLoaded('account')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'items' => InvoiceItemsResource::collection($this->whenLoaded('items')),
            'running_balance' => $this->running_balance,
            'invoice_number' => $this->invoice_number,
            'created_at' => $this->created_at,
        ];
    }
}
