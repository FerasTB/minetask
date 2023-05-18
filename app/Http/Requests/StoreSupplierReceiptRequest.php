<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'debt_id' => 'nullable|integer',
            'note' => 'nullable|string',
            'amount' => 'required|integer',
            'office_id' => 'required|integer',
            'doctor' => 'required|integer',
            'supplier_name' => 'required|string',
        ];
    }
}
