<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSettingResource extends JsonResource
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
            'note' => $this->note,
            'doctor_id' => $this->doctor_id,
            'rate_type' => $this->rate_type,
            'rate' => $this->rate,
            'salary' => $this->salary,
            'doctors' => $this->doctors,
            'coa_id' => $this->coa_id,
            'target' => $this->target,
        ];
    }
}
