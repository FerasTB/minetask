<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use App\Enums\PatientInClinicStatus;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(AppointmentStatus::getKeys())],
            'step' => 'nullable|integer',
            'is_patient_in_clinic' => ['nullable', 'string', Rule::in(PatientInClinicStatus::getKeys())],
        ];
    }
}
