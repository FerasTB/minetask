<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorsAccessRequest extends FormRequest
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
            'doctors' => 'required|array|min:1',
            'doctors.*.doctor_id' => 'required|integer|exists:users,id', // Assuming doctors are users
            'doctors.*.approve' => 'required|boolean',
        ];
    }
}
