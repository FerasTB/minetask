<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalInformationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role = Role::Doctor;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'Previous_oral_surgeries' => 'nullable|string',
            'medical_condition' => 'nullable|string',
            'used_medicines' => 'nullable|string',
            'is_temporary' => 'nullable|boolean',
            'allergy' => 'nullable|string',
            'doctor_id' => 'required|integer',
            'patient_id' => 'required|integer',
        ];
    }
}