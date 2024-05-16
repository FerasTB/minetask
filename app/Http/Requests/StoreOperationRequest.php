<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreOperationRequest extends FormRequest
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
            'record_id' => 'required|integer',
            'operations' => 'array|required',
            // 'operations.*' => 'array|required',
            'operations.operation_description' => 'string|nullable',
            'operations.operation_name' => 'string|required',
            // 'operations.*.coa_id' => 'integer|nullable',
            // 'operations.*.amount' => 'integer|required',
            // 'operations.*.total_price' => 'integer|required',
            // 'operations.*.price_per_one' => 'integer|required',
            'operations.number_of_tooth' => 'integer|required',
        ];
    }
}
