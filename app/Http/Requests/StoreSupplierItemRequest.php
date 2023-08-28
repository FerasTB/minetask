<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierItemRequest extends FormRequest
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
            'COA_id' => 'required|integer',
            'doctor_id' => 'nullable|integer',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'cost' => 'nullable|integer',
        ];
    }
}
