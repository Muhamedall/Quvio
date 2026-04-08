<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id'           => ['sometimes', 'integer', 'exists:clients,id'],
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'due_date'            => ['sometimes', 'date'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'items'               => ['sometimes', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.quantity'    => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}