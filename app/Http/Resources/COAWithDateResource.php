<?php

namespace App\Http\Resources;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class COAWithDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'office' => new OfficeResource($this->whenLoaded('office')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'group' => new CoaGroupsResource($this->whenLoaded('group')),
            'note' => $this->note,
            'initial_balance' => $this->initial_balance,
            'sub_type' => COASubType::getKey($this->sub_type),
            'general_type' => COAGeneralType::getKey($this->general_type),
            'type' => COAType::getKey($this->type),
            'entry' => DoubleEntryResource::collection($this->whenLoaded('doubleEntries')),
            'direct_double_entry' => DirectDoubleEntryResource::collection($this->whenLoaded('directDoubleEntries')),
            // 'opening_balance' => $fromDate ? $this->getOpeningBalance($fromDate) : $this->initial_balance,
            'closing_balance' => $this->calculateTotal(null, $toDate),
            'total' => $this->calculateTotal($fromDate, $toDate),
        ];
    }
}
