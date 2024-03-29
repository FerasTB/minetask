<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDentalLabInvoiceForDoctorRequest extends FormRequest
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
            'invoice_id' => 'nullable|integer',
            'date_of_invoice' => 'date|nullable',
            'due_to_invoice' => 'date|nullable',
            'total_price' => 'integer|required',
        ];
    }
}
