<?php

namespace App\Http\Requests;

use App\Enums\DoubleEntryType;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDirectDoubleEntryInvoiceRequest extends FormRequest
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
            'office_id' => 'integer|required',
            'COA_id' => 'integer|required',
            'main_coa_type' => ['required', Rule::in(DoubleEntryType::getKeys())],
            'total_price' => 'integer|required',
            'date_of_transaction' => 'date|nullable',
            'note' => 'string|nullable',
        ];
    }
}
