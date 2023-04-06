<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeethRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role == Role::Doctor;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'patientCase_id' => 'required|integer',
            'appointment_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'number_of_teeth' => 'nullable|integer',
            'after_treatment_instruction' => 'nullable|string',
            'anesthesia_type' => 'nullable|integer',
        ];
    }
}
