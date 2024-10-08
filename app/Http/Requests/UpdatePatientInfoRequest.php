<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientInfoRequest extends FormRequest
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
            'first_name' => 'string|required',
            'father_name' => 'string|nullable',
            'marital' => ['nullable', Rule::in(MaritalStatus::getKeys())],
            'mother_name' => 'string|nullable',
            'last_name' => 'string|required',
            'email' => 'email|nullable',
            'birth_date' => 'date|nullable',
            'gender' => ['required', Rule::in(Gender::getValues())],
        ];
    }
}
