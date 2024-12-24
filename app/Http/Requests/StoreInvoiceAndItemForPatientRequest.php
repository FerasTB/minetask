<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceAndItemForPatientRequest extends FormRequest
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
            'due_to_invoice' => 'nullable|date',
            'invoice_number' => 'nullable|integer',
            'total_price' => 'integer|required',
            'doctor_id' => 'required|integer',
            'office_id' => 'required|integer',
            'items' => 'array|nullable',
            'items.*.name' => 'required_with:items|string',
            'items.*.description' => 'nullable|string',
            'items.*.amount' => 'required_with:items|integer',
            'items.*.total_price' => 'required_with:items|integer',
            'items.*.price_per_one' => 'required_with:items|integer',
            'items.*.coa_id' => 'required_with:items|integer',
            'items.*.service_percentage' => 'nullable|integer',
            'items.*.teeth_record_id' => 'nullable|integer',
            'binding_charges' => 'array|nullable',
            'binding_charges.*' => 'integer|exists:invoice_items,id',
            'paid_done' => 'required|boolean',
            'cash_coa' => 'integer|required_if:paid_done,true',
        ];
    }
    public function withValidator($validator)
    {
        // Add conditional validation for items.*.coa_id
        $validator->sometimes('items.*.coa_id', 'nullable', function ($input) {
            return $input->paid_done === true;
        });

        // Add custom validation logic
        $validator->after(function ($validator) {
            $items = $this->input('items');
            $bindingCharges = $this->input('binding_charges');

            if (is_null($items) && is_null($bindingCharges)) {
                $validator->errors()->add('items', 'Either items or binding_charges must be provided.');
                $validator->errors()->add('binding_charges', 'Either items or binding_charges must be provided.');
            }
        });
    }

    public function messages()
    {
        return [
            'cash_coa.required_if' => 'The cash_coa field is required when paid_done is true.',
        ];
    }
}
