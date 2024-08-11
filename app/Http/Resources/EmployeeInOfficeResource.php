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
        $token = $this->getOrCreateToken($user);
        return [
            'sub_role' => SubRole::getKey($this->sub_role),
            'user' => $this->sub_role == SubRole::OfficeSecretary ?  $user->patient :  new DoctorResource($user->doctor),
            'setting' => new EmployeeSettingResource($this->setting),
            'token' => $token,
            'phone' => $user->phone,
            'properties' => HasRolePropertyResource::collection($this->properties),
        ];
    }

    private function getOrCreateToken(User $user)
    {
        // Check if the user already has a valid token for this app
        $existingToken = $user->tokens()->where('name', 'medcare_app')->first();

        if ($existingToken) {
            // If the existing token is still valid, return it
            return $existingToken->plainTextToken;
        }

        // If no valid token exists, create a new one
        return $user->createToken("medcare_app")->plainTextToken;
    }
}
