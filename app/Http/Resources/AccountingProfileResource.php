<?php

namespace App\Http\Resources;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountingProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $patient = Patient::find($this->patient_id);
        $doctor = Doctor::find($this->doctor_id);
        return [
            'patient' => new PatientInfoForDoctorResource($patient),
            'doctor' => new DoctorResource($doctor),
            'initial_balance' => $this->initial_balance,
            'invoice' => DebtResource::collection($this->debts),
            'receipts' => ReceiptResource::collection($this->receipts),
        ];
    }
}
