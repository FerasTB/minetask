<?php

namespace App\Http\Resources;

use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'role' => Role::getKey($this->role),
            'current_role' => $this->current_role_id == null ? 'noRole' : $this->currentRole->name,
            'current_office' => $this->current_office_id == null ? 'noOffice' : $this->current_office_id,
            'roles' => RoleResource::collection($this->allRoles),
            // 'info' => auth()->user()->info ?  new UserInfoResource(auth()->user()->info) : null,
            'created_at' => $this->created_at,
            'patient' => $this->whenLoaded('patient'),
            'doctor' => $this->whenLoaded('doctor'),
        ];
    }
}
