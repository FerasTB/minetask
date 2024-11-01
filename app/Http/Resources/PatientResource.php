<?php

namespace App\Http\Resources;

use App\Enums\MaritalStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => 0 . $this->phone,
            'email' => $this->email,
            'birth_date' => $this->birth_date,
            'note' => $this->note,
            'gender' => $this->gender,
            'marital' => MaritalStatus::getKey($this->marital),
            'mother_name' => $this->mother_name,
            'father_name' => $this->father_name,
            'created_at' => $this->created_at,
            'info' => new MedicalInformationResource($this->whenLoaded('info')),
            'user' => new UserToDisplayResource($this->whenLoaded('user')),
        ];
    }
}
