<?php

namespace App\Http\Requests;

use App\Enums\DentalLabType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDentalLabRequest extends FormRequest
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
            'address' => 'required|string',
            'image' => 'nullable|text',
            'name' => 'required|string',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'date_format:H:i:s|nullable',
            'type' => ['nullable', Rule::in(DentalLabType::getKeys())],
        ];
    }
}
