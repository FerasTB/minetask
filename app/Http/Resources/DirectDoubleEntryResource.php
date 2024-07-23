<?php

namespace App\Http\Resources;

use App\Enums\DoubleEntryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectDoubleEntryResource extends JsonResource
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
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'running_balance' => $this->running_balance,
            'type' => DoubleEntryType::getValue($this->type),
            'entryType' => 'DirectEntry',
            'coa' => new COAResource($this->whenLoaded('coa')),
        ];
    }
}
