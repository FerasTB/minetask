<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
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
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'after:start_time|date_format:H:i:s|required',
            'taken_date' => 'date|required',
            'status_id' => ['required', 'integer', Rule::in([1, 2, 3])],
            'patient_id' => 'required|integer',
            'doctor_id' => 'nullable|integer',
            'office_id' => 'required|integer',
        ];
    }
}