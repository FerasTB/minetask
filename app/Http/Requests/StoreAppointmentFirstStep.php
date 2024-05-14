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
            'patient_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'office_id' => 'required|integer',
            'with_draft' => 'required|boolean',
            'diagnosis' => 'required|string',
            'diagnosis_teeth' => 'array|required',
            'diagnosis_teeth.*' => 'integer|required',
            'description' => 'required|string',
            'after_treatment_instruction' => 'nullable|string',
            'operations' => 'array|nullable',
            'operations.*' => 'array|required',
            'operations.*.operation_description' => 'string|nullable',
            'operations.*.operation_name' => 'string|required',
            'operations.*.coa_id' => 'integer|nullable',
            'operations.*.amount' => 'integer|required',
            'operations.*.total_price' => 'integer|required',
            'operations.*.price_per_one' => 'integer|required',
            'operations.*.teeth' => 'array|required',
            'operations.*.teeth.*' => 'integer|required',
        ];
    }
}
