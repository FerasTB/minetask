<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientInvoiceItemRequest extends FormRequest
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
            'name' => 'required|string',
            'description' => 'nullable|string',
            'amount' => 'required|integer',
            'total_price' => 'required|integer',
            'price_per_one' => 'required|integer',
            'service_coa' => 'required|integer',
            'service_percentage' => 'required|integer',
            // 'cash_coa' => 'required|integer',
        ];
    }
}
