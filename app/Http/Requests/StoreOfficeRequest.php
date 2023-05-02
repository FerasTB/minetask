<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficeRequest extends FormRequest
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
            'time_per_client' => 'nullable|integer',
            'number' => 'nullable|integer',
            'address' => 'required|string',
            'office_image' => 'nullable|text',
            'office_name' => 'required|string',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'date_format:H:i:s|nullable',
        ];
    }
}
