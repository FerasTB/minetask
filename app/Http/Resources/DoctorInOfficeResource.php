<?php

namespace App\Http\Resources;

use App\Enums\SubRole;
use App\Models\EmployeeSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorInOfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->user_id);
        return [
            'sub_role' => SubRole::getKey($this->sub_role),
            'user' => $this->sub_role == SubRole::OfficeSecretary ?  $user->patient :  new DoctorResource($user->doctor),
            'setting' => new EmployeeSettingResource($this->setting),
        ];
    }
}
