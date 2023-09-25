<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDentalLabSupplierAccountingProfileRequest extends FormRequest
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
            'supplier_name' => 'required|string',
            'lab_id' => 'required|integer',
            'note' => 'nullable|string',
            'initial_balance' => 'nullable|integer',
            'type' => ['required', Rule::in(['SupplierAccount'])],
        ];
    }
}
