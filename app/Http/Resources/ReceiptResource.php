<?php

namespace App\Http\Resources;

use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bool = false;
        if ($this->debt_id != null) {
            $debt = Debt::find($this->debt_id);
            $bool = true;
        }
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'note' => $this->note,
            'debt' => $bool ? new DebtResource($debt) : 'not connected to debt',
        ];
    }
}
