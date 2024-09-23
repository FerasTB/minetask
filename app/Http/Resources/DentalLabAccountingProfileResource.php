<?php

namespace App\Http\Resources;

use App\Enums\AccountingProfileType;
use App\Enums\DentalDoctorTransaction;
use App\Enums\DentalLabTransaction;
use App\Enums\DentalLabType;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Api\AccountingProfileController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalLabAccountingProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $positive = $this->invoices
            ->whereIn('type', DentalLabTransaction::getValues())
            ->where('status', '!=', TransactionStatus::Canceled);
        $totalPositiveDentalLab = $positive != null ?
            $positive->sum('total_price') : 0;
        $negative = $this->receipts
            ->whereIn('type', DentalLabTransaction::getValues())
            ->where('status', '!=', TransactionStatus::Canceled);
        $totalNegativeDentalLab = $negative != null ?
            $negative->sum('total_price') : 0;
        $negative = $this->invoices
            ->whereIn('type', DentalDoctorTransaction::getValues())
            ->where('status', '!=', TransactionStatus::Canceled);
        $totalNegativeDoctor = $negative != null ?
            $negative->sum('total_price') : 0;
        $positive = $this->receipts
            ->whereIn('type', DentalDoctorTransaction::getValues())
            ->where('status', '!=', TransactionStatus::Canceled);
        $totalPositiveDoctor = $positive != null ?
            $positive->sum('total_price') : 0;
        return [
            'id' => $this->id,
            // 'patient' => new MyPatientsResource($role),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'lab' => new DentalLabResource($this->whenLoaded('lab')),
            'supplier_name' => $this->supplier_name,
            'initial_balance' => $this->initial_balance,
            'secondary_initial_balance' => $this->secondary_initial_balance,
            'type' => AccountingProfileType::getKey($this->type),
            'invoice' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
            'order' => LabOrderResource::collection($this->whenLoaded('labOrders')),
            'office' => new OfficeResource($this->whenLoaded('office')),
            'new' => $this->whenLoaded('receipts') != [] || $this->whenLoaded('invoices') != [] ? false : true,
            // 'total' => AccountingProfileController::accountOutcomeInt($this->id)
            'totalForLab' => $totalPositiveDentalLab - $totalNegativeDentalLab + $this->initial_balance,
            'totalForDoctor' => $this->total_balance + $this->initial_balance,
        ];
    }
}
