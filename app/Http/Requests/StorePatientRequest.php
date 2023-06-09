<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
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
            'last_name' => 'string|required',
            'phone' => 'integer|required',
            'email' => 'email|nullable',
            'birth_date' => 'date|nullable',
            'note' => 'nullable|string',
            'gender' => ['required', Rule::in(Gender::getValues())],
            'office_id' => 'integer|required',
        ];
    }
}
