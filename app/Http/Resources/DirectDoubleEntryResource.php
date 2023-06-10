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
            'type' => DoubleEntryType::getKey($this->type),
            'coa' => new COAResource($this->coa),
        ];
    }
}
