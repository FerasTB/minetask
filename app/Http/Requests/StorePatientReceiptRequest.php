<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientReceiptRequest extends FormRequest
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
            // 'invoice_id' => 'nullable|integer',
            'note' => 'nullable|string',
            'date_of_payment' => 'nullable|date',
            'total_price' => 'required|integer',
            'office_id' => 'required|integer',
            // 'doctor_id' => 'required|integer',
            'cash_coa' => 'required|integer',
        ];
    }
}
