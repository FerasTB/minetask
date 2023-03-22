<?php

namespace App\Http\Resources;

use App\Enums\SubRole;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeThroughHasRoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $office = Office::find($this->roleable_id);
        return [
            'id' => $office->id,
            'first_consultation_fee' => $office->first_consultation_fee,
            'followup_consultation_fee' => $office->followup_consultation_fee,
            'time_per_client' => $office->time_per_client,
            'city' => $office->city,
            'address' => $office->address,
            'office_image' => $office->office_image,
            'office_name' => $office->office_name,
            'role_in_office' => SubRole::getKey($this->sub_role),
        ];
    }
}
