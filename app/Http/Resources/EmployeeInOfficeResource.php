<?php

namespace App\Http\Resources;

use App\Enums\SubRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeInOfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->user_id);
        $token = $user->tokens()->first();
        return [
            'sub_role' => SubRole::getKey($this->sub_role),
            'user' => $this->sub_role == SubRole::OfficeSecretary ?  $user->patient :  new DoctorResource($user->doctor),
            'setting' => new EmployeeSettingResource($this->setting),
            'token' => (string)$token->id + '|' + $token->token,
            'properties' => HasRolePropertyResource::collection($this->properties),
        ];
    }
}
