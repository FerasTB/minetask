<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOperationRequest extends FormRequest
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
            'operation_name' => 'nullable|string|required_without_all:operation_description,number_of_tooth',
            'operation_description' => 'nullable|string|required_without_all:operation_name,number_of_tooth',
            'number_of_tooth' => 'nullable|string|required_without_all:operation_name,operation_description',
        ];
    }
}
