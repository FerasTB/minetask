<?php

namespace App\Http\Resources;

use App\Models\AccountingProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceReceiptsResource extends JsonResource
{
    protected $user;

    public function __construct($resource, $user)
    {
        parent::__construct($resource);
        $this->user = $user;
    }
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
            'account' => new AccountingProfileResource($this->whenLoaded('account'), $this->user),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'office' => new OfficeResource($this->whenLoaded('office')),
            'items' => InvoiceItemsResource::collection($this->whenLoaded('items')),
            // 'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'patient' => new PatientInfoForDoctorResource($this->whenLoaded('patient')),
            'running_balance' => $this->running_balance,
            'type' => 'SellInvoice',
            'prefix' => 'PINV',
            'invoice_number' => $this->invoice_number,
            'created_at' => $this->created_at,
        ];
    }
}
