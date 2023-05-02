<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeRequest extends FormRequest
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
            'number' => 'nullable|integer',
            'time_per_client' => 'nullable|integer',
            'address' => 'nullable|string',
            'office_image' => 'nullable|text',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'date_format:H:i:s|nullable',
        ];
    }
}
