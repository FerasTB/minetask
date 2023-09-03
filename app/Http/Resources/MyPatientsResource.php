<?php

namespace App\Http\Resources;

use App\Enums\DoctorRoleForPatient;
use App\Enums\MaritalStatus;
use App\Models\Patient;
use App\Models\TemporaryInformation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyPatientsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
            $patient = Patient::find($this->roleable_id);
            return [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'phone' => 0 . $patient->phone,
                'email' => $patient->email,
                'birth_date' => $patient->birth_date,
                'note' => $patient->note,
                'gender' => $patient->gender,
                'marital' => MaritalStatus::getKey($patient->marital),
                'mother_name' => $patient->mother_name,
                'father_name' => $patient->father_name,
                'created_at' => $patient->created_at,
                'image' => DoctorImageResource::collection($patient->doctorImage()->where('doctor_id', auth()->user()->doctor->id)->get()),
                'status' => 'Approve'
            ];
        }
        if ($this->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
            $patient = TemporaryInformation::where(['patient_id' => $this->roleable_id, 'doctor_id' => auth()->user()->doctor->id])->first();
            $originalPatient = Patient::find($this->roleable_id);
            return [
                'id' => $originalPatient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'phone' => 0 . $originalPatient->phone,
                'email' => $patient->email,
                'birth_date' => $patient->birth_date,
                'gender' => $originalPatient->gender,
                'note' => $patient->note,
                'marital' => MaritalStatus::getKey($patient->marital),
                'mother_name' => $patient->mother_name,
                'father_name' => $patient->father_name,
                'created_at' => $patient->created_at,
                'status' => 'WithoutApprove',
                'TemporaryId' => $patient->id,
                'image' => DoctorImageResource::collection($originalPatient->doctorImage()->where('doctor_id', auth()->user()->doctor->id)->get()),
            ];
        }
        return [];
    }
}
