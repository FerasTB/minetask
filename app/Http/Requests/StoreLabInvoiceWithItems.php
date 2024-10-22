<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabInvoiceWithItems extends FormRequest
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
            // Invoice validation rules
            'note' => 'nullable|string',
            'invoice_id' => 'nullable|integer',
            'date_of_invoice' => 'date|nullable',
            'due_to_invoice' => 'date|nullable',
            'total_price' => 'required|integer',

            // Items validation rules
            'items' => 'nullable|array',  // Make sure 'items' is an array
            'items.*.name' => 'required|string', // Validate each item in the array
            'items.*.description' => 'nullable|string',
            'items.*.amount' => 'required|integer',
            'items.*.total_price' => 'required|integer',
            'items.*.price_per_one' => 'required|integer',
            'items.*.coa_id' => 'required|integer',
        ];
    }
}
