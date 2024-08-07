<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDraftPatientInvoiceItemRequest extends FormRequest
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
            'accounting_profile_id' => 'required|integer|exists:accounting_profiles,id',
        ];
    }
}
