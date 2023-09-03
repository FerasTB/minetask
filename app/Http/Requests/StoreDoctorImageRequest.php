<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorImageRequest extends FormRequest
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
            'teeth_record_id' => 'nullable|integer',
            'patient_id' => 'required|integer',
            'name' => 'nullable|string',
            'note' => 'nullable|string',
            'image' => 'image|required',
        ];
    }
}
