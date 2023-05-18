<?php

namespace App\Http\Resources;

use App\Enums\OfficeType;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
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
        $doctor = Doctor::find($this->doctor_id);
        $office = Office::find($this->office_id);
        if ($office->type == OfficeType::Separate) {
            $role = HasRole::where(['roleable_id' => $this->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->get();
        } else {
            $role = HasRole::where(['roleable_id' => $this->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => $office->owner->user_id])->first();
        }
        return [
            'patient' => new MyPatientsResource($role),
            'doctor' => new DoctorResource($doctor),
            'initial_balance' => $this->initial_balance,
            'invoice' => DebtResource::collection($this->debts),
            'receipts' => ReceiptResource::collection($this->receipts),
        ];
    }
}
