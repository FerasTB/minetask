<?php

namespace App\Http\Resources;

use App\Enums\AccountingProfileType;
use App\Enums\DoctorRoleForPatient;
use App\Enums\OfficeType;
use App\Http\Controllers\Api\AccountingProfileController;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountingProfileResource extends JsonResource
{

    protected $doctorUser;

    public function __construct($resource, $doctorUser)
    {
        parent::__construct($resource);
        $this->doctorUser = $doctorUser;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'patient' => $this->office->type == OfficeType::Separate ?
                new MyPatientSeparateThroughAccountingProfileResource($this, $this->doctorUser) :
                new MyPatientCombinedThroughAccountingProfileResource($this),
            'details' => [
                'id' => $this->id,
                'doctor' => new DoctorResource($this->whenLoaded('doctor')),
                'lab' => new DentalLabResource($this->whenLoaded('lab')),
                'supplier_name' => $this->supplier_name,
                'initial_balance' => $this->initial_balance,
                'type' => AccountingProfileType::getKey($this->type),
                'invoice' => InvoiceResource::collection($this->whenLoaded('invoices')),
                'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
                'invoice_receipt' => InvoiceReceiptsResource::collection($this->whenLoaded('invoiceReceipt')),
                'lab_orders' => $this->type == AccountingProfileType::PatientAccount && $this->relationLoaded('patient') ? LabOrderResource::collection($this->patient->labOrders) : null,
                'office_id' => $this->office_id,
                'total' => $this->total_balance + $this->initial_balance,
            ],
            'default_case' => $this->when(isset($this->default_case), function () {
                return new PatientDefaultCaseResource($this->default_case);
            }),
        ];
    }
}
