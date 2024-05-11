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
            'patientCase' => 'required|integer',
            // 'office_id' => 'required|integer',
            'patient_id' => 'required|integer',
            'diagnosis' => 'required|string',
            'diagnosis_teeth' => 'array|required',
            'diagnosis_teeth.teeth' => 'array|required',
            'diagnosis_teeth.teeth.*' => 'integer|required',
            'description' => 'required|string',
            'after_treatment_instruction' => 'nullable|string',
            'operations' => 'array|required',
            'operations.*' => 'array|required',
            'operations.*.record_id' => 'integer|required',
            'operations.*.operation_description' => 'string|nullable',
            'operations.*.operation_name' => 'string|required',
            'operations.*.teeth' => 'array|required',
            'operations.*.teeth.*' => 'integer|required',
        ];
    }
}
