<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessDraftInvoiceRequest extends FormRequest
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
            'invoice_item_ids' => 'required|array',
            'invoice_item_ids.*' => 'exists:invoice_items,id',
        ];
    }
}
