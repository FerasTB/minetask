<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreDentalLabForDoctorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->currentRole->id == Role::DentalDoctor;
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
            'coa_id' => 'nullable|integer',
            'initial_balance' => 'nullable|integer',
            'note' => 'nullable|string',
            'address' => 'required|string',
            'image' => 'nullable|text',
            'name' => 'required|string',
        ];
    }
}
