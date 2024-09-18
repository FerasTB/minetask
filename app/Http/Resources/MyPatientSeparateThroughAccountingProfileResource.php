<?php

namespace App\Http\Resources;

use App\Enums\DoctorRoleForPatient;
use App\Enums\MaritalStatus;
use App\Models\HasRole;
use App\Models\Patient;
use App\Models\TemporaryInformation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyPatientSeparateThroughAccountingProfileResource extends JsonResource
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
            $this->doctorUser,
        ];
        // $role = HasRole::where(['roleable_id' => $this->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->first();
        $role = $this->user->roles->where('roleable_id', $this->patient_id)->where('roleable_type', 'App\Models\Patient')->first();
        if ($role) {
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
                $patient = $this->patient;
                return [
                    'id' => $patient->id,
                    'parent_id' => $patient->parent_id,
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
                    'info' => new UserInfoResource($this->patient->info),
                    'image' => $patient->doctorImage != null ?
                        DoctorImageResource::collection($this->patient->doctorImage->where('doctor_id', auth()->user()->doctor->id))
                        : "no image",
                    'medical_info' => new MedicalInformationResource($patient->medicalInformation),
                ];
            }
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
                // $patient = TemporaryInformation::where(['patient_id' => $role->roleable_id, 'doctor_id' => auth()->user()->doctor->id])->first();
                $patient = $this->patient->temporaries->where('doctor_id', auth()->user()->doctor->id)->first();
                $originalPatient = $this->patient;
                return [
                    'id' => $originalPatient->id,
                    'parent_id' => $originalPatient->parent_id,

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
                    'info' => new UserInfoResource($this->patient->info),
                    'TemporaryId' => $patient->id,
                    'image' => $originalPatient->doctorImage != null ?
                        DoctorImageResource::collection($this->patient->doctorImage->where('doctor_id', auth()->user()->doctor->id))
                        : "no image",
                    'medical_info' => new MedicalInformationResource($this->patient->allMedicalInformation->where('doctor_id', auth()->user()->doctor->id)),
                ];
            }
        }
        // return parent::toArray($request);
        return [];
    }
}
