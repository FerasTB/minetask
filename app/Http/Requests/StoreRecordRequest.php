<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecordRequest extends FormRequest
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
            'case_id' => 'required|integer',
            'appointment_id' => 'required|integer',
            'description' => 'nullable|string',
        ];
    }
}
