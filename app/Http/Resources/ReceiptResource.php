<?php

namespace App\Http\Resources;

use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
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
            'date_of_payment' => $this->note,
            'invoice' => new PatientInvoiceResource($this->invoice),
            'doctor' => new DoctorResource($this->doctor),
            'created_at' => $this->created_at,
        ];
    }
}
