<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'  => ['required', 'integer', 'exists:clients,id'],
            'quote_id'   => ['nullable', 'integer', 'exists:quotes,id'],
            'tax_rate'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'due_date'   => ['required', 'date', 'after:today'],
            'notes'      => ['nullable', 'string'],

            'items'                  => ['required', 'array', 'min:1'],
            'items.*.description'    => ['required', 'string', 'max:500'],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
        ];
    }
}