<?php

namespace App\Http\Resources;

use App\Enums\DoctorRoleForPatient;
use App\Models\HasRole;
use App\Models\Patient;
use App\Models\TemporaryInformation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientInfoForDoctorResource extends JsonResource
{


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $role = $this->roles->firstWhere('roleable_type', 'App\Models\Patient');

        if ($role) {
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
                return [
                    'id' => $this->id,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'phone' => '0' . $this->phone,
                    'email' => $this->email,
                    'birth_date' => $this->birth_date,
                    'gender' => $this->gender,
                    'note' => $this->note,
                    'status' => 'Approve'
                ];
            }

            if ($role->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
                $patient = $this->temporaries->first();
                return [
                    'id' => $this->id,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'phone' => '0' . $this->phone,
                    'email' => $patient->email,
                    'birth_date' => $patient->birth_date,
                    'note' => $patient->note,
                    'gender' => $this->gender,
                    'status' => 'WithoutApprove',
                    'TemporaryId' => $patient->id,
                ];
            }
        }
        return null;
    }
}
