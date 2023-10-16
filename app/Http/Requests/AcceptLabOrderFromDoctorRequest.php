<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcceptLabOrderFromDoctorRequest extends FormRequest
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
            'note' => 'string|required',
            'attached_materials' => 'string|required',
            'patient_name' => 'string|required',
            'steps' => 'integer|nullable',
            'received_date' => 'required|date',
            'delivery_date' => 'after:received_date|date|required',
            'order_steps' => 'array|required',
            'order_steps.*' => 'array|required',
            'order_steps.*.name' => 'string|required',
            'order_steps.*.note' => 'string|nullable',
            'order_steps.*.patient_id' => 'integer|nullable',
        ];
    }
}
