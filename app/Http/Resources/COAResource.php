<?php

namespace App\Http\Resources;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Http\Controllers\Api\COAController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class COAResource extends JsonResource
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
            'name' => $this->name,
            'office' => new OfficeResource($this->whenLoaded('office')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'note' => $this->note,
            'initial_balance' => $this->initial_balance,
            'sub_type' => COASubType::getKey($this->sub_type),
            'general_type' => COAGeneralType::getKey($this->general_type),
            'type' => COAType::getKey($this->type),
            'entry' => DoubleEntryResource::collection($this->whenLoaded('doubleEntries')),
            'total' => COAController::coaOutcomeInt($this->id),
        ];
    }
}
