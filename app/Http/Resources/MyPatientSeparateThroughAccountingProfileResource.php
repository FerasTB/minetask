<?php

namespace App\Http\Resources;

use App\Enums\DoctorRoleForPatient;
use App\Models\HasRole;
use App\Models\Patient;
use App\Models\TemporaryInformation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyPatientSeparateThroughAccountingProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $role = HasRole::where(['roleable_id' => $this->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->first();
        $role = auth()->user()->roles->where('roleable_id', $this->patient_id)->where('roleable_type', 'App\Models\Patient')->first();
        if ($role) {
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
                    'status' => 'Approve'
                ];
            }
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
                // $patient = TemporaryInformation::where(['patient_id' => $role->roleable_id, 'doctor_id' => auth()->user()->doctor->id])->first();
                $patient = $this->temporaries->where('patient_id', $this->id)->where('doctor_id', auth()->user()->doctor->id)->first();
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
                    'status' => 'WithoutApprove',
                    'TemporaryId' => $patient->id,
                ];
            }
        }
        return parent::toArray($request);
    }
}
