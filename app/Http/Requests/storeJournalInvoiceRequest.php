<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeJournalInvoiceRequest extends FormRequest
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
            'note' => 'nullable|string',
            'date_of_invoice' => 'date|nullable',
            'invoice_number' => 'nullable|integer',
            'doctor_id' => 'required|integer',
            'office_id' => 'required|integer',
            'debit_transactions' => 'required|array',
            'debit_transactions.*.account_id' => 'required|integer',
            'debit_transactions.*.amount' => 'required|integer',
            'debit_transactions.*.type' => 'required|string',
            'debit_transactions.*.is_coa' => 'required|boolean',
            'credit_transactions' => 'required|array',
            'credit_transactions.*.account_id' => 'required|integer',
            'credit_transactions.*.amount' => 'required|integer',
            'credit_transactions.*.type' => 'required|string',
            'credit_transactions.*.is_coa' => 'required|boolean',
        ];
    }
}
