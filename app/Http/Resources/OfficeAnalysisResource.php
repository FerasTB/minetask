<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeAnalysisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'appointments' => $this->appointments ? $this->appointments->count() : "0",
            // 'teethRecords' => $this->teethRecords ? $this->teethRecords->count : "0",
        ];
    }
}
