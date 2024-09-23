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
    protected $doctorFromController;

    public function __construct($resource, $doctorFromController)
    {
        parent::__construct($resource);
        $this->doctorFromController = $doctorFromController;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
            $patient = $this->roleable;
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
                'image' => $patient->doctorImage != null ?
                    DoctorImageResource::collection($patient->doctorImage->where('doctor_id', $this->doctorFromController->id))
                    : "no image",
                'status' => 'Approve'
            ];
        }
        if ($this->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
            $patient = $this->roleable->temporaries()->where('doctor_id', $this->doctorFromController->id)->first();
            $originalPatient = $this->roleable;
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
                'TemporaryId' => $patient->id,
                'image' => $originalPatient->doctorImage != null ?
                    DoctorImageResource::collection($originalPatient->doctorImage->where('doctor_id', $this->doctorFromController->id))
                    : "no image",
            ];
        }
        return [];
    }
}
