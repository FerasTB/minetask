<?php

namespace App\Http\Requests;

use App\Enums\HasRolePropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHasRolePropertyRequest extends FormRequest
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
            'office_id' => 'required|integer',
            'type' => ['required', Rule::in(HasRolePropertyType::getKeys())],
            'read' => 'nullable|bool',
            'write' => 'nullable|bool',
            'edit' => 'nullable|bool',
        ];
    }
}
