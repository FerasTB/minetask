<?php

namespace App\Http\Resources;

use App\Enums\OfficeType;
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
        // $office = Office::find($this->roleable_id);
        // return [
        //     'id' => $office->id,
        //     'number' => $office->number,
        //     'time_per_client' => $office->time_per_client,
        //     'address' => $office->address,
        //     'office_image' => $office->office_image,
        //     'office_name' => $office->office_name,
        //     'start_time' => $office->start_time,
        //     'end_time' => $office->end_time,
        //     'role_in_office' => SubRole::getKey($this->sub_role),
        //     'type' => OfficeType::getKey($office->type),
        // ];
        return [
            'role_in_office' => SubRole::getKey($this->sub_role),
            'office' => new OfficeResource($this->whenLoaded('roleable')),
            'setting' => new EmployeeSettingResource($this->setting),
            'properties' => HasRolePropertyResource::collection($this->whenLoaded('properties')),
        ];
    }
}
