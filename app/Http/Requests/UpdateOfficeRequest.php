<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeRequest extends FormRequest
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
            'first_consultation_fee' => 'nullable|integer',
            'followup_consultation_fee' => 'nullable|integer',
            'time_per_client' => 'nullable|integer',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
        ];
    }
}
