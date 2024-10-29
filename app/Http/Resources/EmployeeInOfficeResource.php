<?php

namespace App\Http\Resources;

use App\Enums\SubRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

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
        // $token = $this->getOrCreateToken($user);
        return [
            'sub_role' => SubRole::getKey($this->sub_role),
            'user' => $this->sub_role == SubRole::OfficeSecretary ?  $user->patient :  new DoctorResource($user->doctor),
            'setting' => new EmployeeSettingResource($this->setting),
            // 'token' => $token,
            'phone' => $user->phone,
            'properties' => HasRolePropertyResource::collection($this->properties),
        ];
    }

    private function getOrCreateToken(User $user)
    {
        // Generate a cache key based on the user's ID
        $cacheKey = 'user_token_' . $user->id;

        // Check if the token exists in the cache
        $cachedToken = Cache::get($cacheKey);

        if ($cachedToken) {
            // Return the cached token
            return $cachedToken;
        } else {
            // Create a new token for the user
            $newToken = $user->createToken('medcare_app')->plainTextToken;

            // Store the new token in the cache for 24 hours
            Cache::put($cacheKey, $newToken, now()->addHours(24));
            return $newToken;
        }
    }
}
