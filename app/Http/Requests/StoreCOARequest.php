<?php

namespace App\Http\Requests;

use App\Enums\COAType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCOARequest extends FormRequest
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
            'name' => 'required|string',
            'office_id' => 'required|integer',
            'doctor_id' => 'nullable|integer',
            'note' => 'nullable|string',
            'initial_balance' => 'nullable|integer',
            'type' => ['required', Rule::in(COAType::getKeys())],
        ];
    }
}
