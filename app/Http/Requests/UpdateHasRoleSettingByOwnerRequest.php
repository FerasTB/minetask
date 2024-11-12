<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHasRoleSettingByOwnerRequest extends FormRequest
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
            'rate' => 'nullable|decimal|max:100',
            'salary' => 'nullable|integer',
            // 'doctors' => 'required|array',
            // 'doctors.*' => 'exists:doctors,id',
            'coa_id' => 'nullable|exists:c_o_a_s,id',
            'target' => 'nullable|integer',
        ];
    }
}
