<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentFirstStep extends FormRequest
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
            'appointment_id' => 'nullable|integer',
            'case_id' => 'required|integer',
            // 'office_id' => 'required|integer',
            'patient_id' => 'required|integer',
            'diagnosis' => 'required|string',
            'description' => 'required|string',
        ];
    }
}
