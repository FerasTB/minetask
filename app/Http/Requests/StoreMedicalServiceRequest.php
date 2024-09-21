<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalServiceRequest extends FormRequest
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
            'office_id' => 'required|integer',
            // 'doctor_id' => 'required|integer',
            'cost' => 'nullable|integer',
            'description' => 'nullable|string',
            'name' => 'required|string',
            'COA_id' => 'required|integer',
        ];
    }
}
