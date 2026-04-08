<?php

namespace App\Http\Requests\Quote;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id'           => ['required', 'integer', 'exists:clients,id'],
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'valid_until'         => ['nullable', 'date'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ];
    }
}