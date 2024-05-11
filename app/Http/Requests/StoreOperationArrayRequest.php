<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationArrayRequest extends FormRequest
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
            'operations' => 'array|required',
            'operations.*' => 'array|required',
            'operations.*.record_id' => 'integer|required',
            'operations.*.operation_description' => 'string|nullable',
            'operations.*.operation_name' => 'string|required',
            'operations.*.teeth' => 'array|required',
            'operations.*.teeth.*' => 'integer|required',
        ];
    }
}
