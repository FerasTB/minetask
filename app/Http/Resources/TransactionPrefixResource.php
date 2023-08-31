<?php

namespace App\Http\Resources;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionPrefixResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'last_transaction_number' => $this->last_transaction_number,
            'type' => TransactionType::getValue($this->type),
            'prefix' => $this->prefix,
        ];
    }
}
