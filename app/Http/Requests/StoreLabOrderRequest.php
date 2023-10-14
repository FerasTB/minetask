<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabOrderRequest extends FormRequest
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
            'note' => 'string|nullable',
            'attached_materials' => 'string|nullable',
            'patient_id' => 'integer|required',
            'details' => 'array|required',
            'details.*' => 'array|required',
            'details.*.note' => 'string|nullable',
            'details.*.materials' => 'string|nullable',
            'details.*.color' => 'string|nullable',
            'details.*.kind_of_work' => 'string|required',
            'details.*.teeth' => 'array|required',
            'details.*.teeth.*' => 'integer|required',
        ];
    }
}
