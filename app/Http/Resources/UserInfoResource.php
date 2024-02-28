<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoResource extends JsonResource
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
            'country' => $this->country,
            'numberPrefix' => $this->numberPrefix,
            'languages' => LanguageResource::collection($this->whenLoaded('allLanguage')),
            'current_language' => $this->current_language_id == null ? 'noLanguage' : $this->currentLanguage->name,
        ];
    }
}
