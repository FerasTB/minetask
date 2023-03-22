<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
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
            'office_id' => 'integer|required',
            'reason_unavailability'  => 'string|nullable',
            'is_available' => 'boolean|nullable',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'after:start_time|date_format:H:i:s|required',
            'day_name' => 'string|required',
        ];
    }
}
