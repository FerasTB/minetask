<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreDrugRequest extends FormRequest
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
            'diagnosis_id' => 'required|integer',
            'drug_name' => 'required|string',
            'eat' => 'nullable|bool',
            'portion' => 'nullable|string',
            'frequency' => 'nullable|string',
            'note' => 'nullable|string',
            'effect' => 'nullable|string',
        ];
    }
}
