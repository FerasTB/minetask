<?php

namespace App\Http\Resources;

use App\Enums\DoctorRoleForPatient;
use App\Enums\MaritalStatus;
use App\Models\HasRole;
use App\Models\Patient;
use App\Models\TemporaryInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyPatientCombinedThroughAccountingProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $role = HasRole::where(['roleable_id' => $this->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => $this->office->owner->user_id])->first();
        $role = auth()->user()->roles->where('roleable_id', $this->patient_id)->where('roleable_type', 'App\Models\Patient')->first();
        if ($role) {
            $ownerUser = $this->office->owner->user;
            $ownerDoctor = $ownerUser->doctor;
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
                $patient = $this->patient;
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
                    'status' => 'Approve',
                    'image' => $patient->doctorImage != null ?
                        DoctorImageResource::collection($patient->doctorImage->where('doctor_id', auth()->user()->doctor->id))
                        : "no image",
                ];
            }
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
                // $patient = TemporaryInformation::where(['patient_id' => $role->roleable_id, 'doctor_id' => $ownerDoctor->id])->first();
                $patient = TemporaryInformation::where(['patient_id' => $this->patient_id, 'doctor_id' => auth()->user()->doctor->id])->first();
                $originalPatient = $this->patient;
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
                    'image' => $originalPatient->doctorImage != null ?
                        DoctorImageResource::collection($originalPatient->doctorImage->where('doctor_id', auth()->user()->doctor->id))
                        : "no image",
                ];
            }
        }
        return [];
    }
}
