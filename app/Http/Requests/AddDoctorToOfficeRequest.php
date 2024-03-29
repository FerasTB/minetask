<?php

namespace App\Http\Requests;

use App\Enums\SubRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class AddDoctorToOfficeRequest extends FormRequest
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
            'sub_role' => ['nullable', Rule::in(SubRole::getKeys())],
            'note' => 'string|nullable',
            'doctor_id' => 'integer|required',
            'rate_type' => 'integer|nullable',
            'rate' => 'integer|nullable',
            'salary' => 'integer|nullable',
            'doctors' => 'string|nullable',
        ];
    }
}
